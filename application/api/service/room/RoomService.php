<?php
namespace app\api\service\room;

use app\api\model\config\Config;
use app\api\model\place\Place;
use app\api\model\place\PlaceProp;
use app\api\model\place\PlaceRelation;
use app\api\model\record\CheckinRecord;
use app\api\service\BaseService;
use think\Db;
use think\Exception;

class RoomService extends BaseService
{
    protected $placeModel;
    protected $placePropModel;
    protected $placeRelModel;
    protected $configModel;
    protected $checkinModel;

    const placeField = 'ID,NO,NAME,SEQ,ORG_ID,LINK_TYPE,HOUSE_TYPE,STATUS';

    public function __construct()
    {
        $this->placeModel = new Place();
        $this->placePropModel = new PlaceProp();
        $this->placeRelModel = new PlaceRelation();
        $this->placeRelModel = new PlaceRelation();
        $this->configModel = new Config();
        $this->checkinModel = new CheckinRecord();
    }

    public function roomList(string $orgId, array $param)
    {
        $object = $this->placeModel
            ->where("TYPE","ROOM");

        if (!empty($orgId)) {
            $object->where("ORG_ID", $orgId);
        }
        if (!empty($param['NO'])) {
            $object->where("NO", $param['NO']);
        }
        if (!empty($param['LINK_TYPE'])) {
            $object->where("LINK_TYPE", $param['LINK_TYPE']);
        }
        if (!empty($param['HOUSE_TYPE'])) {
            $object->where("HOUSE_TYPE", $param['HOUSE_TYPE']);
        }
        if (!empty($param['STATUS'])) {
            $object->where("STATUS", $param['STATUS']);
        }

        $list = $object
            ->field(self::placeField)
            ->order('SEQ')
            ->paginate(['list_rows' => $param['page_size'], 'query' => $param['page']])
            ->toArray();
        //房型
        $roomType = $this->configModel->getConf($orgId, "ROOM_TYPE");
        //床位数
        $roomIds = array_column($list['data'], 'ID');
        $bedCnt = $this->placeModel
            ->field('b.A_ID,count(*) cnt')
            ->alias('a')
            ->join('dor_place_relation b', "a.ID=b.B_ID")
            ->where('a.TYPE', 'BED')
            ->where('a.STATUS', 'UNUSED')
            ->whereIn('b.A_ID', $roomIds)
            ->group('b.A_ID')
            ->select();
        $bedCnt = array_column($bedCnt, 'cnt', 'A_ID');

        foreach ($list['data'] as &$v){
            $v['ROOM_TYPE'] = $roomType[$v['LINK_TYPE']] ?? '';
            $v['BED_NUM'] = $bedCnt[$v['ID']] ?? 0;
        }

        return $list;
    }

    public function roomInfo(int $roomId)
    {
        $room = $this->placeModel
            ->field(self::placeField)
            ->find($roomId);
        if (empty($room)) {
            return [];
        }

        $bed = $this->placeModel
            ->field('a.ID,NO,NAME,SEQ,ORG_ID,LINK_TYPE,HOUSE_TYPE,STATUS')
            ->alias('a')
            ->join('dor_place_relation b', "a.ID=b.B_ID")
            ->where('a.TYPE', 'BED')
            ->where('b.A_ID', $roomId)
            ->select();
        $bed = collection($bed)->toArray();

        $prop = $this->placePropModel->getConf($room['NAME']);

        $data['ROOM'] = $room;
        $data['BED'] = $bed;
        $data['PROP'] = $prop;

        return $data;
    }

    public function addRoom(array $paramRoom, array $paramBed, array $paramProp)
    {
        Db::startTrans();
        try {
            //房间
            $roomIn = $this->placeModel->where(['NO'=>$paramRoom['NO']])->value('ID');
            if (!empty($roomIn)) {
                throw new Exception('房间编号不能重复');
            }
            $time = date("Y-m-d H:i:s");
            $paramRoom['TYPE'] = 'ROOM';
            $paramRoom['STATUS'] = 'EMPTY';
            $paramRoom['CREATE_TIME'] = $time;
            $roomId = $this->placeModel->insertGetId($paramRoom);
            if (!$roomId) {
                throw new Exception('房间操作失败');
            }
            //床位
            $relData = [];
            if (!empty($paramBed)) {
                foreach ($paramBed as $v) {
                    $v['TYPE'] = 'BED';
                    $v['STATUS'] = 'UNUSED';
                    $v['CREATE_TIME'] = $time;
                    $v['ORG_ID'] = $paramRoom['ORG_ID'];
                    $v['HOUSE_TYPE'] = $paramRoom['HOUSE_TYPE'];
                    $bedId = $this->placeModel->insertGetId($v);
                    if (!$bedId) {
                        throw new Exception('床位操作失败');
                    }
                    array_push($relData, [
                        'A_ID' => $roomId,
                        'B_ID' => $bedId,
                        'RELATION_TYPE' => 'ROOM_BED',
                    ]);
                }
            }
            if (!empty($relData)) {
                $rs1 = $this->placeRelModel->saveAll($relData);
                if (!$rs1) {
                    throw new Exception('床位数据关联失败');
                }
            }
            //其他
            if (!empty($paramProp)) {
                $rs2 = $this->placePropModel->saveRoomProp($paramRoom['NO'], $paramProp);
                if ($rs2 === false) {
                    throw new Exception('房间数据关联失败');
                }
            }
            Db::commit();
            return $roomId;
        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }
    }

