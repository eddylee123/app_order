<?php

namespace app\gateway\controller;

use app\api\library\OrderMq;
use app\api\service\queue\OrdService;
use RocketMQ\PushConsumer;

class Daemon
{
    public function __construct()
    {
        $this->pidfile = dirname(__FILE__).'/daemon_queue.pid';

    }

    private function startDeamon() {
        if (file_exists($this->pidfile)) {
            echo "The file $this->pidfile exists.n";
            exit();
        }
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            echo 'start ok';
            exit($pid);
        } else {
            // we are the child
            file_put_contents($this->pidfile, getmypid());
            return getmypid();
        }
    }
    private function start(){
        $pid = $this->startDeamon();

//        (new OrderMq())->consumer();
//        while (true) {
//            file_put_contents(dirname(__FILE__).'/test.txt', date('Y-m-d H:i:s'), FILE_APPEND);
//            sleep(2);
//        }
        $pushConsumer = new PushConsumer("my_push_consumer");
        $pushConsumer->setNameServerAddress("10.251.64.83:9876");
        $pushConsumer->subscribe("payment-notification", "order-payment");
        $pushConsumer->setThreadCount(1);
        $pushConsumer->registerCallback(function($consumer, $messageExt){
            if (!empty($messageExt->getMessageBody())) {
                return (new OrdService())->saveOrder($messageExt->getMessageBody());
            }
//            echo "[message_ext.message_id] --> " . $messageExt->getMessageBody() . "\n";
//            return 0;

        });

        $pushConsumer->start();
        for ($i = 0; $i < 10; $i ++){
            echo "now running ". $i * 10 .  "s\n";
            sleep(10);
        }
        $pushConsumer->shutdown();
    }
    private function stop(){
        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            posix_kill($pid, 9);
            unlink($this->pidfile);
            echo 'stop ok';
        }

    }
    public function run($argv) {
        if($argv == 'start') {
            $this->start();
        }else if($argv == 'stop') {
            $this->stop();
        }else{
            echo 'param error';
        }
    }
}
$deamon = new Daemon();
$argv = getopt('',['a:']);
$deamon->run($argv['a']);
