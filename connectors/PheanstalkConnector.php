<?php
/**
 * pheanstalk链接器，用于操作beanstalkd队列
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/28
 * Time: 14:11
 */

namespace shmilyzxt\queue\connectors;


use Pheanstalk\Pheanstalk;
use yii\base\Component;

class PheanstalkConnector extends Component implements IConnector
{
    public $host = '127.0.0.1';
    public $port = 11300;

    public function connect()
    {
        return new Pheanstalk($this->host, $this->port);
    }
}