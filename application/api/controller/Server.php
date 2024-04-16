<?php


namespace app\api\controller;


use app\api\service\queue\OrdService;

class Server
{
    public function test1()
    {
        $body = '{"accountDate":"20240415","actualPayAmount":1,"body":"2024041512092250565448","channel":1,"channelId":1,"channelOrderId":"4200002211202404153884044851","endDate":"2024-04-15 12:09:29","expireSeconds":0,"extra":"{\"amount\":{\"payer_total\":1,\"total\":1,\"currency\":\"CNY\",\"payer_currency\":\"CNY\"},\"bank_type\":\"OTHERS\",\"payer\":{\"openid\":\"ooowN5lyRtnxLP6XCBkSrwbaO3eA\"}}","ipAddress":"39.144.190.4","jumpUrl":"","notifyUrl":"","orderId":"24041512092400010046","refundableAmount":1,"remark":"","settleAmount":1,"sourceTag":"order-payment","title":"order","totalAmount":1,"tradeState":"SUCCESS","tradeType":4,"userId":""}';
        (new OrdService())->saveOrder($body);
    }
    
    public function test2()
    {
        (new OrdService())->clearOrder();
    }

}