<?php
/**
 * ActiveMQ队列
 * User: 49783121@qq.com
 * Date: 2017/1/26
 * Time: 13:40
 */

namespace shmilyzxt\queue\queues;


use common\tools\var_dumper;
use shmilyzxt\queue\base\Job;
use shmilyzxt\queue\base\Queue;

class ActivemqQueue extends Queue
{
    public function init()
    {
        parent::init(); 
        if(!$this->connector instanceof \Stomp){
            \Yii::$container->setSingleton('connector', $this->connector);
            $this->connector = \Yii::$container->get("connector")->connect();
        }
    }

    /**
     * 入队列
     * @param $job
     * @param string $data
     * @param null $queue
     */
    protected function push($job, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);
        $this->pushDataToQueue($job,$data,$queue, 0,0,0);
        
        //更新
    }

    protected function later($dealy, $job, $data = '', $queue = null)
    {
        throw new \Exception("ActiveMQ does not support this feature for now!");
        //$queue = $this->getQueue($queue);
        //$this->pushDataToQueue($job,$data,$queue, $dealy,0,0);
    }

    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        $this->connector->subscribe($queue);
        if($this->connector->hasFrame()){
            $this->connector->begin("pop");
            $job = $this->connector->readFrame();
            $this->connector->ack($job);
            $this->connector->commit("pop");
            $config = array_merge($this->jobEvent, [
                'class' => 'shmilyzxt\queue\jobs\ActivemqJob',
                'queue' => $queue,
                'job' => $job,
                'queueInstance' => $this,
            ]);

            return \Yii::createObject($config);
        }

        $this->connector->unsubscribe($queue);
        //unset($this->connector);
        return false;
    }

    public function release($queue, $job, $delay, $attempts = 0)
    {
        $queue = $this->getQueue($queue);
        $this->connector->begin("release");
        $this->connector->send($queue,$job->body, ['persistent'=> true,'delay'=>$delay,'reserved'=>1,'attempts'=>$attempts]);
        $this->connector->commit("release");
        //unset($this->connector);
    }

    public function flush($queue = null)
    {
        throw new \Exception("ActiveMq does not support this method!");
    }

    public function getJobCount($queue = null)
    {
        throw new \Exception("ActiveMq does not support this method!");
    }
    
    private function pushDataToQueue($job,$data,$queue,$delay=0,$rserved=0,$attempts=0){
        $this->connector->begin("push");
        $this->connector->send($queue, $this->createPayload($job,$data,$queue), ['persistent'=> true,'delay'=>$delay,'reserved'=>$rserved,'attempts'=>$attempts]);
        $this->connector->commit("push");
    }
}