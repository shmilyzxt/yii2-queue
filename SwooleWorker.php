<?php
/**
 * Created by PhpStorm.
 * User: too
 * Date: 2017/4/1
 * Time: 14:22
 * @author too <hayto@foxmail.com>
 */

new SwooleWorker();

class SwooleWorker
{

    public static $config = [
        'worker_num'=>1,
        'backlog'=>128,
    ];
    public function __construct()
    {
        $server = new Swoole\Server('127.0.0.1', '9527');
        $server->set(self::$config);
        $server->on('start', $this->start());
        $server->on('workerstart', $this->workerStart());
    }

    private function start(Swoole\Server $server)
    {
        echo "ready go\r\n";
    }

    private function workerStart(\Swoole\Server $server, $worker_id)
    {
        if($worker_id >= $server->setting['worker_num']){
            swoole_set_process_name("swoole_task_worker:{$worker_id}");
        }else{
            swoole_set_process_name("swoole_worker:{$worker_id}");
        }
    }
}