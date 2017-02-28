<?php
/**
 * activeMq stomp链接器
 * User: zhenxiaotao
 * Date: 2017/1/26
 * Time: 13:28
 */

namespace shmilyzxt\queue\connectors;


use common\tools\var_dumper;

class ActivemqConnector implements IConnector
{
    public $broker = 'tcp://localhost:61613';
    public $timeout = 0;

    public function connect()
    {
        if(!class_exists('\Stomp')){
            throw new \Exception("you need php_stomp extension!");
        }

        try{
            $stomp = new \Stomp($this->broker);
        }catch (\Exception $e){
            throw  new \Exception("can not connect activemq:".$this->broker);
        }
        $stomp->setReadTimeout($this->timeout);
        return $stomp;
    }
}