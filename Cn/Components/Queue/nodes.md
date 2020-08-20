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
namespace App\Utility;


use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class MyQueue1 extends Queue
{
    use Singleton;
    function __construct(QueueDriverInterface $driver)
    {
        $this->driver = $driver;
        $this->atomic = new Long(0);
        $this->nodeId = '自定义nodeid';
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
namespace App\Utility;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class QueueProcess extends AbstractProcess
{

    protected function run($arg)
    {
        go(function (){
            MyQueue1::getInstance()->consumer()->listen(function (Job $job){
                var_dump($job->getJobId());
            });
            MyQueue2::getInstance()->consumer()->listen(function (Job $job){
                var_dump($job->getJobId());
            });
        });
    }
}
```
> 可以多进程，多协程消费


## 驱动注册

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
        // 这里是重点
        MyQueue1::getInstance($driver);
        MyQueue2::getInstance($driver);
        //注册一个消费进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new QueueProcess());
        //模拟生产者，可以在任意位置投递
        $register->add($register::onWorkerStart,function ($ser,$id){
            if($id == 0){
                Timer::getInstance()->loop(3000,function (){
                   $job = new Job();
                   $job->setJobData(['time'=>\time()]);
                   // 这里是重点
                   MyQueue1::getInstance()->producer()->push($job);
                   MyQueue2::getInstance()->producer()->push($job);
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
