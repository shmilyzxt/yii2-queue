<?php
/**
 * Created by PhpStorm.
 * User: too
 * Date: 2017/4/1
 * Time: 14:20
 * @author too <hayto@foxmail.com>
 */

namespace shmilyzxt\queue\queues;

use shmilyzxt\queue\base\Job;
use shmilyzxt\queue\base\Queue;

class SwooleQueue extends Queue
{

    /**
     * 入队列
     * @param $job
     * @param string $data
     * @param $queue
     */
    protected function push($job, $data = '', $queue = null)
    {
        // TODO: Implement push() method.
    }

    /**
     * 延时入队列
     * @param $dealy
     * @param $job
     * @param string $data
     * @param $queue
     */
    protected function later($dealy, $job, $data = '', $queue = null)
    {
        // TODO: Implement later() method.
    }

    /**
     * 出队列
     * @param null $queue
     * @return Job
     */
    public function pop($queue = null)
    {
        // TODO: Implement pop() method.
    }

    /**
     * 将一个任务重新加入队列
     * @param $queue
     * @param $job
     * @param $delay
     * @param int $attempts
     * @return mixed
     */
    public function release($queue, $job, $delay, $attempts = 0)
    {
        // TODO: Implement release() method.
    }

    /**
     * 清空某个队列
     * @param null $queue 队列名称，为空则清空default队列
     * @return mixed
     */
    public function flush($queue = null)
    {
        // TODO: Implement flush() method.
    }

    /**
     * 获取当前队列中等待执行的任务数量
     */
    public function getJobCount($queue = null)
    {
        // TODO: Implement getJobCount() method.
    }
}