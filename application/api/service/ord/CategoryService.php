<?php
namespace app\api\service\ord;


use app\api\model\ord\Category;
use app\api\service\BaseService;
use function fast\e;

class CategoryService extends BaseService
{
    protected $cateModel;

    public function __construct()
    {
        $this->cateModel = new Category();
    }

    public function listTree(string $orgId, string $mealType)
    {
        return $this->cateModel->getCateTree($orgId, $mealType);
    }

    public function lists(string $orgId, array $param)
    {
        $object =  $this->cateModel
            ->field('ID,PID,NAME')
            ->where('ORG_CODE', $orgId)
            ->where('STATUS', "ON");

        if (!empty($param['NAME'])) {
            $object->where("NAME", "like", "%".$param['NAME']."%");
        }
        if (isset($param['PID'])) {
            $object->where("PID", $param['PID']);
        }
        return $object->order('SEQ', 'desc')->select();
    }

    public function info(int $id)
    {
        return $this->cateModel->find($id);
    }

    public function add(string $orgCode, array $param)
    {
        return $this->cateModel->save($param);
    }


    public function edit(string $orgCode, array $param)
    {
        $info = $this->cateModel->find($param['ID']);
        if (!$info) {
            app_exception('请求参数异常');
        }
        unset($param['FILE_ID']);

        return $info->save($param);
    }

    public function del(int $dishesId)
    {
        $info = $this->cateModel->find($dishesId);
        if (!$info) {
            app_exception('请求参数异常');
        }

        return $info->delete();

    }
}