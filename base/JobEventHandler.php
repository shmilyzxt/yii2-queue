<?php
/**
 * 任务事件处理handler
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/29
 * Time: 15:39
 */

namespace shmilyzxt\queue\base;


use common\tools\var_dumper;

class JobEventHandler
{
    public static function beforeExecute(JobEvent $event)
    {
        echo "beforeExecute\r\n";
    }

    public static function beforeDelete(JobEvent $event)
    {
        echo "beforeDelete\r\n";
    }
}