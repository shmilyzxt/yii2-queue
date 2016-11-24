<?php
/**
 * predisÁ´½ÓÆ÷
 * User: shmilyzxt 49783121@qq.com
 * Date: 2016/11/24
 * Time: 13:15
 */

namespace shmilyzxt\queue\connectors;


use yii\base\Component;

class PredisConnector extends Component
{
    public $parameters;
    public $options;
    public static $predis;

    public function init()
    {
        parent::init();
    }

    public function connect(){
        if(!self::$predis instanceof \Predis\Client){
            self::$predis = new \Predis\Client($this->parameters,$this->options);
        }

        return self::$predis;
    }
}