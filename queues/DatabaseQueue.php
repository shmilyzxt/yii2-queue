<?php

/**
 * 数据库队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/21
 * Time: 15:40
 * 需要创建数据表(数据库只支持mysql)
 */
namespace shmilyzxt\queue\queues;

use shmilyzxt\queue\base\Queue;
use yii\db\Query;

class DatabaseQueue extends Queue
{
    /**
     * @var \Yii\db\Connection
     */
    public $connector;

    /**
     * 存储队列任务表名称
     * @var string
     */
    public $table = 'jobs';

    public function init()
    {
        parent::init();
        if (!$this->connector instanceof \yii\db\Connection) {
            \Yii::$container->setSingleton('connector', $this->connector);
            $this->connector = \Yii::$container->get('connector');
            if ($this->connector->driverName != 'mysql') {
                throw new \Exception("sorry,only mysql db supported!");
            }
        }
    }

    /**
     * 添加任务记录到数据库
     * @param mixed $job
     * @param string $data
     * @param null $queue
     * @return mixed
     */
    protected function push($job, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);
        return $this->pushToDatabase(0, $queue, $this->createPayload($job, $data));
    }

    /**
     * 添加payload到数据库
     * @param string $payload
     * @param null $queue
     * @param array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queue = $this->getQueue($queue);
        return $this->pushToDatabase(0, $queue, $payload);
    }

    /**
     * 添加一个延时记录到数据库
     * @param int $dealy
     * @param $job
     * @param string $data
     * @param null $queue
     * @return mixed
     */
    protected function later($dealy, $job, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);
        return $this->pushToDatabase($dealy, $queue, $this->createPayload($job, $data));
    }

    /**
     * 取出一个任务
     * @param null $queue
     * @return mixed
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        if (!is_null($this->expire)) {
            $this->releaseJobsThatHaveBeenReservedTooLong($queue);
        }

        $tran = $this->connector->beginTransaction();

        if ($job = $this->getNextAvailableJob($queue)) {
            $this->markJobAsReserved($job->id);
            $tran->commit();

            $config = array_merge($this->jobEvent, [
                'class' => 'shmilyzxt\queue\jobs\DatabaseJob',
                'queue' => $queue,
                'job' => $job,
                'queueInstance' => $this,
            ]);

            return \Yii::createObject($config);

        }
        $tran->commit();
        return false;
    }

    /**
     * 清空数据库队列
     * @param null $queue
     * @return integer
     * @throws \Exception execution failed
     */
    public function flush($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->connector->createCommand()
            ->delete("jobs", "queue='{$queue}'")
            ->execute();
    }

    /**
     * 从队列中删除一个已经处理过的任务
     *
     * @param  string $queue
     * @param  string $id
     * @return mixed
     */
    public function deleteReserved($queue, $id)
    {
        return $this->connector->createCommand()
            ->delete("jobs", "id={$id}")
            ->execute();
    }

    /**
     * 将一个任务重新加入队列
     *
     * @param  string $queue
     * @param  \StdClass $job
     * @param  int $delay
     * @return mixed
     */
    public function release($queue, $job, $delay, $attempt = 0)
    {
        return $this->pushToDatabase($delay, $queue, $job->payload, $job->attempts);
    }

    /**
     * 将任务数据写入数据库
     * @param $delay
     * @param $queue
     * @param $payload
     * @param int $attempts
     * @return integer
     */
    protected function pushToDatabase($delay, $queue, $payload, $attempts = 0)
    {
        $queue = $this->getQueue($queue);
        $created_at = time();
        $available_at = $this->getAvailableAt($delay, $created_at);
        return $this->connector->createCommand()->insert('jobs', [
            'queue' => $queue,
            'payload' => $payload,
            'attempts' => $attempts,
            'reserved' => 0,
            'reserved_at' => null,
            'available_at' => $available_at,
            'created_at' => $created_at
        ])->execute();
    }

    /**
     * 获取队列当前任务数量
     */
    public function getJobCount($queue = null)
    {
        $queue = $this->getQueue($queue);
        return (new Query())
            ->select(['id'])
            ->from($this->table)
            ->where(['reserved' => 0])
            ->andWhere(['queue' => $queue])
            ->count("*", $this->connector);
    }


    /**
     * 获取任务有效时间
     * @param $delay
     * @return mixed
     */
    protected function getAvailableAt($delay, $createtime)
    {
        return $createtime + $delay;
    }

    /**
     * 重新激活那些长时间未处理完的任务
     * @param  string $queue
     * @return integer
     */
    protected function releaseJobsThatHaveBeenReservedTooLong($queue)
    {
        $expired = time() + $this->expire;
        $sql = "update {$this->table} set reserved=0,reserved_at=null,attempts=attempts+1 where queue='{$this->getQueue($queue)}' and reserved=1 and reserved_at<={$expired}";
        return $this->connector->createCommand($sql)->execute();

    }

    /**
     * 获取下一个可用的任务
     *
     * @param  string|null $queue
     * @return \StdClass|null
     */
    protected function getNextAvailableJob($queue)
    {
        $now = time();
        $sql = "select * from {$this->table} where queue='{$this->getQueue($queue)}' and reserved=0 and available_at<={$now} ORDER BY id asc limit 1 for update";
        $job = \Yii::$app->db->createCommand($sql)->queryOne();
        return $job ? (object)$job : null;
    }

    /**
     * 将任务标记为已处理
     *
     * @param  string $id
     * @return void
     */
    protected function markJobAsReserved($id)
    {
        $now = time();
        $sql = "update {$this->table} set reserved=1,reserved_at={$now} where id={$id}";
        return $this->connector->createCommand($sql)->execute();
    }
}