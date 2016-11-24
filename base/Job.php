<?php
/**
 * 队列任务抽象基类
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
     * The name of the queue the job belongs to.
     *
     * @var string
     */
    protected $queue;

    /**
     * Queue实例
     * @var
     */
    public $queueInstance;

    /**
     * job处理handler实例
     * @var
     */
    public $handler;

    /**
     * the job payload data
     * @var
     */
    public $job;


    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     *
     * @var bool
     */
    protected $released = false;
    
    /**
     * Determine if the job was released back into the queue.
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    abstract public function getAttempts();

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    abstract public function getPayload();

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $this->resolveAndFire();
    }
    
    /**
     * Resolve and execute the job handler method.
     *
     * @param  array  $payload
     * @return void
     */
    protected function resolveAndFire()
    {
        $payload = Json::decode($this->getPayload());
        $class = $payload['job'];
        $this->handler = \Yii::$container->get($class);
        $this->handler->handle($this,$payload['data']);
    }

    /**
     * Call the failed method on the job instance.
     *
     * @return void
     */
    public function failed()
    {
        $payload = Json::decode($this->getPayload());
        $class = $payload['job'];
        $this->handler = \Yii::$container->get($class);

        if (method_exists($this->handler, 'failed')) {
            $this->handler->failed($payload['data']);
        }
    }
    
    
    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

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