<?php


namespace app\api\controller\ord;


use app\api\controller\BaseController;
use app\api\service\ord\OrderService;

class Order extends BaseController
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

        $rs = OrderService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }

    /**
     * 详情
     * DateTime: 2024-03-20 15:41
     */
    public function info()
    {
        if (empty($this->Data['ID'])) app_exception('请求参数不能为空');

        $rs = OrderService::instance()->info($this->Data['ID']);

        app_response(200, $rs);
    }
}