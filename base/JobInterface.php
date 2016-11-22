<?php
/**
 * 队列任务接口
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 13:20
 */

namespace shmilyzxt\queue\base;


interface JobInterface
{
    /**
     * 执行任务
     * @return mixed
     */
    public function fire();

    /**
     * 从队列中删除任务
     * @return mixed
     */
    public function delete();

    /**
     * 检查任务是否被删除
     * @return mixed
     */
    public function isDeleted();

    /**
     * 将任务重新加入队列
     * @return mixed
     */
    public function relaese($delay=0);

    /**
     * 获取任务重试次数
     * @return mixed
     */
    public function attempts();

    /**
     * 获取任务名称
     * @return mixed
     */
    public function getName();

    /**
     * 任务失败时执行的方法
     * @return mixed
     */
    public function failed();

    /**
     * 获取任务所在队列名称
     * @return string
     */
    public function getQueue();

    /**
     * 获取任务的内容
     * @return string
     */
    public function getRawBody();
}