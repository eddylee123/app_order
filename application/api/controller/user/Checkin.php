<?php


namespace app\api\controller\user;


use app\api\controller\BaseController;
use app\api\service\fee\FeeService;
use app\api\service\user\CheckinService;
use app\api\validate\CheckinValidate;

class Checkin extends BaseController
{

    public function __construct()
    {
        parent::_initialize();
    }

    /**
     * 入住统计
     * DateTime: 2024-03-14 10:28
     */
    public function totalCheck()
    {
        $rs = CheckinService::instance()->totalCheck($this->OrgId);

        app_response(200, $rs);
    }


    /**
     * 入住列表
     * DateTime: 2024-03-14 10:28
     */
    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = CheckinService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 新增、更新入住
     * DateTime: 2024-03-14 10:28
     */
    public function save()
    {
        $validate = new CheckinValidate();
        if (!empty($this->Data['ID'])) {
            //编辑
            $result = $validate->scene('edit')->check($this->Data);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = CheckinService::instance()->editCheckin($this->Data);
        } else {
            //新增
            $result = $validate->scene('add')->check($this->Data);
            if (!$result) {
                app_exception($validate->getError());
            }
            $rs = CheckinService::instance()->addCheckin($this->Data);
        }

        app_response(200, $rs);
    }

    /**
     * 房间入住列表
     * DateTime: 2024-03-14 10:34
     */
    public function roomCheckin()
    {
        if (empty($this->Data['ROOM_ID'])) app_exception('请求参数不能为空');

        $rs = CheckinService::instance()->roomCheckin($this->Data['ROOM_ID']);

        app_response(200, $rs);
    }

    /**
     * 钥匙分配
     * DateTime: 2024-03-14 10:34
     */
    public function setKeys()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = CheckinService::instance()->setKeys($this->Data);

        app_response(200, $rs);
    }

    /**
     * 退宿确认
     * DateTime: 2024-03-14 18:56
     */
    public function preCheckout()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = CheckinService::instance()->preCheckout($this->Data);

        app_response(200, $rs);
    }

    /**
     * 退宿
     * DateTime: 2024-03-14 18:56
     */
    public function checkout()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = FeeService::instance()->checkout($this->Data);

        app_response(200, $rs);
    }
}