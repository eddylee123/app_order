<?php
/**
 * workerman + GatewayWorker
 * 此文件只能在Linux运行
 * run with command
 * php start.php start
 */
ini_set('display_errors', 'on');
if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows.\n");
}
//检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}
if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('ROOT_PATH', dirname(realpath(APP_PATH)) . '/');
// 绑定模块
define('BIND_MODULE','gateway/Startqueue');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
