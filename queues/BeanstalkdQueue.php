<?php
/**
 * beanstalkd队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/28
 * Time: 14:16
 */

namespace shmilyzxt\queue\queues;

use Pheanstalk\Pheanstalk;
use shmilyzxt\queue\base\Queue;

class BeanstalkdQueue extends Queue
{
    /**
     * Pheanstalk链接实例
     * @var \Pheanstalk\Pheanstalk
     */
    public $connector;

    public function init()
    {
        parent::init();

        if (!class_exists('\Pheanstalk\Pheanstalk')) {
            throw new \Exception('the extension pda\pheanstalk does not exist ,you need it to operate beanstalkd ,you can run "composer require pda/pheanstalk" to gei it!');
        }

        if (!$this->connector instanceof \Pheanstalk\Pheanstalk) {
            \Yii::$container->setSingleton('connector', $this->connector);
            $this->connector = \Yii::$container->get("connector")->connect();
        }
    }

    /**
     * 将一个任务加入队列
     * @param $job
     * @param string $data
     * @param null $queue
     */
    protected function push($job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);
        return $this->connector->useTube($this->getQueue($queue))->put(
            $payload, Pheanstalk::DEFAULT_PRIORITY, Pheanstalk::DEFAULT_DELAY, $this->expire
        );
    }

    /**
     * 将一个延时任务加入队列
     * @param $dealy
     * @param $job
     * @param string $data
     * @param null $queue
     * @return int
     */
    protected function later($dealy, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);
        $tube = $this->connector->useTube($this->getQueue($queue));
        return $tube->put($payload, Pheanstalk::DEFAULT_PRIORITY, $dealy, $this->expire);
    }


    /**
     * 从队列中取出一个任务
     * @param null $queue
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        $job = $this->connector->watchOnly($queue)->reserve(0);

        if ($job instanceof \Pheanstalk\Job) {

            $config = array_merge($this->jobEvent, [
                'class' => 'shmilyzxt\queue\jobs\BeanstalkdJob',
                'queue' => $queue,
                'job' => $job,
                'queueInstance' => $this,
            ]);

            return \Yii::createObject($config);
        }

        return false;
    }

    /**
     * 将一个任务重新加入队列
     * @param $queue
     * @param $job
     * @param $delay
     * @param int $attempts
     * @return mixed
     */
    public function release($queue, $job, $delay, $attempts = 0)
    {
        $priority = Pheanstalk::DEFAULT_PRIORITY;
        $this->connector->release($job, $priority, $delay);
    }

    /**
     * 清空队列任务
     * @param null $queue
     */
    public function flush($queue = null)
    {
        /*$queue = $this->getQueue($queue);
        while (true){
            $job = $this->connector->watchOnly($queue)->reserve(0);
            if($job instanceof \Pheanstalk\Job){
               $this->connector->useTube($queue)->delete($job);
            }
        }*/
        throw new \Exception("your can't do fulsh when use beanstalkd!");
    }

    /**
     * 获取tube中的任务数量（ready的+delay的）
     * @param null $queue
     */
    public function getJobCount($queue = null)
    {
        $queue = $this->getQueue($queue);
        $statsTube = $this->connector->statsTube($queue);
        return (int)$statsTube->current_jobs_ready + (int)$statsTube->current_jobs_delayed;
    }

    /**
     * 后去当前beanstalkd里面被监听的tube
     * @return array
     */
    public function listTubesWatched()
    {
        return $this->connector->listTubeUsed();
    }
}