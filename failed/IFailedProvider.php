<?php
/**
 * 任务失败日志接口
 * User: shmilyzxt 4978321@qq.com
 * Date: 2016/11/29
 * Time: 14:19
 */

namespace shmilyzxt\queue\failed;


interface IFailedProvider
{
    public function log($connector, $queue, $payload);
}