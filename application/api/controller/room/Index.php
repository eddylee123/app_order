<?php
namespace app\api\controller\room;

use app\api\controller\BaseController;
use app\api\service\room\RoomService;
use app\api\validate\RoomValidate;

class Index extends BaseController
{

    public function __construct()
    {
        parent::_initialize();
    }

    /**
     * 房间列表
     * DateTime: 2024-03-14 9:59
     */
    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

//        $param['ORG_ID'] = $this->request->param('ORG_ID/s', '');
//        $param['NO'] = $this->request->param('NO/s', '');
//        $param['LINK_TYPE'] = $this->request->param('LINK_TYPE/s', '');
//        $param['HOUSE_TYPE'] = $this->request->param('HOUSE_TYPE/s', '');
//        $param['STATUS'] = $this->request->param('STATUS/s', '');

        $rs = RoomService::instance()->roomList($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 房间详情
     * DateTime: 2024-03-14 9:59
     */
    public function info()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = RoomService::instance()->roomInfo($this->Data['ID']);

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
            $rs = RoomService::instance()->editRoom($paramRoom, $paramBed, $paramProp);
        } else {
            //新增
            $result = $validate->scene('add')->check($paramRoom);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = RoomService::instance()->addRoom($paramRoom, $paramBed, $paramProp);
        }

        app_response(200, $rs);
    }

    /**
     * 删除房间
     * DateTime: 2024-03-14 10:00
     */
    public function delRoom()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = RoomService::instance()->delRoom($this->Data['ID']);

        app_response(200, $rs);
    }
}