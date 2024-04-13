<?php


namespace app\gateway\controller;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;

class Start
{
    public function __construct(){
        include_once dirname(__FILE__).'/../const.php';
        // register 服务必须是text协议
        $register = new Register(sprintf('text://%s',GW_REGISTER_PROTOCOL));
        // gateway 进程
//        $gateway = new Gateway(sprintf('Websocket://%s',GW_GATEWAY_ADDRESS));
//        // 设置名称，方便status时查看
//        $gateway->name = GW_GATEWAY_NAME;
//        // 设置进程数，gateway进程数建议与cpu核数相同
//        $gateway->count = GW_GATEWAY_COUNT;
//        // 分布式部署时请设置成内网ip（非127.0.0.1）
//        $gateway->lanIp = GW_LOCAL_HOST_IP;
//        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
//        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
//        $gateway->startPort = GW_GATEWAY_START_PORT;
//        // 心跳间隔
//        $gateway->pingInterval = GW_GATEWAY_PING_INTERVAL;
//        // 心跳次数
//        $gateway->pingNotResponseLimit = GW_GATEWAY_PING_LIMIT;
//        // 服务注册地址
//        $gateway->registerAddress = GW_REGISTER_ADDRESS;
//        // 心跳数据
//        $gateway->pingData = '';

        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = GW_WORKER_NAME;
        // bussinessWorker进程数量
        $worker->count = GW_BUSINESS_WORKER_COUNT;
        // 服务注册地址
        $worker->registerAddress = GW_REGISTER_ADDRESS;
        //设置处理业务的类,此处制定Events的命名空间
        $worker->eventHandler = GW_BUSINESS_EVENT_HANDLER;

        // 如果不是在根目录启动，则运行runAll方法
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }
}