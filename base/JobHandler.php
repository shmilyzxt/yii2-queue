<?php
/**
 * 任务处理handler基类，至少需要实现handle方法用于处理任务，可选实现failed方法用于任务执行失败处理
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/22
 * Time: 15:37
 */

namespace shmilyzxt\queue\base;


abstract  class JobHandler
{
    /**
     * 从队列中拿到任务和相关数据后，需要对任务进行处理
     * @param  $job
     */
    abstract public function handle(Job $job,$data);

    /**
     * 队列任务执行失败处理方法
     * @param $job
     * @return mixed
     */
    public function failed($job,$data)
    {
        
    }

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
}