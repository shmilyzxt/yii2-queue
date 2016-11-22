<?php
/**
 * 队列任务抽象基类
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:21
 */

namespace shmilyzxt\queue\base;


use yii\base\Component;

abstract class Job extends Component
{
    /**
     * The name of the queue the job belongs to.
     *
     * @var string
     */
    protected $queue;

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
     * Resolve the given job handler.
     *
     * @param  string  $class
     * @return mixed
     */
    protected function resolve($class)
    {
        return \Yii::$container->get($class);
    }
    
    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return json_decode($this->getPayload(), true)['job'];
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
}