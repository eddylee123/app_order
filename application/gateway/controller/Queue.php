<?php

namespace app\gateway\controller;

use app\api\library\OrderMq;


class Queue
{
    /**
     * 每个进程启动
     * @param $worker
     */
    public static function onWorkerStart($worker)
    {
        //订阅队列
        (new OrderMq())->consumer();
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $reqData 具体消息
     */
    public static function onMessage($client_id, $reqData)
    {
    }


    /**
     * 当连接断开时触发的回调函数
     * @param $client_id
     */
    public static function onClose($client_id)
    {
        //断开队列队列
        (new OrderMq())->consumerOver();
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public static function onError($client_id, $code, $msg)
    {
        echo "error $code $msg\n";
    }


}