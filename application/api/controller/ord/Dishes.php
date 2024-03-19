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

    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = DishesService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 房间详情
     * DateTime: 2024-03-14 9:59
     */
    public function info()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = DishesService::instance()->info($this->Data['ID']);

        app_response(200, $rs);
    }

    /**
     * 新增、编辑房间
     * DateTime: 2024-03-14 10:00
     */
    public function saveRoom()
    {

        $paramRoom = $this->Data['ROOM'] ?? [];
        $paramBed = $this->Data['BED'] ?? [];
        $paramProp = $this->Data['PROP'] ?? [];

        $validate = new RoomValidate();
        $paramRoom['ORG_ID'] = $this->OrgId;
        if (!empty($paramRoom['ID'])) {
            //编辑
            $result = $validate->scene('edit')->check($paramRoom);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = DishesService::instance()->edit($paramRoom, $paramBed, $paramProp);
        } else {
            //新增
            $result = $validate->scene('add')->check($paramRoom);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = DishesService::instance()->add($paramRoom, $paramBed, $paramProp);
        }

        app_response(200, $rs);
    }

    /**
     * 删除房间
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