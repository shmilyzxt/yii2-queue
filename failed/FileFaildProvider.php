<?php
/**
 * 文件记录方式进行任务失败处理
 * User: shmilyzxt 49783121@qq.com
 * Date: 2017/1/26
 * Time: 10:17
 */

namespace shmilyzxt\queue\failed;


use yii\base\Component;

class FileFaildProvider extends Component implements IFailedProvider
{
    public $filePath=null;

    public function log($connector, $queue, $payload)
    {
        if($this->filePath === null){
            throw new \Exception("you must specify the fielPath!");
        }
        $file = fopen($this->filePath, "a");
        if(!$file){
            throw new \Exception("can not open file:".$this->filePath);
        }
        if(fwrite($file, date('Y-m-d H:i:s').":connector:".$connector." | queue:".$queue." | payload:".$payload."\n")){
            return true;
        }

        return false;
    }
}