<?php

namespace app\api\library;

use app\api\service\queue\OrdService;
use RocketMQ\ConsumeFromWhere;
use RocketMQ\Message;
use RocketMQ\MessageModel;
use RocketMQ\Producer;
use RocketMQ\PullConsumer;
use RocketMQ\PullStatus;
use RocketMQ\PushConsumer;
use think\Env;
use think\Log;


class OrderMq
{

    const instancePay = 'app-order-master';
    const topicPay = 'payment-notification';
    const topicRefund = 'refund-notification';
    const tagPay = 'order-payment';
    const tagRefund = 'order-refund';

    protected $orderService;
    public function __construct()
    {
        $this->orderService = new OrdService();
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
                return $this->orderService->saveOrder($messageExt->getMessageBody());
          }
        });
        static $common_model_db;
        if(!$common_model_db){
        $consumer->start();
        //$consumer->shutdown();
        }
    }

}