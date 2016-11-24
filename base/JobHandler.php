<?php
/**
 * 任务处理handler基类
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/22
 * Time: 15:37
 */

namespace shmilyzxt\queue\base;


abstract  class JobHandler
{
    /**
     * 队列任务执行方法
     * @param  $job
     */
    abstract public function handle(Job $job,array $data);

    /**
     * 队列任务执行失败处理方法
     * @param $palyload
     * @return mixed
     */
    public function failed($palyload)
    {
        
    }

    public static function className()
    {
        return get_called_class();
    }
}