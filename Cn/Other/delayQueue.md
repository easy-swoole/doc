---
title: easyswoole 基于Redis组件实现延迟队列
meta:
  - name: description
    content: easyswoole 延迟队列
  - name: keywords
    content: easyswoole 延迟队列|swoole|swoole 框架|easyswoole 框架
---

# EasySwoole 基于Redis组件实现延迟队列

## 介绍

在用户要支付订单的时候，如果超过30分钟未支付，会把订单关掉。当然我们可以做一个定时任务，每个一段时间来扫描未支付的订单，如果该订单超过支付时间就关闭，但是在数据量小的时候并没有什么大的问题，但是数据量一大轮训数据库的方式就会变得特别耗资源。当面对千万级、上亿级数据量时，本身写入的IO就比较高，导致长时间查询或者根本就查不出来，更别说分库分表以后了。

使用延迟队列解决的痛点无非是

1. 实现了数据延迟
2. 数据摊开(仔细去理解)

## 知识点

1. [redis有序集合](https://www.runoob.com/redis/redis-sorted-sets.html)
2. [EasySwoole Redis协程客户端](http://www.easyswoole.com/Cn/Components/Redis/introduction.html)

## 案例

生成订单id ---> 扔到延迟队列 ---> 延迟队列消费进程不停获取30分钟前的订单满足条件的订单 ---> 处理订单

## 直接上代码

#### EasySwooleEvent.php 注册redis连接池、注册延迟队列消费进程

````php
<?php
namespace EasySwoole\EasySwoole;

use App\Process\Consumer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Pool\Manager;
use EasySwoole\Redis\Config\RedisConfig;
use App\RedisPool\RedisPool;
use EasySwoole\Pool\Config;
class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {

        //TODO:: 注册redis连接池
        $config = new Config();
        $redisConfig1 = new RedisConfig([
            'host'      => '127.0.0.1',
            'port'      => '6379'
        ]);

        // 这里的redis连接池看文档配吧
        Manager::getInstance()->register(new RedisPool($config,$redisConfig1),'redis');

        //TODO:: 延迟队列消费进程
        $processConfig= new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('testProcess');

        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new Consumer($processConfig));
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

````

#### 扔到延迟队列

````php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Pool\Manager;

class Index extends Controller
{

    function index()
    {
        /** @var $redis \EasySwoole\Redis\Redis*/
        $orderId = date('YmdHis', time());
        $redis = Manager::getInstance()->get('redis')->getObj();
        $res = $redis->zAdd('delay_queue_test1', time(), $orderId);
        if ($res) {
            $this->writeJson(200, '订单添加成功:'.$orderId);
        }
    }

}
````

#### 延迟队列消费进程

````php
<?php
namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Pool\Manager;
use Swoole\Coroutine;

class Consumer extends AbstractProcess {
    protected function run($arg)
    {
        go(function (){
            while (true) {

                //TODO:: 拿到redis
                /** @var $redis \EasySwoole\Redis\Redis*/
                $redis = Manager::getInstance()->get('redis')->defer();

                //TODO:: 从有序集合中拿到三秒(模拟30分钟)以前的订单
                $orderIds = $redis->zRangeByScore('delay_queue_test1', 0, time()-3, ['withscores' => TRUE]);

                if (empty($orderIds)) {
                    Coroutine::sleep(1);
                    continue;
                }

                //TODO::拿出后立马删除
                $redis->zRem('delay_queue_test1', ...$orderIds);

                foreach ($orderIds as $orderId)
                {
                    var_dump($orderId);

                    //TODO::判断此订单30分钟后，是否仍未完成，做相应处理
                }
            }
        });
    }

}
````

## 测试

#### 请求index/index 投递订单到延迟队列

````php
➜  ~ curl 127.0.0.1:9501/index/index
{"code":200,"result":"订单添加成功:20200422004046","msg":null}%
````

#### 等3s看终端是否输出

````php
➜  easyswoole php easyswoole start
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/
main server                   SWOOLE_WEB
listen address                0.0.0.0
listen port                   9501
ip@en0                        192.168.43.57
worker_num                    8
reload_async                  true
max_wait_time                 3
pid_file                      /Users/xx/sites/easyswoole/Temp/pid.pid
log_file                      /Users/xx/sites/easyswoole/Log/swoole.log
user                          xx
daemonize                     false
swoole version                4.4.15
php version                   7.2.18
easy swoole                   3.3.7
develop/produce               develop
temp dir                      /Users/xx/sites/easyswoole/Temp
log dir                       /Users/xx/sites/easyswoole/Log

string(14) "20200422004046"
````

## 总结

这只是一个思路，大家可以根据实际业务做不同调整
