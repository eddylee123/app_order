<?php
namespace app\api\controller\ord;

use app\api\controller\BaseController;
use app\api\service\ord\DishesService;

class Dishes extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    /**
     * 列表
     * DateTime: 2024-03-20 15:42
     */
    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;
        if ($this->IsApp) {
            $rs = DishesService::instance()->lists($this->OrgId, $this->Data, true);
        } else {
            $rs = DishesService::instance()->lists($this->OrgId, $this->Data);
        }

        app_response(200, $rs);
    }

    /**
     * 详情
     * DateTime: 2024-03-20 15:41
     */
    public function info()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = DishesService::instance()->info($this->Data['ID']);

        app_response(200, $rs);
    }

    /**
     * 新增、编辑
     * DateTime: 2024-03-14 10:00
     */
    public function save()
    {
//        $validate = new RoomValidate();
        if (!empty($this->Data['ID'])) {
            //编辑
//            $result = $validate->scene('edit')->check($paramRoom);
//            if (!$result) {
//                app_exception($validate->getError());
//            }
            $rs = DishesService::instance()->edit($this->OrgId, $this->Data);
        } else {
            //新增
//            $result = $validate->scene('add')->check($paramRoom);
//            if (!$result) {
//                app_exception($validate->getError());
//            }
            $rs = DishesService::instance()->add($this->OrgId, $this->Data);
        }

        app_response(200, $rs);
    }

    /**
     * 删除
     * DateTime: 2024-03-14 10:00
     */
    public function del()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = DishesService::instance()->del($this->Data['ID']);

        app_response(200, $rs);
    }

    /**
     * 导出
     * DateTime: 2024-03-15 14:14
     */
    public function export()
    {
        $rs = DishesService::instance()->export($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}