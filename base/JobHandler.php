<?php
/**
 * Created by PhpStorm.
 * User: zhenxiaotao
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
    abstract public function handle($job);

    /**
     * 队列任务执行失败处理方法
     * @param $job
     * @return mixed
     */
    public function failed($job)
    {
        
    }

    public static function className()
    {
        return get_called_class();
    }
}