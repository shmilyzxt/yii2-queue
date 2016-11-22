<?php

/**
 * 数据库队列任务类
 * User: zhenxiaotao
 * Date: 2016/11/21
 * Time: 17:30
 */
namespace shmilyzxt\queue\jobs;

use shmilyzxt\queue\base\Job;

class DatabaseJob extends Job
{
    /**
     * 数据库记录对象
     * @var
     */
    public $job;

    /**
     * DatabaseQueue实例
     * @var
     */
    public $dbQueue;


    /**
     * 获取队列任务执行次数
     * @return mixed
     */
    public function getAttempts()
    {
        return $this->job->attempts;
    }

    /**
     * 获取队列任务数据
     * @return mixed
     */
    public function getPayload()
    {
        return $this->job->payload;
    }

    /**
     * 获取对垒任务id
     * @return mixed
     */
    public function getJobId(){
        return $this->job->id;
    }

    /**
     * 将任务重新加入队列
     * @param int $delay
     */
    public function relaese($delay=0)
    {
        parent::release($delay);
        $this->delete();
        $this->dbQueue->release($this->queue,$this->job,$delay);
    }
    
    /*
     * 删除任务
     */
    public function delete()
    {
        parent::delete();
        $this->dbQueue->deleteReserved($this->queue,$this->job->id);
    }

    /*
     * 属性设置
     */
    public function setJob($job){
        $this->job = $job;
    }

    /**
     * 属性
     * @return mixed
     */
    public function getJob(){
        return $this->getJob();
    }

    /**
     * 属性
     * @return mixed
     */
    public function getdbQueue(){
        return $this->dbQueue;
    }

    /**
     * 属性
     * @param $dbQueue
     */
    public function setdbQueue($dbQueue){
        $this->dbQueue = $dbQueue;
    }
}
