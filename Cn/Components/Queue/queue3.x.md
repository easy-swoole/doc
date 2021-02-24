---
title: easyswoole队列服务
meta:
  - name: description
    content: easyswoole轻量级队列
  - name: keywords
    content: easyswoole队列|swoole队列|swoole多进程队列
---

# Queue介绍

## 原理
`EasySwoole` 封装实现了一个轻量级的队列，默认使用 `Redis` 作为队列驱动器。

用户可以自己实现一个队列驱动器来实现队列，用 `kafka` 作为队列驱动器或者 `其他驱动器方式` 作为队列驱动器，来进行存储。

从上可知，`Queue` 并不是一个单独使用的组件，它更像一个对不同驱动的队列进行统一封装的门面组件。

::: tip 
  旧版本 (2.1.x) 的 `Queue` 组件的使用，请看 [Queue 2.1.x](/Components/Queue/install.md)
:::

## 组件要求

- ext-swoole: >=4.4.0
- easyswoole/component: ^2.0
- easyswoole/redis-pool: ~2.2.0

## 安装方法
> composer require easyswoole/queue 3.x

## 仓库地址
[easyswoole/queue 3.x](https://github.com/easy-swoole/queue)

## 基本使用
默认自带的队列驱动为 `Redis` 队列。这里简单列举 2 种用户可使用的方式：
- 在框架的任意位置进行生产和消费队列任务。
- 在框架的任意位置进行生产队列任务， 然后在自定义进程中进行消费任务。

## 在框架中进行生产和消费任务

### 创建队列
```php
use EasySwoole\Queue\Driver\RedisQueue;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Queue;
use EasySwoole\Redis\Config\RedisConfig;

// 配置 Redis 队列驱动器
$redisConfig = new RedisConfig([
    'host' => '127.0.0.1', // 服务端地址 默认为 '127.0.0.1'
    'port' => 6379, // 端口 默认为 6379
    'auth' => '', // 密码 默认为 不设置
    'db'   => 0, // 默认为 0 号库
]);

// 创建队列
$queue = new Queue(new RedisQueue($redisConfig));
```

### 普通生产任务
`$queue` 为上述创建队列中得到的队列对象。
```php
// 创建任务
$job = new Job();

// 设置任务数据
$job->setJobData("this is my job data time time ".date('Ymd h:i:s'));

// 生产普通任务
$queue->producer()->push($job);
```

### 普通消费任务
`$queue` 为上述创建队列中得到的队列对象。
```php
// 消费任务
$job = $queue->consumer()->pop();

// 或者是自定义进程中消费任务(具体使用请看下文自定义进程消费任务完整使用示例)
$queue->consumer()->listen(function (Job $job){
    var_dump($job);
});
```

### 生产延迟任务
`$queue` 为上述创建队列中得到的队列对象。
```php
// 创建任务
$job = new Job();

// 设置任务数据
$job->setJobData("this is my job data time time ".date('Ymd h:i:s'));

// 设置任务延后执行时间
$job->setDelayTime(5);

// 生产延迟任务
$queue->producer()->push($job);
```

### 生产可信任务
```php
// 创建任务
$job = new Job();

// 设置任务数据
$job->setJobData("this is my job data time time ".date('Ymd h:i:s'));

// 设置任务重试次数为 3 次。任务如果没有确认，则会执行三次
$job->setRetryTimes(3);

// 如果5秒内没确认任务，会重新回到队列。默认为3秒
$job->setWaitConfirmTime(5);

// 投递任务
$queue->producer()->push($job);

// 确认一个任务
$queue->consumer()->confirm($job);
```

### 完整使用示例
以在 `http` 服务中为例，使用示例代码如下：
```php
<?php

namespace App\HttpController;

use App\Utility\MyQueue;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;
use EasySwoole\Queue\Driver\RedisQueue;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Queue;
use EasySwoole\Redis\Config\RedisConfig;

class Index extends Controller
{
    // 创建队列
    public function createQueue()
    {
        // 配置 Redis 队列驱动器
        $redisConfig = new RedisConfig([
            'host' => '127.0.0.1', // 服务端地址 默认为 '127.0.0.1'
            'port' => 6379, // 端口 默认为 6379
            'auth' => '', // 密码 默认为 不设置
            'db'   => 0, // 默认为 0 号库
        ]);

        // 创建队列
        $queue = new Queue(new RedisQueue($redisConfig, 'easyswoole_queue'));
        return $queue;
    }

    // 生产普通任务
    public function producer1()
    {
        // 获取队列
        $queue = $this->createQueue();

        // 创建任务
        $job = new Job();

        // 设置任务数据
        $job->setJobData("this is my job data time time " . date('Ymd h:i:s'));

        var_dump('producer1 => ');
        var_dump($job->getJobData());

        // 生产普通任务
        $produceRes = $queue->producer()->push($job);
        if (!$produceRes) {
            $this->writeJson(Status::CODE_OK, [], '队列生产普通任务失败!');
        } else {
            $this->writeJson(Status::CODE_OK, [], '队列生产普通任务成功!');
        }
    }

    // 生产延迟任务
    public function producer2()
    {
        // 获取队列
        $queue = $this->createQueue();

        // 创建任务
        $job = new Job();

        // 设置任务数据
        $job->setJobData("this is my job data time time " . date('Ymd h:i:s'));

        // 设置任务延后执行时间
        $job->setDelayTime(5);

        var_dump('producer2 => ');
        var_dump($job->getJobData());

        // 生产延迟任务
        $produceRes = $queue->producer()->push($job);
        if (!$produceRes) {
            $this->writeJson(Status::CODE_OK, [], '队列生产延迟任务失败!');
        } else {
            $this->writeJson(Status::CODE_OK, [], '队列生产延迟任务成功!');
        }
    }

    // 生产可信任务
    public function producer3()
    {
        // 获取队列
        $queue = $this->createQueue();

        // 创建任务
        $job = new Job();

        // 设置任务数据
        $job->setJobData("this is my job data time time " . date('Ymd h:i:s'));

        var_dump('producer3 => ');
        var_dump($job->getJobData());

        // 设置任务重试次数为 3 次。任务如果没有确认，则会执行三次
        $job->setRetryTimes(3);

        // 如果5秒内没确认任务，会重新回到队列。默认为3秒
        $job->setWaitConfirmTime(5);

        // 投递任务
        $queue->producer()->push($job);

        // 确认一个任务
        $queue->consumer()->confirm($job);
    }

    // 消费任务
    public function consumer()
    {
        // 获取队列
        $queue = $this->createQueue();

        ### 消费任务
        // 获取到需要消费的任务
        $job = $queue->consumer()->pop();
        
        if (!$job) {
            $this->writeJson(Status::CODE_OK, [], '没有队列任务需要消费了!');
            return false;
        }
        
        // 获取需要消费的任务的数据
        $jobData = $job->getJobData();
        var_dump($jobData);
    }
}
```

## 在框架中生产任务和自定义进程中消费任务
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

### 定义消费进程
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class QueueProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function (){
            MyQueue::getInstance()->consumer()->listen(function (Job $job){
                var_dump($job->getJobData());
            });
        });
    }
}
```

> 支持多进程、多协程消费

### 注册队列驱动器、消费进程及设置生产者投递任务
```php
<?php

