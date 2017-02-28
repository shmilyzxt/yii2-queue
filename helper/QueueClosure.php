<?php
/**
 * 处理jobhandler为closure类型的的任务
 * User: zhenxiaotao
 * Date: 2016/11/29
 * Time: 10:56
 */

namespace shmilyzxt\queue\helper;

use shmilyzxt\queue\base\JobHandler;

class QueueClosure extends JobHandler
{
    /**
     * @var \Closure
     */
    public $closure;

    /**
     * 执行任务
     * @param   $job
     * @param  array $data
     * @return void
     * @throws \Exception
     */
    public function handle($job, $data)
    {
        if ($this->closure instanceof \Closure) {
            $closure = $this->closure;
            $closure($job, $data);
        } else {
            throw new \Exception("closure is wrong!");
        }
    }
}