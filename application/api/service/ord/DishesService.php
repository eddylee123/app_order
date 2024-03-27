<?php
namespace app\api\service\ord;

use app\api\model\ord\Category;
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

    public function __construct()
    {
        $this->dishesModel = new Dishes();
        $this->fileModel = new File();
        $this->cateModel = new Category();
        $this->dishesFileModel = new DishesFile();
    }

    public function lists(string $orgId, array $param)
    {
        $object = $this->dishesModel;

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

        $list = $object
            ->order('CREATE_DATE', 'DESC')
            ->paginate(['list_rows' => $param['page_size'], 'page' => $param['page']])
            ->toArray();

        $cateIds = array_column($list['data'], 'CATE_ID');
        $cateList = $this->cateModel->whereIn('ID', $cateIds)->column('NAME', 'ID');
        $dishIds = array_column($list['data'], 'ID');
        $fileList = $this->fileModel->getFile($dishIds);

        foreach ($list['data'] as &$v) {
            $v['CATE_NAME'] = $cateList[$v['CATE_ID']] ?? '';
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
        $fileId = $param['FILE_ID'];
        unset($param['FILE_ID']);

        Db::startTrans();
        try {
            $param['ORG_CODE'] = $orgCode;
            $param['STATUS'] = 'ON';
            $param['CREATE_DATE'] = date('Y-m-d h:i:s');

            $dishesId = $this->dishesModel->insertGetId($param);
            if (!$dishesId) {
                throw new Exception('菜谱新增失败');
            }

            if (!empty($fileId)) {
                $fileArr = explode(',', $fileId);
                $fileArr = array_map(function ($item) use ($dishesId) {
                    return [
                        'FILE_ID' => $item,
                        'DISHES_ID' => $dishesId,
                    ];
                }, $fileArr);

                $df = $this->dishesFileModel->saveAll($fileArr);
                if (!$df) {
                    throw new Exception('操作失败');
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
        $fileId = $param['FILE_ID'];
        unset($param['FILE_ID']);

        Db::startTrans();
        try {

            $rs0 = $dishes->save($param);
            if ($rs0 === false) {
                throw new Exception('菜谱更新失败');
            }

            $dfArr = $this->dishesFileModel->where('DISHES_ID', $dishes['ID'])->column('FILE_ID');
            $fileArr = explode(',', $fileId);

            if (array_diff($dfArr, $fileArr)) {
                $this->dishesFileModel->whereIn('FILE_ID', $dfArr)->delete();
                $fileArr = array_map(function ($item) use ($dishes) {
                    return [
                        'FILE_ID' => $item,
                        'DISHES_ID' => $dishes['ID'],
                    ];
                }, $fileArr);

                $df = $this->dishesFileModel->saveAll($fileArr);
                if (!$df) {
                    throw new Exception('操作失败');
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