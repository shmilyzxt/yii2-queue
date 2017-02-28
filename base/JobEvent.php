<?php
/**
 * 任务事件
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/29
 * Time: 15:32
 */

namespace shmilyzxt\queue\base;


use yii\base\Event;

class JobEvent extends Event
{
    /**
     * @var Job
     */
    public $job;

    /**
     * @var string
     */
    public $payload;
}