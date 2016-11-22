<?php

/**
 * 队列监听进程类,用户后台启动监听队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/22
 * Time: 16:54
 */
namespace shmilyzxt\queue;

use shmilyzxt\queue\base\Queue;
use shmilyzxt\queue\base\JobHandler;

class Worker
{
    public static function listen(Queue $queue,JobHandler $handler,$queueName='default',$memory=128,$sleep=3){
        while (true){
            if($job = $queue->pop($queueName)){
                $handler->handle($job);
            }else{
                self::sleep($sleep);
            }

            if (self::memoryExceeded($memory)) {
                self::stop();
            }
        }
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param  int   $memoryLimit
     * @return bool
     */
    public static function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * 停止队列监听
     */
    public static function stop(){
        die;
    }
    
    /**
     * 休眠
     */
    public static function sleep($seconds){
        sleep($seconds);
        echo "sleep";
    }
}