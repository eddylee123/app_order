<?php


namespace app\api\controller\ord;


use app\api\controller\BaseController;
use app\api\model\order\OrderMain;

class Conf extends BaseController
{

    protected $orderMainModel;

    public function __construct()
    {

        parent::_initialize();

        $this->orderMainModel = new OrderMain();
    }

    public function order()
    {
        return [
            'stateMap' => $this->orderMainModel->stateMap
        ];
    }
}