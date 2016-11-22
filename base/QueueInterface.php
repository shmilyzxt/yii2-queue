<?php
/**
 * 队列接口
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:20
 */

namespace shmilyzxt\queue\base;


interface QueenInterface
{
    /**将一个任务加入队列
     * @param mixed $job
     * @param string $data
     * @param null $queen
     */
    public function push($job,$data='',$queue=null);

    /**
     * 将raw内容加入队列中
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = []);

    /**
     * Push a new job onto the queue.
     * @param  string  $queue
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '');
    
    /**
     * 将一个延时任务假如到队列当中
     * @param int $dealy 延时秒数
     * @param $job
     * @param string $data
     * @param null $queen
     * @return mixed
     */
    public function later($dealy,$job,$data='',$queue=null);

    /**
     * Push a new job onto the queue after a delay.
     * @param  string  $queue
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '');


    /**
     * 从队列中取出一个任务
     * @param  $queue
     * @return mixed
     */
    public function pop($queue);
}