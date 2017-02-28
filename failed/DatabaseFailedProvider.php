<?php

/**
 * 数据库表记录方式进行任务失败处理
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/29
 * Time: 14:00
 */
namespace shmilyzxt\queue\failed;

use yii\base\Component;

class DatabaseFailedProvider extends Component implements IFailedProvider
{
    /**
     * 数据库链接实例
     * @var \Yii\db\Connection
     */
    public $db;

    /**
     * 记录错误信息的表
     * @var string
     */
    public $table = 'failed_jobs';

    public function init()
    {
        parent::init();
        if (!$this->db instanceof \yii\db\Connection && is_array($this->db)) {
            \Yii::$container->setSingleton('failedDb', $this->db);
            $this->db = \Yii::$container->get('failedDb');
        }
    }

    /**
     * 将失败日志写入数据库
     */
    public function log($connector, $queue, $payload)
    {
        return $this->db->createCommand()->insert($this->table, [
            'connector' => $connector,
            'queue' => $queue,
            'payload' => $payload,
            'failed_at' => date("Y-m-d H:i:s", time())
        ])->execute();
    }
}