    public function editRoom(array $paramRoom, array $paramBed, array $paramProp)
    {
        Db::startTrans();
        try {
            //房间
            $time = date("Y-m-d H:i:s");
            $roomId = $paramRoom['ID'];
            unset($paramRoom['ID']);
            $room = $this->placeModel->find($roomId);
            if (empty($room)) {
                throw new Exception('系统异常');
            }
            $rs0 = $room->save($paramRoom);
            if ($rs0 === false) {
                throw new Exception('房间操作失败');
            }
            //床位
            $relData = [];
            if (!empty($paramBed)) {
                $bed = $this->placeModel
                    ->alias('a')
                    ->join('dor_place_relation b', "a.ID=b.B_ID")
                    ->where('a.TYPE', 'BED')
                    ->where('b.A_ID', $roomId)
                    ->column('a.ID','a.NO');
                if (count($bed) != count($paramBed)) {
                    //删除后新增
                    $this->placeModel
                        ->whereIn('ID', array_values($bed))
                        ->delete();
                    $this->placeRelModel
                        ->whereIn('B_ID', array_values($bed))
                        ->delete();

                    foreach ($paramBed as $v) {
                        $v['TYPE'] = 'BED';
                        $v['STATUS'] = 'UNUSED';
                        $v['CREATE_TIME'] = $time;
                        $v['ORG_ID'] = $paramRoom['ORG_ID'];
                        $v['HOUSE_TYPE'] = $paramRoom['HOUSE_TYPE'];
                        $bedId = $this->placeModel->insertGetId($v);
                        if (!$bedId) {
                            throw new Exception('床位操作失败');
                        }
                        array_push($relData, [
                            'A_ID' => $roomId,
                            'B_ID' => $bedId,
                            'RELATION_TYPE' => 'ROOM_BED',
                        ]);
                    }
                } else {
                    //更新
                    foreach ($paramBed as $key=>&$val) {
                        if (empty($bed[$val['NO']])) {
                            unset($paramBed[$key]);
                            continue;
                        }
                        $val['ID'] = $bed[$val['NO']];
                    }
                    $rs3 = $this->placeModel->saveAll($paramBed);
                    if ($rs3 === false) {
                        throw new Exception('床位操作失败');
                    }
                }

            }
            if (!empty($relData)) {
                $rs1 = $this->placeRelModel->saveAll($relData);
                if ($rs1 === false) {
                    throw new Exception('床位数据关联失败');
                }
            }
            //其他
            if (!empty($paramProp)) {
                $rs2 = $this->placePropModel->saveRoomProp($paramRoom['NO'], $paramProp);
                if ($rs2 === false) {
                    throw new Exception('房间数据关联失败');
                }
            }
            Db::commit();
            return $roomId;
        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }
    }

    public function delRoom(int $roomId)
    {
        Db::startTrans();
        try {
            //房间
            $room = $this->placeModel->find($roomId);
            if (empty($room)) {
                throw new Exception('系统异常');
            }
            if ($room['STATUS'] != 'EMPTY') {
                throw new Exception('当前房间未清空，禁止删除');
            }
            $rs0 = $this->placeModel->where('ID',$roomId)->delete();
            if (!$rs0) {
                throw new Exception('房间操作失败');
            }
            //床位
            $bed = $this->placeModel
                ->alias('a')
                ->join('dor_place_relation b', "a.ID=b.B_ID")
                ->where('a.TYPE', 'BED')
                ->where('b.A_ID', $roomId)
                ->column('a.ID','a.NO');
            if (count($bed) > 0) {
                //删除后新增
                $this->placeRelModel
                    ->whereIn('B_ID', array_values($bed))
                    ->delete();
                $rs1 = $this->placeModel
                    ->whereIn('ID', array_values($bed))
                    ->delete();
                if (!$rs1) {
                    throw new Exception('床位操作失败');
                }
            }
            //其它
            $prop = $this->placePropModel
                ->where('FIELD_NAME', $room['NO'])
                ->find();
            if ($prop) {
                $rs2 = $prop->delete();
                if (!$rs2) {
                    throw new Exception('房间数据操作失败');
                }
            }

            Db::commit();
            return $roomId;
        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }
    }


}