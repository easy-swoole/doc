---
title: easyswoole Implementation of delay queue based on redis component
meta:
  - name: description
    content: easyswoole Delay queue
  - name: keywords
    content: easyswoole Delay queue|swoole|swoole framework|easyswoole framework
---

# EasySwoole Implementation of delay queue based on redis component

## introduce

When the user wants to pay for the order, if it is not paid for more than 30 minutes, the order will be closed. Of course, we can do a regular task to scan the unpaid orders every period of time. If the order exceeds the payment time, it will be closed. However, when the amount of data is small, there is no big problem. However, when the amount of data is large, the way of rotating training database will become particularly resource consuming. When faced with tens of millions or hundreds of millions of data, the IO written by itself is relatively high, resulting in long-time query or no query at all, let alone after database and table splitting.

Using delay queue to solve the pain point is nothing more than

1. Realize data delay
2. Spread out the data (understand it carefully)

## Knowledge points

1. [Redis ordered set](https://www.runoob.com/redis/redis-sorted-sets.html)
2. [EasySwoole Redis coroutine client](http://www.easyswoole.com/Cn/Components/Redis/introduction.html)

## case

Generate order ID --- > throw it into delay queue --- > the consumption process of delay queue keeps getting the order 30 minutes ago and the order meeting the condition --- > process the order

## Code directly

#### EasySwooleEvent.php  Register redis connection pool, register delay queue consumption process

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

        //TODO:: Register redis connection pool
        $config = new Config();
        $redisConfig1 = new RedisConfig([
            'host'      => '127.0.0.1',
            'port'      => '6379'
        ]);

        Manager::getInstance()->register(new RedisPool($config,$redisConfig1),'redis');

        //TODO:: Delay queue consumption process
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

#### Drop to delay queue

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
            $this->writeJson(200, 'Order added successfully:'.$orderId);
        }
    }

}
````

#### Delay queue consumption process

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

                //TODO:: Get redis
                /** @var $redis \EasySwoole\Redis\Redis*/
                $redis = Manager::getInstance()->get('redis')->defer();

                //TODO:: Get orders three seconds (30 minutes) ago from the ordered set
                $orderIds = $redis->zRangeByScore('delay_queue_test1', 0, time()-3, ['withscores' => TRUE]);

                if (empty($orderIds)) {
                    Coroutine::sleep(1);
                    continue;
                }

                //TODO::Take it out and delete it immediately
                $redis->zRem('delay_queue_test1', ...$orderIds);

                foreach ($orderIds as $orderId)
                {
                    var_dump($orderId);

                    //TODO::Judge whether the order has not been completed 30 minutes later and deal with it accordingly
                }
            }
        });
    }

}
````

## test 

#### Request index/index post order to delay queue

````php
➜  ~ curl 127.0.0.1:9501/index/index
{"code":200,"result":"订单添加成功:20200422004046","msg":null}%
````

#### Wait 3S to see if the terminal outputs

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

## summary

This is just a train of thought, we can make different adjustments according to the actual business
