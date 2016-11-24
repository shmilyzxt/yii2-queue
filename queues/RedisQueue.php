<?php
/**
 * redis队列
 * push是通过redis列表实现队列
 * later是通过redis有序结合实现队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/23
 * Time: 17:13
 */

namespace shmilyzxt\queue\queues;

use shmilyzxt\queue\base\Queue;
use shmilyzxt\queue\helper\ArrayHelper;
use shmilyzxt\queue\jobs\RedisJob;

class RedisQueue extends Queue
{
    /*
     * redis连接实例
     */
    public $redis;
    
    public function init()
    {
        parent::init();

        if(!class_exists('\Predis\Client')){
            throw new \Exception('the extension predis\predis does not exist ,you need it to operate redis ,you can run "composer require predis/predis" to gei it!');
        }

        if(!$this->redis instanceof \Predis\Client){
            $this->set('connector',$this->connector );
            $this->redis = $this->get('connector')->connect();
        }
    }

    protected function push($job, $data = '', $queue = null)
    {
        return $this->redis->rpush($this->getQueue($queue), $this->createPayload($job,$data,$queue));
    }

    protected function later($dealy, $job, $data = '', $queue = null)
    {
        return $this->redis->zadd($this->getQueue($queue).':delayed', time() + $dealy, $this->createPayload($job,$data,$queue));
    }

    public function pop($queue = null)
    {
        $original = $queue ?: $this->queue;
        $queue = $this->getQueue($queue);

        if (! is_null($this->expire)) {
            $this->migrateAllExpiredJobs($queue);
        }

        $job = $this->redis->lpop($queue);

        if (! is_null($job)) {
            $this->redis->zadd($queue.':reserved', time() + $this->expire, $job);

            return \Yii::createObject([
                'class' =>'shmilyzxt\queue\jobs\RedisJob',
                'queue' => $original,
                'job' => $job,
                'queueInstance' => $this,
            ]);
        }
    }

    public function getJobCount()
    {
        //TODO
        return 0;
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  string  $payload
     * @param  int  $delay
     * @param  int  $attempts
     * @return void
     */
    public function release($queue, $payload, $delay, $attempts=0)
    {
        $payload = $this->setMeta($payload, 'attempts', $attempts);
        $this->redis->zadd($this->getQueue($queue).':delayed', time() + $delay, $payload);
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return string
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        $payload = parent::createPayload($job, $data);
        $payload = $this->setMeta($payload, 'id', $this->getRandomId(32));
        return $this->setMeta($payload, 'attempts', 1);
    }

    /**
     * 创建一个随机串作为id
     * @param int $length
     * @return string
     */
    protected function getRandomId($length = 16){
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return 'queues:'.($queue ?: $this->queue);
    }

    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function migrateExpiredJobs($from, $to)
    {
        $options = ['cas' => true, 'watch' => $from, 'retry' => 10];

        $this->redis->transaction($options, function ($transaction) use ($from, $to) {
            // First we need to get all of jobs that have expired based on the current time
            // so that we can push them onto the main queue. After we get them we simply
            // remove them from this "delay" queues. All of this within a transaction.
            $jobs = $this->getExpiredJobs(
                $transaction, $from, $time = time()
            );

            // If we actually found any jobs, we will remove them from the old queue and we
            // will insert them onto the new (ready) "queue". This means they will stand
            // ready to be processed by the queue worker whenever their turn comes up.
            if (count($jobs) > 0) {
                $this->removeExpiredJobs($transaction, $from, $time);

                $this->pushExpiredJobsOntoNewQueue($transaction, $to, $jobs);
            }
        });
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param  string  $queue
     * @param  string  $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        $this->redis->zrem($this->getQueue($queue).':reserved', $job);
    }

    /**
     * Remove the expired jobs from a given queue.
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $from
     * @param  int  $time
     * @return void
     */
    protected function removeExpiredJobs($transaction, $from, $time)
    {
        $transaction->multi();

        $transaction->zremrangebyscore($from, '-inf', $time);
    }

    /**
     * Push all of the given jobs onto another queue.
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $to
     * @param  array  $jobs
     * @return void
     */
    protected function pushExpiredJobsOntoNewQueue($transaction, $to, $jobs)
    {
        call_user_func_array([$transaction, 'rpush'], array_merge([$to], $jobs));
    }

    /**
     * Migrate all of the waiting jobs in the queue.
     *
     * @param  string  $queue
     * @return void
     */
    protected function migrateAllExpiredJobs($queue)
    {
        $this->migrateExpiredJobs($queue.':delayed', $queue);
        $this->migrateExpiredJobs($queue.':reserved', $queue);
    }

    /**
     * Set additional meta on a payload string.
     *
     * @param  string  $payload
     * @param  string  $key
     * @param  string  $value
     * @return string
     */
    protected function setMeta($payload, $key, $value)
    {
        $payload = json_decode($payload, true);
        return json_encode(ArrayHelper::set($payload, $key, $value));
    }

    /**
     * Get the expired jobs from a given queue.
     *
     * @param  \Predis\Transaction\MultiExec  $transaction
     * @param  string  $from
     * @param  int  $time
     * @return array
     */
    protected function getExpiredJobs($transaction, $from, $time)
    {
        return $transaction->zrangebyscore($from, '-inf', $time);
    }
}