<?php
/**
 * redis队列任务
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/23
 * Time: 17:14
 */

namespace shmilyzxt\queue\jobs;


use shmilyzxt\queue\base\Job;
use shmilyzxt\queue\helper\ArrayHelper;

class RedisJob extends Job
{

    public function getAttempts()
    {
        return ArrayHelper::get(unserialize($this->job), 'attempts');
    }

    public function getPayload()
    {
        return $this->job;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return ArrayHelper::get(unserialize($this->job), 'id');
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
        $this->queueInstance->deleteReserved($this->queue, $this->job);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->delete();
        $this->queueInstance->release($this->queue, $this->job, $delay, $this->getAttempts() + 1);
    }
}