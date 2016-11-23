yii2-queue
==========
a yii2 extension to make simple to use queue.

yii2-queue让队列的使用在yii2中变得更轻松，她为各种队列组件的使用提供了一个标准的接口，您只需要配置好需要使用的队列组件，就能轻松使用。
目前支持数据库队列，redis队列，其它队列中间件支持正在添加中。

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist shmilyzxt/yii2-queue "dev-master"
```

or add

```
"shmilyzxt/yii2-queue": "dev-master"
```

to the require section of your `composer.json` file.


Usage
-----

1:在配置文件中配置好需要使用的队列，配置代码一般如下（以数据库队列为例，数据库队列需要先建立队列存储表，sql参见扩展目录jobs.sql）：

```php
'queue' => [
            'class' => 'shmilyzxt\queue\queues\DatabaseQueue',
            'db' => [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
            ],
            'table' => 'jobs',
            'queue' => 'email',
            'expire' => 60
        ]
```php

2：任务入队列,在需要将任务加入队列的地方使用

```php
\Yii::$app->queue->pushOn(null,['email'=>'49783121@qq.com','title'=>'test','content'=>'email test'],'email');
```

3:新建自己的队列处理handler，继承、shmilyzxt\queue\base\JobHandler,并实现任务处理方法handle和失败处理方法failed，一个jobhandler类似：

```php
class SendMail extends JobHandler
{

    public function handle($job)
    {
        if($job->getAttempts() > 3){
            $this->failed($job);
        }

        $payload = $job->getPayload();
        $job->delete();
    }

    public function failed($job)
    {
        die("发了3次都失败了，算了");
    }
}
```

4：启动后台队列监听进程，对任务进行处理，您可以使用console app来启动(比如使用WorkerController里的list方法启动)
```php
public function actionListen(){
        $email = new \frontend\job\SendMail();
        Worker::listen(\Yii::$app->queue,$email,'email');
}

yii worker/list
```

当后台监听任务启动起来后，一但有任务加入队列，队列就会调用jobhandler的handle方法对任务进行处理了

```php

```

```php

```