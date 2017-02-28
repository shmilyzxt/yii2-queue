<?php
/**
 * Created by PhpStorm.
 * User: zhenxiaotao
 * Date: 2017/1/26
 * Time: 13:35
 */

namespace shmilyzxt\queue\jobs;


use shmilyzxt\queue\base\Job;

class ActivemqJob extends Job
{

    public function getAttempts()
    {
        return $this->job->headers['attempts'];
    }

    public function getPayload()
    {
        return $this->job->body;
    }

    /**
     * 删除一个任务
     * @return void
     */
    public function delete()
    {
        parent::delete();
    }

    /**
     * 将任务重新假如队列
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->queueInstance->release($this->queue, $this->job, $delay, $this->getAttempts() + 1);
    }

    /**
     * Get the job identifier.
     * @return string
     */
    public function getJobId()
    {
        return $this->job->headers['message-id'];
    }
}