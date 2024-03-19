<?php


namespace app\api\service\user;


use app\api\model\config\Config;
use app\api\model\config\SubsidyConfig;
use app\api\model\place\Place;
use app\api\model\record\CheckinRecord;
use app\api\service\BaseService;
use app\cache\BaseCache;
use think\Db;
use think\Exception;

class CheckinService extends BaseService
{
    protected $redis;
    protected $checkinModel;
    protected $placeModel;
    protected $configModel;
    protected $feePeriodModel;

    public function __construct()
    {

        $this->redis = alone_redis();
        $this->redis->select(1);

        $this->checkinModel = new CheckinRecord();
        $this->placeModel = new Place();
        $this->configModel = new Config();

    }

    public function totalCheck(string $orgId)
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d 23:59:59');

        $data = [];
        $data['CHECKOUT'] = $this->checkinModel
            ->where('ORG_ID', $orgId)
            ->whereBetween('CHECKOUT_DATE', [$start, $end])
            ->count();

        $data['WAIT_CHECKOUT'] = $this->checkinModel
            ->where('ORG_ID', $orgId)
            ->where('STATE', 'WAIT_CHECKOUT')
            ->count();

        $data['CHECKIN'] = $this->checkinModel
            ->where('ORG_ID', $orgId)
            ->whereBetween('CHECKIN_DATE', [$start, $end])
            ->count();

        $data['CHECKING'] = $this->checkinModel
            ->where('ORG_ID', $orgId)
            ->whereIn('STATE', ['CHECKIN','WAIT_CHECKOUT'])
            ->count();

        $data['ROOM_NUM'] = $this->placeModel
            ->where('ORG_ID', $orgId)
            ->where('TYPE','ROOM')
            ->whereIn('STATUS', ['EMPTY','VACANT'])
            ->count();

        $data['BED_NUM'] = $this->placeModel
            ->where('ORG_ID', $orgId)
            ->where('TYPE','BED')
            ->where('STATUS', 'UNUSED')
            ->count();