namespace EasySwoole\EasySwoole;

use App\Utility\MyQueue;
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
        // redis pool 使用请看 redis 章节文档
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig(
            [
                'host' => '127.0.0.1', // 服务端地址 默认为 '127.0.0.1'
                'port' => 6379, // 端口 默认为 6379
                'auth' => '', // 密码 默认为 不设置
                'db'   => 0, // 默认为 0 号库
            ]
        );
        // 配置 队列驱动器
        $driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig, 'easyswoole_queue');
        MyQueue::getInstance($driver);
        // 注册一个消费进程
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'QueueProcess', // 设置 自定义进程名称
            'processGroup' => 'Queue', // 设置 自定义进程组名称
            'enableCoroutine' => true, // 设置 自定义进程自动开启协程
        ]);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new QueueProcess($processConfig));
        // 模拟生产者，可以在任意位置投递
        $register->add($register::onWorkerStart, function ($server, $id) {
            if ($id == 0) {
                Timer::getInstance()->loop(3000, function () {
                    $job = new Job();
                    $job->setJobData(['time' => \time()]);
                    MyQueue::getInstance()->producer()->push($job);
                });
            }
        });
    }
}
```
> 进程安全退出问题请看 [自定义进程 章节](/Components/Component/process.md)。

## 进阶使用
我们可以自定义驱动，实现 `RabbitMQ`、`Kafka` 等消费队列软件的封装。

用户需要定义类，并实现 `\EasySwoole\Queue\QueueDriverInterface` 接口的几个方法即可。该接口的详细实现请看下文。

### QueueDriverInterface 接口类实现
```php
<?php

namespace EasySwoole\Queue;

interface QueueDriverInterface
{
    public function push(Job $job,float $timeout = 3.0): bool;

    public function pop(float $timeout = 3.0, array $params = []): ?Job;

    public function info(): ?array;

