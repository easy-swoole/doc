---
title: easyswoole队列服务
meta:
  - name: description
    content: easyswoole轻量级队列
  - name: keywords
    content: easyswoole队列|swoole队列|swoole多进程队列
---

# Queue 介绍

## 原理

`EasySwoole` 封装实现了一个轻量级的队列，默认以 `Redis` 作为队列驱动器。

可以自己实现一个队列驱动来实现队列，用 `kafka` 作为队列驱动器或者 `其他驱动器方式` 作为队列驱动器，来进行存储。

从上可知，`Queue` 并不是一个单独使用的组件，它更像一个对不同驱动的队列进行统一封装的门面组件。


## 组件要求

- ext-swoole: >=4.4.0
- easyswoole/component: ^2.0
- easyswoole/redis-pool: ~2.2.0

## 安装方法

> composer require easyswoole/queue=2.1.x

## 仓库地址

[easyswoole/queue](https://github.com/easy-swoole/queue)

## 基本使用

- 注册队列驱动器
- 设置消费进程
- 生产者投递任务

### 定义一个队列

```php
<?php

namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class MyQueue extends Queue
{
    use Singleton;
}
```

###  定义消费进程
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class QueueProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            MyQueue::getInstance()->consumer()->listen(function (Job $job) {
                // 打印消费数据
                var_dump($job->getJobData());
            });
        });
    }
}
```
> 可以多进程，多协程消费

### 驱动注册
 
```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Queue\Job;

class EasySwooleEvent implements Event
{
    
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // redis-pool 的使用请看 redis 章节文档(https://www.easyswoole.com/Components/Redis/pool.html)
        // 注册一个名为 queue 的 Redis 连接池
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig(
            [
                'host' => '127.0.0.1',
                'port' => '6379',
                // [可选参数] 密码
                // 'auth' => ''
            ]
        ), 'queue');

        // 获取 Redis 连接池中的一个 Redis 连接对象
        $redisPool = \EasySwoole\RedisPool\RedisPool::getInstance()->getPool('queue');

        // 配置队列驱动器，底层使用 Redis 驱动，并设置队列名为 'queue'
        $driver = new \EasySwoole\Queue\Driver\Redis($redisPool, 'queue');

        // 注册自定义队列
        \App\Utility\MyQueue::getInstance($driver);

        // 注册一个消费进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new \App\Utility\QueueProcess());

        // 模拟生产者，投递任务到队列中，可以在任意位置投递
        $register->add($register::onWorkerStart, function ($server, $id) {
            if ($id == 0) {
                Timer::getInstance()->loop(3000, function () {
                    $job = new Job();
                    // 设置投递的队列任务数据
                    $job->setJobData(['time' => \time()]);
                    \App\Utility\MyQueue::getInstance()->producer()->push($job);
                });
            }
        });
    }
}
```

> 关于进程安全退出问题请看 [进程章节](Components/Component/process.md)。


## 进阶使用

我们可以自定义驱动，实现 `RabbitMQ`、`Kafka` 等消费队列软件的封装。

用户需要定义类，并实现 `\EasySwoole\Queue\QueueDriverInterface` 接口的几个方法即可。该接口的详细实现请看下文。

### QueueDriverInterface 接口类实现

```php
<?php

namespace EasySwoole\Queue;

interface QueueDriverInterface
{
    public function push(Job $job):bool ;

    public function pop(float $timeout = 3.0):?Job;

    public function size():?int ;
}
```

### 组件自带的 `Redis` 队列驱动器实现

```php
<?php

namespace EasySwoole\Queue\Driver;

use EasySwoole\Queue\Job;
use EasySwoole\Queue\QueueDriverInterface;
use EasySwoole\Redis\Redis as Connection;
use EasySwoole\RedisPool\Pool;

class Redis implements QueueDriverInterface
{

    protected $pool;
    protected $queueName;
    public function __construct(Pool $pool,string $queueName = 'easy_queue')
    {
        $this->pool = $pool;
        $this->queueName = $queueName;
    }

    public function push(Job $job): bool
    {
        $data = serialize($job);
        return $this->pool->invoke(function (Connection $connection)use($data){
            return $connection->lPush($this->queueName,$data);
        });
    }

    public function pop(float $timeout = 3.0): ?Job
    {
        return $this->pool->invoke(function (Connection $connection){
            $data =  $connection->rPop($this->queueName);
            if($data){
                return unserialize($data);
            }
            return null;
        });
    }

    public function size(): ?int
    {
        return $this->pool->invoke(function (Connection $connection){
            return $connection->lLen($this->queueName);
        });
    }
}
```

## Queue 多节点使用

### 定义第一个队列(自定义 nodeId)

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
        // 自定义 nodeId
        $this->setNodeId('xxxxx1');
    }
}
```

### 定义第二个队列(自动生成 nodeId)
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class MyQueue2 extends Queue
{
    use Singleton;
}
```

### 获取节点id
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class QueueProcess extends AbstractProcess
{
    protected function run($arg)
    {   
        // 消费队列
        go(function () {
            MyQueue1::getInstance()->consumer()->listen(function (Job $job) {
                // 打印 节点Id
                var_dump($job->getNodeId());
                // 打印 任务Id
                var_dump($job->getJobId());
            });
            MyQueue2::getInstance()->consumer()->listen(function (Job $job) {
                // 打印 节点Id
                var_dump($job->getNodeId());
                // 打印 任务Id
                var_dump($job->getJobId());
            });
        });
    }
}
```
> 可以多进程，多协程消费

### 驱动注册

```php
<?php

namespace EasySwoole\EasySwoole;

use App\Utility\QueueProcess;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Queue\Job;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // redis-pool 的使用请看 redis 章节文档(https://www.easyswoole.com/Components/Redis/pool.html)
        // 注册一个名为 queue 的 Redis 连接池
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig(
            [
                'host' => '127.0.0.1',
                'port' => '6379',
                // [可选参数] 密码
                // 'auth' => ''
            ]
        ), 'queue');

        // 获取 Redis 连接池中的一个 Redis 连接对象
        $redisPool = \EasySwoole\RedisPool\RedisPool::getInstance()->getPool('queue');

        // 配置队列驱动器，底层使用 Redis 驱动，并设置队列名为 'queue'
        $driver = new \EasySwoole\Queue\Driver\Redis($redisPool, 'queue');

        // 【这里是重点】
        // 注册自定义队列
        \App\Utility\MyQueue1::getInstance($driver);
        \App\Utility\MyQueue2::getInstance($driver);

        // 注册一个消费进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new \App\Utility\QueueProcess());

        // 模拟生产者，投递任务到队列中，可以在任意位置投递
        $register->add($register::onWorkerStart, function ($server, $id) {
            if ($id == 0) {
                Timer::getInstance()->loop(3000, function () {
                    $job = new Job();
                    // 设置投递的队列任务数据
                    $job->setJobData(['time' => \time()]);
                    // 这里是重点
                    \App\Utility\MyQueue1::getInstance()->producer()->push($job);
                    \App\Utility\MyQueue2::getInstance()->producer()->push($job);
                });
            }
        });
    }
}
```

