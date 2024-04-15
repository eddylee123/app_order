<?php
// 定义应用目录
use app\gateway\controller\Daemon;

define('APP_PATH', __DIR__ . '/../application/');
define('ROOT_PATH', dirname(realpath(APP_PATH)) . '/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';

$deamon = new Daemon();
$deamon->run($argv);