    public function confirm(Job $job,float $timeout = 3.0): bool;
}
```

### 组件自带的 Redis 队列驱动器实现
```php
<?php

namespace EasySwoole\Queue\Driver;

use EasySwoole\Queue\Job;
use EasySwoole\Queue\QueueDriverInterface;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Pool;
use Swoole\Coroutine;

class RedisQueue implements QueueDriverInterface
{
    protected $pool;
    protected $queueName;
    protected $lastCheckDelay = null;

    public function __construct(RedisConfig $config,string $queueName = 'es_q')
    {
        $this->pool = new Pool($config);
        $this->queueName = $queueName;
    }

    public function push(Job $job,float $timeout = 3.0): bool
    {
        if($job->getDelayTime() > 0){
            return $this->pool->invoke(function ($redis)use($job){
                /** @var $redis Redis */
                return $redis->zAdd("{$this->queueName}_d",time() + $job->getDelayTime(),serialize($job));
            },$timeout);
        }else{
            return $this->pool->invoke(function($redis)use($job){
                /** @var $redis Redis */
                return $redis->rPush($this->queueName,serialize($job));
            },$timeout);
        }
    }

    public function pop(float $timeout = 3.0, array $params = []): ?Job
    {
        // 检查当前秒数的延迟任务是否存在未执行任务。
        if($this->lastCheckDelay != time()){
            $this->lastCheckDelay = time();
            Coroutine::create(function ()use($timeout){
                $this->pool->invoke(function ($redis){
                    /** @var $redis Redis */
                    $list = $redis->zCount("{$this->queueName}_d",0,$this->lastCheckDelay);
                    if($list > 0){
                        $jobs = $redis->zPopmin("{$this->queueName}_d",$list);
                        if(is_array($jobs)){
                            foreach ($jobs as $tempJob => $time){
                                if($time > $this->lastCheckDelay){
                                    $redis->zAdd("{$this->queueName}_d",$time,$tempJob);
                                }else{
                                    //插入到队列头
                                    $redis->lPush($this->queueName,$tempJob);
                                }
                            }
                        }
                    }
                },$timeout);
            });
        }
        $job = $this->pool->invoke(function ($redis){
            /** @var $redis Redis */
            return $redis->lPop($this->queueName);
        },$timeout);
        if($job){
            $job = unserialize($job);
        }
        if(!$job instanceof Job){
            return null;
        }
        // 需要确认的任务
        if($job->getRetryTimes() != 0){
            // 到达最大的执行次数
            if($job->getRunTimes() >= $job->getRetryTimes()){
                $this->pool->invoke(function ($redis)use($job){
                    /** @var $redis Redis */
                    $redis->hDel("{$this->queueName}_c",$job->getJobId());
                },$timeout);
                return null;
            }
            // 如果不是第一次执行
            if($job->getRunTimes() !== 0){
                $hashConfirm = $this->pool->invoke(function ($redis)use($job){
                    /** @var $redis Redis */
                    return $redis->hGet("{$this->queueName}_c",$job->getJobId());
                },$timeout);
                // 说明该任务已经被确认删除
                if($hashConfirm != 1){
                    return null;
                }
            }
            // 丢到延迟队列中。
            $temp = clone $job;
            $temp->setRunTimes($temp->getRunTimes() + 1);
            $temp->setDelayTime($temp->getWaitConfirmTime());
            $this->push($temp);
            // 标记为待确认。
            $this->pool->invoke(function ($redis)use($temp){
                /** @var $redis Redis */
                $redis->hSet("{$this->queueName}_c",$temp->getJobId(),1);
            },$timeout);
        }
        $job->setRunTimes($job->getRunTimes() + 1);
        return $job;
    }

    public function confirm(Job $job,float $timeout = 3.0): bool
    {
        if($job->getRetryTimes() != 0){
            $this->pool->invoke(function ($redis)use($job){
                /** @var $redis Redis */
                $redis->hDel("{$this->queueName}_c",$job->getJobId());
            });
            return true;
        }else{
            return false;
        }
    }

    public function info(): ?array
    {
        return $this->pool->invoke(function ($redis){
            /** @var $redis Redis */
            return [
                'runningQueue'=>$redis->lLen($this->queueName),
                'delayQueue'=>$redis->zCard("{$this->queueName}_c")
            ];
        });
    }
}
```

## 相关仓库

[EasySwoole 中利用 Redis 实现消息队列](https://www.umdzz.cn/article/36/easyswooleredis)  

[如何利用 EasySwoole 多进程多协程 Redis 队列实现爬虫](https://www.umdzz.cn/article/37/easyswooleredis)