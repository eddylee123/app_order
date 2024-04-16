<?php
// 定义应用目录

define('APP_PATH', __DIR__ . '/../application/');
define('ROOT_PATH', dirname(realpath(APP_PATH)) . '/');

// 绑定模块
define('BIND_MODULE','gateway/Daemon');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';

