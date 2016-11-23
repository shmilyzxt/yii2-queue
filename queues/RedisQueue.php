<?php
/**
 * redis队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/23
 * Time: 17:13
 */

namespace shmilyzxt\queue\queues;

use shmilyzxt\queue\base\Queue;
use shmilyzxt\queue\jobs\RedisJob;

class RedisQueue extends Queue
{
    /*
     * redis连接实例
     */
    public $redis;

    public $expire = 60;
    
    public function init()
    {
        parent::init(); 
        if($this->redis == null){
            $this->redis = \Yii::$app->redis;
        }
        if(is_array($this->redis)){
            $this->redis = \Yii::createObject($this->redis);
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
            $this->redis->zadd($queue.':reserved', $this->getTime() + $this->expire, $job);

            return new RedisJob($this->container, $this, $job, $original);
        }
    }

    public function getJobCount()
    {
        return 9999;
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

        $this->redis->zadd($this->getQueue($queue).':delayed', $this->getTime() + $delay, $payload);
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
                $transaction, $from, $time = $this->getTime()
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
        return json_encode(self::set($payload, $key, $value));
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}