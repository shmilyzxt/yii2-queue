<?php
/**
 * 队列抽象基类.
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:20
 */

namespace shmilyzxt\queue\base;


use yii\base\Component;
use yii\di\ServiceLocator;

abstract class Queue extends Component
{
    const EVENT_BEFORE_PUSH = 'beforePush';
    const EVENT_AFTER_PUSH = 'afterPush';
    const EVENT_BEFORE_POP = 'beforePop';
    const EVENT_AFTER_POP = 'afterPop';
    
    
    /**
     * 入队列
     * @param $job
     * @param string $data
     * @param $queue
     */
    public function pushOn($job, $data = '', $queue){
        $this->trigger(self::EVENT_BEFORE_PUSH);
        $this->push($job,$data,$queue);
        $this->trigger(self::EVENT_AFTER_PUSH);
    }

    /**
     * 延时入队列
     * @param $dealy
     * @param $job
     * @param string $data
     * @param $queue
     */
    public function laterOn($dealy, $job, $data = '', $queue){
        $this->trigger(self::EVENT_BEFORE_PUSH);
        $this->later($dealy,$job,$data,$queue);
        $this->trigger(self::EVENT_AFTER_PUSH);
    }

    /**
     * 批量入队列
     * @param $jobs
     * @param string $data
     * @param null $queue
     */
    public function pushOnJobs($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->trigger(self::EVENT_BEFORE_PUSH);
            $this->push($job, $data, $queue);
            $this->trigger(self::EVENT_AFTER_PUSH);
        }
    }


    /**
     * 延时任务批量入队列
     * @param $delay
     * @param $jobs
     * @param string $data
     * @param null $queue
     */
    public function laterOnJobs($delay,$jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->trigger(self::EVENT_BEFORE_PUSH);
            $this->later($delay,$job, $data, $queue);
            $this->trigger(self::EVENT_AFTER_PUSH);
        }
    }

    /**
     * 将任务及任务相关数据打包
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return string
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        if (is_object($job)) {
            return json_encode([
                'job' => 'shmilyzxt\queue\CallQueuedHandler@call',
                'data' => ['command' => serialize(clone $job)],
            ]);
        }

        return json_encode(['job'=>$job,'data'=>$this->prepareQueueData($data)]);
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
}