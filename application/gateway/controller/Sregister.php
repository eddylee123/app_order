<?php
namespace app\gateway\controller;

use GatewayWorker\Register;
use Workerman\Worker;

class Sregister{
    public function __construct(){
        include_once dirname(__FILE__).'/../const.php';
        // register 服务必须是text协议
        $register = new Register( sprintf('text://%s',GW_REGISTER_PROTOCOL));

        // 如果不是在根目录启动，则运行runAll方法
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }
}