        return $data;
    }

    public function lists(string $orgId, array $param)
    {
        $object = $this->checkinModel;

        if (!empty($orgId)) {
            $object->where("ORG_ID", $orgId);
        }
        if (!empty($param['STATE'])) {
            $object->where("STATE", $param['STATE']);
        }
        if (!empty($param['CHECKIN_START'])) {
            $object->where("CHECKIN_DATE", '>=', $param['CHECKIN_START']);
        }
        if (!empty($param['CHECKIN_END'])) {
            $object->where("CHECKIN_DATE", '<=', $param['CHECKIN_END']);
        }
        if (!empty($param['PERSON_ID'])) {
            $object->where("PERSON_ID", $param['PERSON_ID']);
        }

        $list = $object
            ->order('CHECKIN_DATE', 'desc')
            ->paginate(['list_rows' => $param['page_size'], 'query' => $param['page']])
            ->toArray();
        $roomIds = array_column($list['data'], 'ROOM_ID');
        $roomList = $this->placeModel->whereIn('ID', $roomIds)->column('ID,NAME,LINK_TYPE', 'ID');
        $roomType = $this->configModel->getConf($orgId, "ROOM_TYPE");

        foreach ($list['data'] as &$v) {
            $emp = $this->getEmpCache($v['PERSON_ID']);

            $v['NAME'] = $emp['name'] ?? '';
            $v['TEL'] = $emp['contactPhone'] ?? '';
            $v['IDCARD'] = $emp['idCard'] ?? '';

            $roomInfo = $roomList[$v['ROOM_ID']] ?? [];
            $v['ROOM_NAME'] = $roomInfo['NAME'] ?? '';
            $v['ROOM_TYPE'] = $roomType[$roomInfo['LINK_TYPE']] ?? '';
        }

        return $list;
    }

    public function roomCheckin(int $roomId)
    {
        $bed = $this->placeModel
            ->field('a.ID,NO,NAME,SEQ,ORG_ID,LINK_TYPE,HOUSE_TYPE,STATUS')
            ->alias('a')
            ->join('dor_place_relation b', "a.ID=b.B_ID")
            ->where('a.TYPE', 'BED')
            ->where('b.A_ID', $roomId)
            ->select();
        $bed = collection($bed)->toArray();

        $bedIds = array_column($bed, 'ID');
        $checkin = $this->checkinModel
            ->whereIn('BED_ID', $bedIds)
            ->column('BED_ID,PERSON_ID,CHECKIN_DATE,ASSIGN_KEYS,STATE,REMARK', 'BED_ID');

        $data = [];
        foreach ($bed as $v) {
            $check = $checkin[$v['ID']] ?? null;
            if ($check) {
                $emp = $this->getEmpCache($check['PERSON_ID']);

                $v['NAME'] = $emp['name'] ?? '';
                $v['TEL'] = $emp['contactPhone'] ?? '';
                $v['IDCARD'] = $emp['idCard'] ?? '';
                $data[] = array_merge($v, $check);
            } else {
                $check = [
                    'BED_ID' => '',
                    'PERSON_ID' => '',
                    'CHECKIN_DATE' => '',
                    'ASSIGN_KEYS' => '',
                    'STATE' => '',
                    'REMARK' => '',
                    'NAME' => '',
                    'TEL' => '',
                    'IDCARD' => '',
                ];
            }

        }

        return $data;
    }

    public function addCheckin(array $param)
    {
        $room = $this->placeModel->find($param['ROOM_ID']);
        if (!$room) {
            app_exception('房间信息不存在');
        }
        if (!in_array($room['STATUS'], ['EMPTY','VACANT'])) {
            app_exception('房间状态异常，不可入住');
        }
        $bed = $this->placeModel->find($param['BED_ID']);
        if (!$bed) {
            app_exception('床位信息不存在');
        }
        if ($bed['STATUS'] != 'UNUSED') {
            app_exception('床位状态不可用');
        }
        $exist = $this->checkinModel
            ->where('PERSON_ID', $param['PERSON_ID'])
            ->whereIn('STATE', ['WAIT_CHECKIN','CHECKIN','WAIT_CHECKOUT'])
            ->value('ID');
        if ($exist) {
            app_exception('员工已登记入住，请勿重复操作');
        }

        $param['ORG_ID'] = $room['ORG_ID'];
        empty($param['CHECKIN_DATE']) && $param['CHECKIN_DATE'] = date('Y-m-d H:i:s');
        $param['STATE'] = strtotime($param['CHECKIN_DATE']) > time() ? 'WAIT_CHECKIN' : 'CHECKIN';

        $rs0 = $this->checkinModel->save($param);
        if (!$rs0) {
            throw new Exception('入住操作失败');
        }

        if ($param['STATE'] == 'CHECKIN') {
            $this->setRoomStatus($param['ROOM_ID'], $param['BED_ID'], 'checkin');
        }

        return $rs0;
    }

    public function editCheckin(array $param)
    {
        $room = $this->placeModel->find($param['ROOM_ID']);
        if (!$room) {
            app_exception('房间信息不存在');
        }
        $bed = $this->placeModel->find($param['BED_ID']);
        if (!$bed) {
            app_exception('床位信息不存在');
        }

        $checkin = $this->checkinModel->find($param['ID']);
        if (empty($checkin)) {
            app_exception('系统数据异常');
        }
        unset($param['ID']);
        $param['STATE'] = strtotime($param['CHECKIN_DATE']) > time() ? 'WAIT_CHECKIN' : 'CHECKIN';

        $rs0 = $checkin->save($param);
        if (!$rs0) {
            throw new Exception('入住操作失败');
        }

        if ($param['STATE'] == 'CHECKIN') {
            $this->setRoomStatus($param['ROOM_ID'], $param['BED_ID'], 'checkin');
        }

        return $rs0;
    }

    public function setKeys(array $param)
    {
        $checkin = $this->checkinModel->find($param['ID']);
        if (empty($checkin)) {
            app_exception('系统数据异常');
        }
        unset($param['ID']);
        $data = [];
        !empty($param['ASSIGN_KEYS']) && $data['ASSIGN_KEYS'] = $param['ASSIGN_KEYS'];
        !empty($param['RETURN_KEYS']) && $data['RETURN_KEYS'] = $param['RETURN_KEYS'];

        if (empty($data)) {
            app_exception('暂无数据更新');
        }

        return $checkin->save($data);
    }

    public function preCheckout(array $param)
    {
        $checkin = $this->checkinModel->find($param['ID']);
        if (empty($checkin)) {
            app_exception('系统数据异常');
        }

        $day = get_day($checkin['CHECKIN_DATE'], $param['CHECKOUT_DATE']);

        $action = [
            'E_ID' => $checkin['ID'],
            'E_TYPE' => 'EMPLOYEE', //员工
            'NAME' => $checkin['PERSON_ID']
        ];

        $period = [
            'NAME' => $checkin['PERSON_ID'],
            'BEGIN_TIME' => $checkin['CHECKIN_DATE'],
            'END_TIME' => $param['CHECKOUT_DATE'],
            'DAYS' => $day,
        ];

        //查看补贴
        $subsidyModel = new SubsidyConfig();

        $emp = $this->getEmpCache($checkin['PERSON_ID']);
        $time = date('Y-m-d H:i:s');
        $subsidy = $subsidyModel
            ->where('PERSON_TYPE', $checkin['PERSON_TYPE'])
            ->where('PERSON_LEVEL', $emp['jobLevel'])
            ->where('ORG_ID', $checkin['ORG_ID'])
            ->where('SUBSIDY_TYPE', 'ELECTRIC')
            ->where('BEGIN_TIME', '>=', $time)
            ->where('END_TIME', '<=', $time)
            ->value('QUANTITY');
        $subsidy = intval($subsidy * 100);
        $electric = $day * 1 * 100;

        $fee = [
            'electric' => $electric / 100,
            'subsidy' => $subsidy / 100,
            'pay' => ($electric - $subsidy)  / 100,
            'ASSIGN_KEYS' => $checkin['ASSIGN_KEYS']
        ];

        //缓存
        $cache = compact('fee', 'action', 'period');
        $this->redis->set(sprintf(BaseCache::fee_emp, $checkin['PERSON_ID']), json_encode($cache), 180);

        return $fee;
    }

    /**
     * 处理房间床位状态
     * @param $roomId
     * @param $bedId
     * @param string $flag
     * @return bool
     * DateTime: 2024-03-14 9:44
     */
    public function setRoomStatus($roomId, $bedId, $flag='checkin')
    {
        $bed = $this->placeModel
            ->alias('a')
            ->join('dor_place_relation b', "a.ID=b.B_ID")
            ->where('a.TYPE', 'BED')
            ->where('b.A_ID', $roomId)
            ->column('STATUS', 'a.ID');

        if ($flag == 'checkout') {
            $used = array_filter($bed, function ($item){
                return $item == 'USED';
            });
            $roomStatus = count($used) <= 1 ? 'EMPTY' : 'VACANT';
            $bedStatus = 'UNUSED';
        } else {
            $unused = array_filter($bed, function ($item){
                return $item == 'UNUSED';
            });
            $roomStatus = count($unused) <= 1 ? 'FULL' : 'VACANT';
            $bedStatus = 'USED';
        }

        Db::startTrans();
        try {
            $rsRoom = $this->placeModel->find($roomId)->save(['STATUS'=>$roomStatus]);
            $rsBed = $this->placeModel->find($bedId)->save(['STATUS'=>$bedStatus]);
            if ($rsRoom === false || $rsBed === false) {
                throw new Exception('房间床位同步失败');
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }

        return false;
    }

    public function getEmpCache($personId)
    {
        $empStr = $this->redis->get(sprintf(BaseCache::base_emp, $personId));
        return !empty($empStr) ? json_decode($empStr, true) : [];
    }
}