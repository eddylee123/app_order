<?php


namespace app\api\controller\ord;


use app\api\controller\BaseController;
use app\api\service\ord\OrderService;
use app\api\validate\OrderValidate;

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

        if ($this->IsApp) {
            //小程序请求
            $rs = OrderService::instance()->lists($this->OrgId, $this->Data, $this->User['userId']);
        } else {
            $rs = OrderService::instance()->lists($this->OrgId, $this->Data);
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

        $rs = OrderService::instance()->info($this->Data['ID']);

        app_response(200, $rs);
    }

    /**
     * 结算
     * DateTime: 2024-03-22 8:56
     */
    public function settle()
    {
        if (empty($this->Data['DISH'])) {
            app_exception('请求参数异常');
        }
        if (empty($this->Data['MEAL_TYPE'])) {
            app_exception('用餐类型必须');
        }
        $validate = new OrderValidate();
        foreach ($this->Data['DISH'] as $dish) {
            $result = $validate->scene('settle')->check($dish);
            if (!$result) {
                app_exception($validate->getError());
            }
        }

        $rs = OrderService::instance()->settle($this->User['userId'], $this->Data);

        app_response(200, $rs);
    }

    /**
     * 发起支付
     * DateTime: 2024-03-26 15:48
     */
    public function pay()
    {
        $validate = new OrderValidate();
        $result = $validate->scene('pay')->check($this->Data);
        if (!$result) {
            app_exception($validate->getError());
        }
        $this->Data['openId'] = $this->User['openId'] ?? '';
        $this->Data['IP'] = $this->request->param('ipAddress', '');

        $rs = OrderService::instance()->pay($this->Data);

        app_response(200, $rs);
    }

    /**
     * 订单号支付
     * DateTime: 2024-03-26 15:49
     */
    public function payOrder()
    {
        $validate = new OrderValidate();
        $result = $validate->scene('payOrder')->check($this->Data);
        if (!$result) {
            app_exception($validate->getError());
        }
        $this->Data['openId'] = $this->User['openId'] ?? '';
        $this->Data['IP'] = $this->request->param('ipAddress', '');

        $rs = OrderService::instance()->payOrder($this->Data);

        app_response(200, $rs);
    }

    /**
     * 发起退款
     * DateTime: 2024-03-26 15:49
     */
    public function refund()
    {
        $validate = new OrderValidate();
        $result = $validate->scene('refund')->check($this->Data);
        if (!$result) {
            app_exception($validate->getError());
        }
        if ($this->IsApp) {
            $rs = OrderService::instance()->refund($this->Data, true);
        } else {
            $rs = OrderService::instance()->refund($this->Data);
        }

        app_response(200, ['SUCCESS'=>$rs]);
    }

    /**
     * 订单查询
     * DateTime: 2024-03-29 15:27
     */
    public function query()
    {
        $validate = new OrderValidate();
        $result = $validate->scene('query')->check($this->Data);
        if (!$result) {
            app_exception($validate->getError());
        }

        $rs = OrderService::instance()->query($this->Data['ORDER_NO']);

        app_response(200, $rs);
    }

    /**
     * 核销
     * DateTime: 2024-03-29 15:27
     */
    public function check()
    {
        $validate = new OrderValidate();
        $result = $validate->scene('query')->check($this->Data);
        if (!$result) {
            app_exception($validate->getError());
        }

        $rs = OrderService::instance()->check($this->Data['ORDER_NO']);

        app_response(200, $rs);
    }

    /**
     * 导出
     * DateTime: 2024-05-08 15:56
     */
    public function export()
    {
        $rs = OrderService::instance()->export($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}