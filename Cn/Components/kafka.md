---
title: easyswoole kafka队列客户端
meta:
  - name: description
    content: easyswoole kafka队列客户端
  - name: keywords
    content: easyswoole kafka客户端|swoole kafka协程客户端
---
# kafka
Kafka是一种高吞吐量的分布式发布订阅消息系统，有如下特性：
  通过O(1)的磁盘数据结构提供消息的持久化，这种结构对于即使数以TB的消息存储也能够保持长时间的稳定性能。
  高吞吐量：即使是非常普通的硬件Kafka也可以支持每秒数百万的消息。
  支持通过Kafka服务器和消费机集群来分区消息。
  支持Hadoop并行数据加载。

> 本项目代码参考自 https://github.com/weiboad/kafka-php

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.4.5
- easyswoole/component: ^2.0
 -easyswoole/spl: ^1.1

## 安装方法

> composer require easyswoole/kafka


## 仓库地址

[easyswoole/kafka](https://github.com/easy-swoole/kafka)

## 基本使用
### 注册kafka服务
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
use EasySwoole\Kafka\Config\ProducerConfig;
use EasySwoole\Kafka\kafka;

class Process extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            $config = new ProducerConfig();
            $config->setMetadataBrokerList('127.0.0.1:9092,127.0.0.1:9093');
            $config->setBrokerVersion('0.9.0');
            $config->setRequiredAck(1);

            $kafka = new kafka($config);
            $result = $kafka->producer()->send([
                [
                    'topic' => 'test',
                    'value' => 'message--',
                    'key'   => 'key--',
                ],
            ]);

            var_dump($result);
            var_dump('ok');
        });
    }
}
```


### 消费者
```php
namespace App\Consumer;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Kafka\Config\ConsumerConfig;
use EasySwoole\Kafka\kafka;

class Process extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            $config = new ConsumerConfig();
            $config->setRefreshIntervalMs(1000);
            $config->setMetadataBrokerList('127.0.0.1:9092,127.0.0.1:9093');
            $config->setBrokerVersion('0.9.0');
            $config->setGroupId('test');

            $config->setTopics(['test']);
            $config->setOffsetReset('earliest');

            $kafka = new kafka($config);
            // 设置消费回调
            $func = function ($topic, $partition, $message) {
                var_dump($topic);
                var_dump($partition);
                var_dump($message);
            };
            $kafka->consumer()->subscribe($func);
        });
    }
}

```

## 附赠
1. Kafka 集群部署 docker-compose.yml 一份，使用方式如下
    1. 保证2181,9092,9093,9000端口未被占用（占用后可以修改compose文件中的端口号）
    2. 根目录下，docker-compose up -d
    3. 访问localhost:9000，可以查看kafka集群状态。
    
> https://github.com/easy-swoole/kafka/blob/master/docker-compose.yml
    

