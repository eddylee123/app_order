<?php


namespace app\gateway\controller;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;

class Startqueue
{
    public function __construct(){
        include_once dirname(__FILE__).'/../constqueue.php';
        // register 服务必须是text协议
        $register = new Register(sprintf('text://%s',GW_REGISTER_PROTOCOL));

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
            Worker::$logFile = '/dev/null';
            Worker::runAll();
        }
    }
}