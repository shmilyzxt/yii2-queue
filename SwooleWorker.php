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

        'task_worker_num'=>2,
        'task_max_request'=>100,
        'task_ipc_mode'=>2, // 使用unix系统的消息队列
    ];

    public function __construct()
    {
        $server = new Swoole\Server('0.0.0.0', '9527');
        $server->set(self::$config);
        $server->on('start', [$this, 'onStart']);
        $server->on('workerstart', [$this, 'onWorkerStart']);
        $server->on('receive', [$this, 'onReceive']);
        $server->on('task', [$this, 'onTask']);
        $server->on('finish', [$this, 'onFinish']);
        $server->start();
    }

    public function onStart(Swoole\Server $server)
    {
        echo "ready go\r\n";
    }

    public function onWorkerStart(\Swoole\Server $server, $worker_id)
    {
        if($worker_id >= $server->setting['worker_num']){
            swoole_set_process_name("swoole_task_worker:{$worker_id}");
        }else{
            swoole_set_process_name("swoole_worker:{$worker_id}");
        }
    }

    public function onReceive(\Swoole\Server $server, $fd, $from_id, $data)
    {
        $server->task($data);
    }

    public function onTask(\Swoole\Server $server, $task_id, $src_worker_id, $data)
    {
        /*if('邮件'==$data){
            // new email
        }else{
            // 短信
        }
        echo '接收到任务:'. $data;
        $m = new Mail();
        $m->send($data['email']);*/

        $s = exec('/mnt/New_17zc/yii worker/listen');
        var_dump($s);
        return $data. $s;
    }

    public function onFinish(\Swoole\Server $server, $task_id, $data)
    {
        echo "完成任务：{$data}\r\n";
    }
}