<?php


namespace app\api\controller;


use app\api\service\queue\OrdService;

class Server
{
    public function test1()
    {
        $body = '{"accountDate":"20240403","actualPayAmount":1,"body":"2024040315293510210099","channel":1,"channelId":1,"channelOrderId":"4200002213202404033950014409","endDate":"2024-04-03 15:29:55","expireSeconds":0,"extra":"{\"amount\":{\"payer_total\":1,\"total\":1,\"currency\":\"CNY\",\"payer_currency\":\"CNY\"},\"bank_type\":\"OTHERS\",\"payer\":{\"openid\":\"ooowN5qRRmwbbEp6E1VjYHy2moOw\"}}","ipAddress":"10.254.30.36","jumpUrl":"","notifyUrl":"","orderId":"24040315295000171112","refundableAmount":1,"remark":"","settleAmount":1,"sourceTag":"order-payment","title":"order","totalAmount":1,"tradeState":"SUCCESS","tradeType":4,"userId":""}';
        (new OrdService())->saveOrder($body);
    }
    
    public function test2()
    {
        (new OrdService())->clearOrder();
    }

}