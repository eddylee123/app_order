<?php
// 注册协议
define('GW_REGISTER_PROTOCOL','0.0.0.0:1283');
// 注册地址
define('GW_REGISTER_ADDRESS','127.0.0.1:1283');
// 网关地址
define('GW_GATEWAY_ADDRESS','0.0.0.0:8283');
// 网关起始端口
define('GW_GATEWAY_START_PORT','2900');
// 心跳检测间隔，单位：秒，0 表示不发送心跳检测
define('GW_GATEWAY_PING_INTERVAL',20);
// 心跳次数
define('GW_GATEWAY_PING_LIMIT',3);
// 本机ip，分布式部署时请设置成内网ip（非127.0.0.1）
define('GW_LOCAL_HOST_IP','127.0.0.1');
// 网关名称
define('GW_GATEWAY_NAME','EventGateway');
// worker进程名称
define('GW_WORKER_NAME','EventWorker');
// Gateway进程数量，建议与CPU核数相同
define('GW_GATEWAY_COUNT',1);
// BusinessWorker进程数量，建议设置为CPU核数的1倍-3倍
define('GW_BUSINESS_WORKER_COUNT',1);
// Business业务处理类，可以带命名空间
define('GW_BUSINESS_EVENT_HANDLER','app\gateway\controller\Events');
