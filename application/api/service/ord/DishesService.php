<?php
namespace app\api\service\ord;

use app\api\model\ord\Category;
use app\api\model\ord\Config;
use app\api\model\ord\Dishes;
use app\api\model\ord\DishesFile;
use app\api\model\ord\File;
use app\api\service\BaseService;
use think\Db;
use think\Exception;

class DishesService extends BaseService
{
    protected $dishesModel;
    protected $fileModel;
    protected $cateModel;
    protected $dishesFileModel;
    protected $configModel;

    public function __construct()
    {
        $this->dishesModel = new Dishes();
        $this->fileModel = new File();
        $this->cateModel = new Category();
        $this->dishesFileModel = new DishesFile();
        $this->configModel = new Config();
    }

    public function lists(string $orgId, array $param, $app=false)
    {
        $object = $this->dishesModel;

        if ($app) {
            if (empty($param['MEAL_TYPE'])) {
                app_exception('用餐类型不能为空');
            }
            $object->where("STATUS", 'ON');
            //单日点餐饱和数
            $conf = $this->configModel->getConf('', 'PAY_CONFIG');
            $dayMaxDish = $conf['DAY_MAX_DISH'] ?? 6;
            $maxDish = OrderService::instance()->getMaxDish($param['MEAL_TYPE']);
            if (count($maxDish) == $dayMaxDish ) {
                $object->whereIn('ID', array_column($maxDish,'DISH_ID'));
            }
        }
        if (!empty($orgId)) {
            $object->where("ORG_CODE", $orgId);
        }
        if (!empty($param['NAME'])) {
            $object->where("NAME", "like", "%".$param['NAME']."%");
        }
        if (!empty($param['PLACE_ID'])) {
            $object->where("PLACE_ID", $param['PLACE_ID']);
        }
        if (!empty($param['CATE_ID'])) {
            $object->where("CATE_ID", $param['CATE_ID']);
        }
        if (!empty($param['STATUS'])) {
            $object->where("STATUS", $param['STATUS']);
        }

        $list = $object
            ->order('CREATE_DATE', 'DESC')
            ->paginate(['list_rows' => $param['page_size'], 'page' => $param['page']])
            ->toArray();

        $cateIds = array_column($list['data'], 'CATE_ID');
        $cateList = $this->cateModel->whereIn('ID', $cateIds)->column('NAME,MEAL_TYPE', 'ID');
        $dishIds = array_column($list['data'], 'ID');
        $fileList = $this->fileModel->getFile($dishIds);

        foreach ($list['data'] as &$v) {
            $cate = $cateList[$v['CATE_ID']] ?? [];
            $v['CATE_NAME'] = $cate['NAME'] ?? '';
            $v['MEAL_TYPE'] = $cate['MEAL_TYPE'] ?? '';
            $v['FILE'] = $fileList[$v['ID']] ?? [];
        }

        return $list;
    }

    public function info(int $dishesId)
    {
        $dishes = $this->dishesModel->find($dishesId);
        if (!$dishes) {
            app_exception('请求参数异常');
        }
        $dishesId = $dishes['ID'];

        $fileList = $this->fileModel->getFile([$dishesId]);

        $dishes['FILE'] = $fileList[$dishesId] ?? [];

        return $dishes;
    }

    public function add(string $orgCode, array $param)
    {
        $files = $param['FILES'];
        unset($param['FILES']);

        Db::startTrans();
        try {
            $param['ORG_CODE'] = $orgCode;
            $param['STATUS'] = 'ON';
            $param['CREATE_DATE'] = date('Y-m-d h:i:s');

            $dishesId = $this->dishesModel->insertGetId($param);
            if (!$dishesId) {
                throw new Exception('菜谱新增失败');
            }

            if (!empty($files)) {
                $fileArr = explode(',', $files);
                foreach ($fileArr as $val) {
                    //图片新增
                    $fileData = [
                        'FILE_PATH' => $val,
                        'FILE_TYPE' => 'PHOTO',
                        'CREATE_DATE' => $param['CREATE_DATE'],
                    ];
                    $fileId = $this->fileModel->insertGetId($fileData);
                    if (!$fileId) {
                        throw new Exception('图片新增失败');
                    }
                    //关联新增
                    $line = [
                        'FILE_ID' => $fileId,
                        'DISHES_ID' => $dishesId,
                    ];
                    $df = $this->dishesFileModel->insert($line);
                    if (!$df) {
                        throw new Exception('操作失败');
                    }
                }

            }

            Db::commit();
            return $dishesId;

        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }

        return false;
    }


    public function edit(string $orgCode, array $param)
    {
        $dishes = $this->dishesModel->find($param['ID']);
        if (!$dishes) {
            app_exception('请求参数异常');
        }
        $files = $param['FILES'];
        unset($param['FILES']);

        Db::startTrans();
        try {
            $rs0 = $dishes->save($param);
            if ($rs0 === false) {
                throw new Exception('菜谱更新失败');
            }

            //更新图片
            $fileArr = $this->dishesFileModel
                ->alias('df')
                ->join('ord_file f', 'f.ID=df.FILE_ID')
                ->where('DISHES_ID', $dishes['ID'])->column('FILE_ID');

            //删除原图
            $this->fileModel->whereIn('ID', $fileArr)->delete();
            $this->dishesFileModel->whereIn('FILE_ID', $fileArr)->delete();
            $time = date('Y-m-d h:i:s');
            if (!empty($files)) {
                $fileArr = explode(',', $files);
                foreach ($fileArr as $val) {
                    //图片新增
                    $fileData = [
                        'FILE_PATH' => $val,
                        'FILE_TYPE' => 'PHOTO',
                        'CREATE_DATE' => $time,
                    ];
                    $fileId = $this->fileModel->insertGetId($fileData);
                    if (!$fileId) {
                        throw new Exception('图片新增失败');
                    }
                    //关联新增
                    $line = [
                        'FILE_ID' => $fileId,
                        'DISHES_ID' => $dishes['ID'],
                    ];
                    $df = $this->dishesFileModel->insert($line);
                    if (!$df) {
                        throw new Exception('操作失败');
                    }
                }
            }

            Db::commit();
            return $dishes['ID'];

        } catch (Exception $e) {
            Db::rollback();
            app_exception($e->getMessage());
        }

        return false;
    }

    public function del(int $dishesId)
    {
        $dishes = $this->dishesModel->find($dishesId);
        if (!$dishes) {
            app_exception('请求参数异常');
        }

        return $dishes->delete();

    }
}