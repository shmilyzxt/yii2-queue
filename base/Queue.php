<?php
/**
 * 队列抽象基类.一个Queue的实例代表一个队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:20
 */

namespace shmilyzxt\queue\base;


use yii\base\Component;
use yii\di\ServiceLocator;
use yii\helpers\Json;

abstract class Queue extends ServiceLocator
{
    const EVENT_BEFORE_PUSH = 'beforePush';
    const EVENT_AFTER_PUSH = 'afterPush';
    const EVENT_BEFORE_POP = 'beforePop';
    const EVENT_AFTER_POP = 'afterPop';

    /**
     * 队列默认名称
     * @var string
     */
    public $queue = 'default';

    /**
     * 队列允许最大任务数量，0代表不限制
     * @var int
     */
    public $maxJob = 0;

    /**
     * 队列组件连接器
     * @var
     */
    public $connector;

    /**
     * 任务过期时间（秒）
     * @var int
     */
    public $expire = 60;


    /**
     * 入队列
     * @param $job
     * @param string $data
     * @param $queue
     */
    abstract protected function push($job, $data = '', $queue=null);

    /**
     * 延时入队列
     * @param $dealy
     * @param $job
     * @param string $data
     * @param $queue
     */
    abstract protected function later($dealy,$job,$data='',$queue=null);

    /**
     * 出队列
     * @param null $queue
     * @return Job 
     */
    abstract public function pop($queue=null);

    /**
     * 将一个任务重新加入队列
     * @param $queue
     * @param $job
     * @param $delay
     * @param int $attempts
     * @return mixed
     */
    abstract public function release($queue, $job, $delay,$attempts=0);

    /**
     * 清空某个队列
     * @param null $queue 队列名称，为空则清空default队列
     * @return mixed
     */
    abstract public function flush($queue=null);

    /**
     * 获取当前队列中等待执行的任务数量
     */
    abstract public function getJobCount($queue=null);
    
    /**
     * 入队列
     * @param $job
     * @param string $data
     * @param $queue
     * @return  mixed
     * @throws \Exception
     */
    public function pushOn($job, $data = '', $queue=null){
        if($this->canPush()){
            $this->trigger(self::EVENT_BEFORE_PUSH);
            $return =  $this->push($job,$data,$queue);
            $this->trigger(self::EVENT_AFTER_PUSH);
            return $return;
        }else{
            throw new \Exception("max jobs number exceed! the max jobs number is {$this->maxJob}");
        }
    }

    /**
     * 延时入队列
     * @param $dealy
     * @param $job
     * @param string $data
     * @param $queue
     * @return mixed
     * @throws \Exception
     */
    public function laterOn($dealy, $job, $data = '', $queue=null){
        if($this->canPush()){
            $this->trigger(self::EVENT_BEFORE_PUSH);
            $return = $this->later($dealy,$job,$data,$queue);
            $this->trigger(self::EVENT_AFTER_PUSH);
            return $return;
        }else{
            throw new \Exception("max jobs number exceed! the max jobs number is {$this->maxJob}");
        }
    }

    /**
     * 将任务及任务相关数据打包成json
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return string
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        if (is_object($job) && $job instanceof JobHandler) {
            $json =  serialize([
                'job' => $job,
                'data' => $this->prepareQueueData($data),
            ]);
            return $json;
        }

        return serialize(['job'=>$job,'data'=>$this->prepareQueueData($data)]);
    }

    /**
     * 处理任务的数据
     * @param $data
     * @return array
     */
    protected function prepareQueueData($data)
    {
        if (is_array($data)) {
            $data = array_map(function ($d) {
                if (is_array($d)) {
                    return $this->prepareQueueData($d);
                }

                return $d;
            }, $data);
        }
        return $data;
    }

    /**
     * 检查队列是否已达最大任务量
     * @return bool
     */
    protected function canPush(){
        if($this->maxJob > 0 && $this->getJobCount() >= $this->maxJob){
            return false;
        }
        return true;
    }


    /**
     * 获取多列名称，默认为：queue
     * @param $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return $queue ?: $this->queue;
    }
}