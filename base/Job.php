<?php
/**
 * 队列任务抽象基类，一个job类的实例代表一个队列里的任务
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:21
 */

namespace shmilyzxt\queue\base;


use yii\base\Component;
use yii\helpers\Json;

abstract class Job extends Component
{
    /**
     * 任务所属队列的名称
     * @var string
     */
    protected $queue;

    /**
     * Queue实例
     * @var Queue
     */
    public $queueInstance;

    /**
     * job处理handler实例
     * @var
     */
    public $handler;

    /**
     * 任务数据
     * @var
     */
    public $job;


    /**
     * 任务是否删除标识
     * @var bool
     */
    protected $deleted = false;

    /**
     * 任务是否releas标识
     * @var bool
     */
    protected $released = false;

    /**
     * 获取任务已经尝试执行的次数
     * @return int
     */
    abstract public function getAttempts();

    /**
     * 获取任务的数据
     * @return string
     */
    abstract public function getPayload();
    
    /**
     * 检测任务是否被重新加入队列
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * 检测任务是否被删除或者被release
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * 删除任务，子类需要实现具体的删除
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * 判断任务是否被删除
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * 将任务重新加入队列
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * 执行任务
     * @return void
     */
    public function execute()
    {
        $this->resolveAndFire();
    }
    
    /**
     * 真正任务执行方法（调用hander的handle方法）
     * @param  array  $payload
     * @return void
     */
    protected function resolveAndFire()
    {
        $payload = Json::decode($this->getPayload());
        $class = unserialize( $payload['job']);
        $this->handler = $this->getHander($class);
        $this->handler->handle($this, $payload['data']);
        
        //执行完任务后删除
        if (! $this->isDeletedOrReleased()) {
            $this->delete();
        }
    }

    /**
     * 任务执行失败后的处理方法（调用handler的failed方法）
     * @return void
     */
    public function failed()
    {
        $payload = Json::decode($this->getPayload());
        $class = unserialize( $payload['job']);
        $this->handler = $this->getHander($class);

        if (method_exists($this->handler, 'failed')) {
            $this->handler->failed($this,$payload['data']);
        }
    }

    /**
     * 获取任务处理handler实例
     */
    protected function getHander($class){
        if(is_object($class) && $class instanceof JobHandler){
            return $this->handler = $class;
        }else{
           return $this->handler = \Yii::$container->get($class);
        }
    }
    
    
    /**
     * 获取队列名称
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * 设置队列名称
     * @param $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
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
    public function getqueueInstance(){
        return $this->queueInstance;
    }

    /**
     * 属性
     * @param $queueInstance
     */
    public function setqueueInstance($queueInstance){
        $this->queueInstance = $queueInstance;
    }
}