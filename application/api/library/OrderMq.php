<?php

namespace app\api\library;

use app\api\model\order\OrderMain;
//use app\api\service\queue\OrdService;
use RocketMQ\MessageModel;
use RocketMQ\PushConsumer;
use think\Env;
use think\Exception;


class OrderMq
{

    const instancePay = 'app-order-master';
    const topicPay = 'payment-notification';
    const topicRefund = 'refund-notification';
    const tagPay = 'order-payment';
    const tagRefund = 'order-refund';

//    protected $orderService;
    protected $orderModel;
    protected $body;
    protected $data;
    protected $main;
    protected $con = null;
    public function __construct()
    {
//        $this->orderService = new OrdService();
        $this->orderModel = new OrderMain();
    }

    public static function nameserver()
    {
        return Env::get('rocketmq.nameserver', '');
    }

    /**
     * 订阅消费队列
     * DateTime: 2024-03-27 9:40
     */
    public function consumer()
    {
        $consumer = new PushConsumer(self::instancePay);
        $consumer->setNameServerAddress(self::nameserver());
        $consumer->setThreadCount(1);
        $consumer->setMessageModel(MessageModel::CLUSTERING);
        //$consumer->setConsumeFromWhere(ConsumeFromWhere::CONSUME_FROM_FIRST_OFFSET);
        $consumer->subscribe(self::topicPay, self::tagPay);
        $consumer->subscribe(self::topicRefund, self::tagRefund);
        $consumer->registerCallback(function($consumer, $messageExt){
            if (!empty($messageExt->getMessageBody())) {
                echo $messageExt->getMessageBody();
                return 0;
                //$this->body = $messageExt->getMessageBody();
                /*$rs = $this->saveOrder($messageExt->getMessageBody());
                var_dump($rs);
                return $rs;*/
            }
        });
        //static $common_model_db;
        //if(!$common_model_db){
        $consumer->start();
        $consumer->shutdown();
        //}
    }

    public function consumerInit()
    {
        $this->con = new PushConsumer(self::instancePay);
        $this->con->setNameServerAddress(self::nameserver());
        $this->con->setThreadCount(1);
        $this->con->setMessageModel(MessageModel::CLUSTERING);
        //$consumer->setConsumeFromWhere(ConsumeFromWhere::CONSUME_FROM_FIRST_OFFSET);
        $this->con->subscribe(self::topicPay, self::tagPay);
        $this->con->subscribe(self::topicRefund, self::tagRefund);

        return;
    }
    public function consumer2()
    {
        $this->con->registerCallbackOrderly(function($consumer, $messageExt){
            if (!empty($messageExt->getMessageBody())) {
                //echo $messageExt->getMessageBody();
                //return 0;
                $this->body = $messageExt->getMessageBody();
                return $this->saveOrder();
            }
        });

        $this->con->start();
        $this->con->shutdown();

        return;
    }


    /**
     * 订单更新
     * @param string $body
     * @return int
     * DateTime: 2024-03-29 15:47
     */
    public function saveOrder()
    {
        $this->data = json_decode($this->body, true);
        if (!is_array($this->data)) {
            return 0;
        }

        logs_write_cli($this->body, __LINE__);
        $flag = isset($this->data['refundId']) ? 'refund' : 'pay';

        if ($flag == 'refund') {
            //退款
            $this->main = $this->orderModel->where('PAYMENT_ID', $this->data['orderId'])->find();
            if (!$this->main) {
                return 0;
            }
            if ($this->main['STATE'] != 'REFUND') {
                return 0;
            }
            $rs = $this->main->save([
                'STATE' => $this->data['tradeState'] == 'SUCCESS' ? 'REFUND_SUCCESS' : 'REFUND_FAIL',
                'REFUND_ID' => $this->data['refundId'] ?? ''
            ]);
        } else {
            //支付
            $this->main = $this->orderModel->where('ORDER_NO', $this->data['body'])->find();
            if (!$this->main) {
                return 0;
            }
            if ($this->main['STATE'] != 'WAIT_PAY') {
                return 0;
            }
            $rs = $this->main->save([
                'STATE' => $this->data['tradeState'] == 'SUCCESS' ? 'PAY_SUCCESS' : 'PAY_FAIL',
                'PAYMENT_ID' => $this->data['orderId'] ?? ''
            ]);
        }

        if ($rs === false) {
            return 1;
        }


        return 0;
    }

}