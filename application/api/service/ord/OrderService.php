<?php


namespace app\api\service\ord;


use app\api\model\order\OrderDetail;
use app\api\model\order\OrderMain;
use app\api\service\BaseService;

class OrderService extends BaseService
{
    protected $mainModel;
    protected $detailModel;

    public function __construct()
    {
        $this->mainModel = new OrderMain();
        $this->detailModel = new OrderDetail();
    }

    public function lists(string $orgId, array $param)
    {
        $object = $this->mainModel;

        if (!empty($orgId)) {
            $object->where("ORG_CODE", $orgId);
        }
        if (!empty($param['PLACE_ID'])) {
            $object->where("PLACE_ID", $param['PLACE_ID']);
        }
        if (!empty($param['PAYMENT_ID'])) {
            $object->where("PAYMENT_ID", $param['PAYMENT_ID']);
        }
        if (!empty($param['STATE'])) {
            $object->where("STATE", $param['STATE']);
        }

    }
}