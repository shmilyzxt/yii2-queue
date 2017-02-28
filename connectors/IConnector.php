<?php
/**
 * 返回一个队列中间件连实例
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/28
 * Time: 10:46
 */

namespace shmilyzxt\queue\connectors;


Interface IConnector
{
    public function connect();
}