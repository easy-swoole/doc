---
title: easyswoole rpc客户端
meta:
  - name: description
    content: easyswoole rpc客户端
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---

# Rpc-Client

## 控制器聚合调用

```php
namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Rpc\Response;
use EasySwoole\Rpc\Rpc;

class Index extends Controller
{

    function index()
    {
        $ret = [];
        $client = Rpc::getInstance()->client();
        /*
         * 调用商品列表
         */
        $client->addCall('goods','list',['page'=>1])
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['goods'] = $response->toArray();
            })->setOnFail(function (Response $response)use(&$ret){
                $ret['goods'] = $response->toArray();
            });
        /*
         * 调用信箱公共
         */
        $client->addCall('common','mailBox')
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['mailBox'] = $response->toArray();
            })->setOnFail(function (Response $response)use(&$ret){
                $ret['mailBox'] = $response->toArray();
            });
        /*
        * 获取系统时间
        */
        $client->addCall('common','serverTime')
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['serverTime'] = $response->toArray();
            });

        $client->exec(2.0);

        $this->writeJson(200,$ret);
    }
}
```

> 注意，控制器中可以这样调用，是因为服务端章节中，在EasySwoole的全局启动事件已经对当前的Rpc实例定义注册了节点管理器。因此在控制器中调用的时候
> 该Rpc实例可以找到对应的节点。一般来说，在做聚合网关的节点，是不需要注册服务进去的，仅需注册节点管理器即可。

## 客户端

> 当rpc服务和客户端不在同一服务中时，并且服务端客户端使用的都是es

````php
<?php
require_once 'vendor/autoload.php';

use EasySwoole\Rpc\Config;
use EasySwoole\Rpc\Rpc;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Response;
$redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
$redisConfig->setHost('127.0.0.1'); // 服务端使用的redis节点地址
$redisConfig->setPort('6379'); // 服务端使用的redis节点端口
$pool=new \EasySwoole\RedisPool\RedisPool($redisConfig);
$config = new Config();
$config->setServerIp('127.0.0.1'); // 指定rpc服务地址
$config->setListenPort(9502); // 指定rpc服务端口
$config->setNodeManager(new RedisManager($pool));
$rpc = new Rpc($config);

\Swoole\Coroutine::create(function () use ($rpc) {
    $client = $rpc->client();
    $client->addCall('UserService', 'register', ['arg1', 'arg2'])
        ->setOnFail(function (Response $response) {
            print_r($response->toArray());
        })
        ->setOnSuccess(function (Response $response) {
            print_r($response->toArray());
        });

    $client->exec();
});
swoole_timer_clear_all();
````
