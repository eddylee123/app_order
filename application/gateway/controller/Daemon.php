<?php

namespace app\gateway\controller;

use app\api\library\OrderMq;

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

        (new OrderMq())->consumer();
//        while (true) {
//            file_put_contents(dirname(__FILE__).'/test.txt', date('Y-m-d H:i:s'), FILE_APPEND);
//            sleep(2);
//        }
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
        if($argv[1] == 'start') {
            $this->start();
        }else if($argv[1] == 'stop') {
            $this->stop();
        }else{
            echo 'param error';
        }
    }
}
//$deamon = new Daemon();
//$deamon->run($argv);
