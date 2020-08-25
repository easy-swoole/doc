---
title: easyswoole队列服务
meta:
  - name: description
    content: easyswoole轻量级队列
  - name: keywords
    content: easyswoole队列|swoole队列|swoole多进程队列
---

# Queue介绍

Easyswoole封装实现了一个轻量级的队列，默认以Redis作为队列驱动器。

可以自己实现一个队列驱动来实现用kafka或者启动方式的队列存储。

从上可知，Queue并不是一个单独使用的组件，它更像一个对不同驱动的队列进行统一封装的门面组件。


## 组件要求

- ext-swoole: >=4.4.0
- easyswoole/component: ^2.0
- easyswoole/redis-pool: ^2.0.7

## 安装方法


> composer require easyswoole/queue

## 仓库地址

[easyswoole/queue](https://github.com/easy-swoole/queue)

## 基本使用

- 注册队列驱动器
- 设置消费进程
- 生产者投递任务

### 定义一个队列

```php
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
> 可以多进程，多协程消费

### 驱动注册
 
 ```php
 namespace EasySwoole\EasySwoole;
 
 
 use App\Utility\MyQueue;
 use App\Utility\QueueProcess;
 use EasySwoole\Component\Timer;
 use EasySwoole\EasySwoole\Swoole\EventRegister;
 use EasySwoole\EasySwoole\AbstractInterface\Event;
 use EasySwoole\Http\Request;
 use EasySwoole\Http\Response;
 use EasySwoole\Queue\Driver\Redis;
 use EasySwoole\Queue\Job;
 use EasySwoole\Redis\Config\RedisConfig;
 use EasySwoole\RedisPool\RedisPool;
 use EasySwoole\Utility\Time;
 
 
 class EasySwooleEvent implements Event
 {
 
     public static function initialize()
     {
         // TODO: Implement initialize() method.
         date_default_timezone_set('Asia/Shanghai');
     }
 
     public static function mainServerCreate(EventRegister $register)
     {
         //redis pool使用请看redis 章节文档
         $config = new RedisConfig([
             'host'=>'127.0.0.1'
         ]);
         $redis = new RedisPool($config);
         $driver = new Redis($redis);
         MyQueue::getInstance($driver);
         //注册一个消费进程
         \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new QueueProcess());
         //模拟生产者，可以在任意位置投递
         $register->add($register::onWorkerStart,function ($ser,$id){
             if($id == 0){
                 Timer::getInstance()->loop(3000,function (){
                    $job = new Job();
                    $job->setJobData(['time'=>\time()]);
                    MyQueue::getInstance()->producer()->push($job);
                 });
             }
         });
 
     }
 
     public static function onRequest(Request $request, Response $response): bool
     {
         // TODO: Implement onRequest() method.
         return true;
     }
 
     public static function afterRequest(Request $request, Response $response): void
     {
         // TODO: Implement afterAction() method.
     }
 }
 ```
 
 > 进程安全退出问题请看[进程章节](/Cn/Components/Component/process.md)。


## 进阶使用

我们可以自定义驱动，实现RabbitMQ等消费队列软件的封装。

定义类，并继承`EasySwoole\Queue\QueueDriverInterface`接口，实现几个方法即可。

### QueueDriverInterface 类实现

```php
namespace EasySwoole\Queue;
interface QueueDriverInterface
{
    public function push(Job $job):bool ;
    public function pop(float $timeout = 3.0):?Job;
    public function size():?int ;
}
```

### 组件自带的Redis驱动实现

```php
namespace EasySwoole\Queue\Driver;


use EasySwoole\Queue\Job;
use EasySwoole\Queue\QueueDriverInterface;
use EasySwoole\Redis\Redis as Connection;
use EasySwoole\RedisPool\RedisPool;

class Redis implements QueueDriverInterface
{

    protected $pool;
    protected $queueName;
    public function __construct(RedisPool $pool,string $queueName = 'EasySwoole')
    {
        $this->pool = $pool;
        $this->queueName = $queueName;
    }

    public function push(Job $job): bool
    {
        $data = $job->__toString();
        return $this->pool->invoke(function (Connection $connection)use($data){
            return $connection->lPush($this->queueName,$data);
        });
    }

    public function pop(float $timeout = 3.0): ?Job
    {
        return $this->pool->invoke(function (Connection $connection){
            $data =  json_decode($connection->rPop($this->queueName),true);
            if(is_array($data)){
                return new Job($data);
            }else{
                return null;
            }
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

## 相关仓库

[EasySwoole中利用redis实现消息队列](https://www.umdzz.cn/article/36/easyswooleredis)  

[如何利用EasySwoole多进程多协程redis队列实现爬虫](https://www.umdzz.cn/article/37/easyswooleredis)


