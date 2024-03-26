<?php


namespace app\api\controller\ord;


use app\api\controller\BaseController;
use app\api\model\ord\Config;
use app\api\model\order\OrderMain;

class Conf extends BaseController
{

    protected $orderMainModel;
    protected $configModel;

    public function __construct()
    {

        parent::_initialize();

        $this->orderMainModel = new OrderMain();
        $this->configModel = new Config();
    }

    public function order()
    {

        $rs = [
            'STATE_MAP' => $this->orderMainModel->stateMap,
            'MEAL_TYPE' => $this->configModel->getConf('', 'MEAL_TYPE')
        ];

        app_response(200, $rs);
    }
}