<?php
/**
 * 数据库连接器（使用yii的）
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/25
 * Time: 17:35
 */

namespace shmilyzxt\queue\connectors;


use yii\base\Component;

class DatabaseConnector extends Component implements IConnector
{
    public function connect()
    {
        throw new \Exception('you should use yii\db\Connection as the database connector!');
    }
}