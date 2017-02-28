<?php
/**
 * predis链接器
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/24
 * Time: 13:15
 */

namespace shmilyzxt\queue\connectors;


use yii\base\Component;

class PredisConnector extends Component implements IConnector
{
    public $parameters;
    public $options;

    public function connect()
    {
        return new \Predis\Client($this->parameters, $this->options);
    }
}