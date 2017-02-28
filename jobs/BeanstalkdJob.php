<?php
/**
 * Beanstalkd 队列任务.
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/28
 * Time: 14:15
 */

namespace shmilyzxt\queue\jobs;

use shmilyzxt\queue\base\Job;

class BeanstalkdJob extends Job
{
    /**
     * @var \Pheanstalk\Pheanstalk
     */
    public $pheanstalk;

    /**
     * @var \Pheanstalk\Job
     */
    public $job;

    public function init()
    {
        parent::init();
        $this->pheanstalk = $this->queueInstance->connector;
    }


    /**
     * 获取任务尝试次数
     * @return int
     */
    public function getAttempts()
    {
        $stats = $this->pheanstalk->statsJob($this->job);
        return (int)$stats->reserves;
    }

    /**
     * 获取任务数据
     * @return string
     */
    public function getPayload()
    {
        return $this->job->getData();
    }

    /**
     * 删除一个任务
     * @return void
     */
    public function delete()
    {
        parent::delete();
        $this->pheanstalk->delete($this->job);
    }

    /**
     * 将任务重新假如队列
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->queueInstance->release($this->queue, $this->job, $delay,$this->getAttempts()+1);
    }

    /**
     * 休眠一个任务（beanstalkd特有功能）
     * @return void
     */
    public function bury()
    {
        $this->pheanstalk->bury($this->job);
    }

    /**
     * Get the job identifier.
     * @return string
     */
    public function getJobId()
    {
        return $this->job->getId();
    }
}