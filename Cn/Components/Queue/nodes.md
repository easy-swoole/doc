---
title: easyswoole队列服务
meta:
  - name: description
    content: easyswoole轻量级队列
  - name: keywords
    content: easyswoole队列|swoole队列|swoole多进程队列
---

# Queue多节点使用

## 定义第一个队列(自定义nodeId)
```php
<?php


namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;
use EasySwoole\Queue\QueueDriverInterface;

class MyQueue1 extends Queue
{
    use Singleton;

    public function __construct(QueueDriverInterface $driver)
    {
        parent::__construct($driver);
        $this->setNodeId('xxxxx1');
    }
}
```

## 定义第二个队列(自动生成nodeId)
```php
namespace App\Utility;


use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class MyQueue2 extends Queue
{
    use Singleton;
}
```

## 获取节点id
```
<?php

namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class QueueProcess extends AbstractProcess
{

    protected function run($arg)
    {
        go(function () {
            MyQueue1::getInstance()->consumer()->listen(function (Job $job) {
                var_dump($job->getNodeId());
                var_dump($job->getJobId());
            });
            MyQueue2::getInstance()->consumer()->listen(function (Job $job) {
                var_dump($job->getNodeId());
                var_dump($job->getJobId());
            });
        });
    }
}
```
> 可以多进程，多协程消费


## 驱动注册

```php
<?php


namespace EasySwoole\EasySwoole;


use App\Utility\MyQueue1;
use App\Utility\MyQueue2;
use App\Utility\QueueProcess;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Queue\Job;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

    }

    public static function mainServerCreate(EventRegister $register)
    {
        //redis pool使用请看redis 章节文档
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig(
            [
                'host' => '127.0.0.1',
                'port' => '6379'
            ]
        ), 'queue');
        $redisPool = \EasySwoole\RedisPool\RedisPool::getInstance()->getPool('queue');
        $driver = new \EasySwoole\Queue\Driver\Redis($redisPool, 'queue');
        // 这里是重点
        MyQueue1::getInstance($driver);
        MyQueue2::getInstance($driver);
        //注册一个消费进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new QueueProcess());
        //模拟生产者，可以在任意位置投递
        $register->add($register::onWorkerStart, function ($ser, $id) {
            if ($id == 0) {
                Timer::getInstance()->loop(3000, function () {
                    $job = new Job();
                    $job->setJobData(['time' => \time()]);
                    // 这里是重点
                    MyQueue1::getInstance()->producer()->push($job);
                    MyQueue2::getInstance()->producer()->push($job);
                });
            }
        });

    }
}
```
