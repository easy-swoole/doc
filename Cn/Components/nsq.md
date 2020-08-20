---
title: easyswoole nsq队列客户端
meta:
  - name: description
    content: easyswoole nsq队列客户端
  - name: keywords
    content: easyswoole nsq队列客户端|swoole nsq队列客户端
---

# NSQ客户端

NSQ 是实时的分布式消息处理平台，其设计的目的是用来大规模地处理每天数以十亿计级别的消息。
它具有分布式和去中心化拓扑结构，该结构具有无单点故障、故障容错、高可用性以及能够保证消息的可靠传递的特征。


## 组件要求

- php: >=5.3.0
- ext-json: *
- easyswoole/easyswoole: 3.x
- easyswoole/http-client: ^1.2.5
- easyswoole/pool: ^1.0
- easyswoole/spl: ^1.1
- monolog/monolog: ~1.0
- react/react: >=0.2.1

## 安装方法

> composer require easyswoole/nsq

## 仓库地址

[easyswoole/nsq](https://github.com/easy-swoole/nsq)


## 基本使用

### 注册Nsq服务
```php
namespace EasySwoole\EasySwoole;

use App\Producer\Process as ProducerProcess;
use App\Consumer\Process as ConsumerProcess;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        // 生产者
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new ProducerProcess());
        // 消费者
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new ConsumerProcess());
    }
    
    ......
    
}

```
### 生产者
```php
namespace App\Producer;

use EasySwoole\Component\Process\AbstractProcess;

class Process extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            $config = new \EasySwoole\Nsq\Config();
            $topic  = "topic.test";
            $nsqlookup = new \EasySwoole\Nsq\Lookup\Nsqlookupd($config->getNsqdUrl());
            $hosts = $nsqlookup->lookupHosts($topic);
        
            foreach ($hosts as $host) {
                $nsq = new \EasySwoole\Nsq\Nsq();
                for ($i = 0; $i < 10; $i++) {
                    $msg = new \EasySwoole\Nsq\Message\Message();
                    $msg->setPayload("test$i");
                    $nsq->push(
                        new \EasySwoole\Nsq\Connection\Producer($host, $config),
                        $topic,
                        $msg
                    );
                }
            }
        });
    }
}
```


### 消费者
```php
namespace App\Consumer;

use EasySwoole\Component\Process\AbstractProcess;

class Process extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            $topic      = "topic.test";
            $config     = new \EasySwoole\Nsq\Config();
            $nsqlookup  = new \EasySwoole\Nsq\Lookup\Nsqlookupd($config->getNsqdUrl());
            $hosts      = $nsqlookup->lookupHosts($topic);
            foreach ($hosts as $host) {
                $nsq = new \EasySwoole\Nsq\Nsq();
                $nsq->subscribe(
                    new \EasySwoole\Nsq\Connection\Consumer($host, $config, $topic, 'test.consuming'),
                    function ($item) {
                        var_dump($item['message']);
                    }
                );
            }
        });
    }
}

```

## 附赠
1. Nsq 集群部署 docker-compose.yml 一份，使用方式如下
    1. 保证4150,4151,4160,4161,4171端口未被占用（占用后可以修改compose文件中的端口号）
    2. 根目录下，docker-compose up -d
    3. 访问localhost:4171，可以查看Web版 nsqadmin 状态。
    
> https://github.com/easy-swoole/nsq/blob/master/docker-compose.yml

