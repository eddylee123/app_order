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
    public function __construct()
    {
//        $this->orderService = new OrdService();
        $this->orderModel = new OrderMain();
    }

    public static function nameserver()
    {
        return Env::get('rocketmq.nameserver', '');
    }

//    public static function producer()
//    {
//        $tag = '*';
//        $producer = new  Producer(self::instanceName);
//        $producer->setInstanceName(self::instanceName);
//        $producer->setNamesrvAddr(self::nameserver);
//        $producer->start();
//
//        $message = new Message(self::topicPay, $tag, "hello world");
//        $sendResult = $producer->send($message);
//
//        return $sendResult->getSendStatus();
//    }

//    public static function pullConsumer()
//    {
//        $consumer = new PullConsumer("GID_AS-final-state-infer");
//        $consumer->setGroup("GID_AS-final-state-infer");
//        $consumer->setInstanceName("GID_AS-final-state-infer");
//        $consumer->setTopic(self::topicPay);
//        $consumer->setNamesrvAddr(self::nameserver);
//
//        $consumer->start();
//        $queues = $consumer->getQueues();
//
//        foreach($queues as $queue){
//            $newMsg = true;
//            $offset = 0;
//            while($newMsg){
//                $pullResult = $consumer->pull($queue, "*", $offset, 8);
//
//                switch ($pullResult->getPullStatus()){
//                    case PullStatus::FOUND:
//                        foreach($pullResult as $key => $val){
//                            echo $val->getMessage()->getBody() . "\n";
//                        }
//                        $offset += count($pullResult);
//                        break;
//                    default:
//                        $newMsg = false;
//                        break;
//                }
//            }
//        }
//    }

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
              //echo $messageExt->getMessageBody();
              //return 0;

                return $this->saveOrder($messageExt->getMessageBody());
          }
        });
        static $common_model_db;
        if(!$common_model_db){
        $consumer->start();
        //$consumer->shutdown();
        }
    }

    /**
     * 断开队列
     * DateTime: 2024-04-15 22:04
     */
    public function consumerOver()
    {
        $consumer = new PushConsumer(self::instancePay);
        $consumer->shutdown();
    }


    /**
     * 订单更新
     * @param string $body
     * @return int
     * DateTime: 2024-03-29 15:47
     */
    public function saveOrder(string $body)
    {
        try {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                return 0;
            }
            //logs_write_cli($body, __LINE__);
            $flag = isset($data['refundId']) ? 'refund' : 'pay';

            if ($flag == 'refund') {
                //退款
                $main = $this->orderModel->where('PAYMENT_ID', $data['orderId'])->find();
                if (!$main) {
                    return 0;
                }
                if ($main['STATE'] != 'REFUND') {
                    return 0;
                }
                $rs = $main->save([
                    'STATE' => $data['tradeState'] == 'SUCCESS' ? 'REFUND_SUCCESS' : 'REFUND_FAIL',
                    'REFUND_ID' => $data['refundId'] ?? ''
                ]);
            } else {
                //支付
                $main = $this->orderModel->where('ORDER_NO', $data['body'])->find();
                if (!$main) {
                    return 0;
                }
                if ($main['STATE'] != 'WAIT_PAY') {
                    return 0;
                }
                $rs = $this->orderModel->where('ORDER_NO', $data['body'])->save([
                    'STATE' => $data['tradeState'] == 'SUCCESS' ? 'PAY_SUCCESS' : 'PAY_FAIL',
                    'PAYMENT_ID' => $data['orderId'] ?? ''
                ]);
            }
            if ($rs === false) {
                return 1;
            }
        } catch (Exception $e) {
            return 0;
        }

        return 0;
    }
}