---
title: easyswoole rpc客户端
meta:
  - name: description
    content: easyswoole rpc客户端
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---

# Rpc-Client

## 前提

1、配置好和 `rpc` 服务端一致的节点管理器，代码如下所示：

```php
<?php

namespace App\RpcServices\NodeManager;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Pool;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\NodeManagerInterface;
use EasySwoole\Rpc\Server\ServiceNode;

class RedisManager implements NodeManagerInterface
{
    protected $redisKey;

    protected $ttl;

    /**
     * @var Pool $pool
     */
    protected $pool;

    public function __construct(RedisPool $pool, string $hashKey = 'rpc', int $ttl = 30)
    {
        $this->pool = $pool;
        $this->redisKey = $hashKey;
        $this->ttl = $ttl;
    }

    function getNodes(string $serviceName, ?int $version = null): array
    {
        $fails = [];
        $hits = [];
        $time = time();

        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->defer(15);

        try {
            $nodes = $redis->hGetAll("{$this->redisKey}_{$serviceName}");

            $nodes = $nodes ?: [];

            foreach ($nodes as $nodeId => $value) {
                $node = json_decode($value, true);
                if ($time - $node['lastHeartbeat'] > $this->ttl) {
                    $fails[] = $nodeId;
                    continue;
                }
                if ($node['service'] === $serviceName) {
                    if ($version !== null && $version === $node['version']) {
                        $serviceNode = new ServiceNode($node);
                        $serviceNode->setNodeId(strval($nodeId));
                        $hits[$nodeId] = $serviceNode;
                    } else {
                        $serviceNode = new ServiceNode($node);
                        $serviceNode->setNodeId(strval($nodeId));
                        $hits[] = $serviceNode;
                    }
                }
            }
            if (!empty($fails)) {
                foreach ($fails as $failKey) {
                    $this->deleteServiceNode($serviceName, $failKey);
                }
            }
            return $hits;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return [];
    }

    function getNode(string $serviceName, ?int $version = null): ?ServiceNode
    {
        $list = $this->getNodes($serviceName, $version);
        if (empty($list)) {
            return null;
        }
        $allWeight = 0;

        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);

        $time = time();

        try {
            foreach ($list as $node) {
                /** @var ServiceNode $nodee */
                $key = $node->getNodeId();
                $nodeConfig = $redis->hGet("{$this->redisKey}_{$serviceName}", $key);
                $nodeConfig = json_decode($nodeConfig, true);
                $lastFailTime = $nodeConfig['lastFailTime'];
                if ($time - $lastFailTime >= 10) {
                    $weight = 10;
                } else {
                    $weight = abs(10 - ($time - $lastFailTime));
                }
                $allWeight += $weight;
                $node->__weight = $weight;
            }
            mt_srand(intval(microtime(true)));
            $allWeight = rand(0, $allWeight - 1);
            foreach ($list as $node) {
                $allWeight = $allWeight - $node->__weight;
                if ($allWeight <= 0) {
                    return $node;
                }
            }
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return null;
    }

    function failDown(ServiceNode $serviceNode): bool
    {
        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $nodeConfig = $redis->hGet($hashKey, $nodeId);
            $nodeConfig = json_decode($nodeConfig, true);
            $nodeConfig['lastFailTime'] = time();
            $redis->hSet($hashKey, $nodeId, json_encode($nodeConfig));
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    function offline(ServiceNode $serviceNode): bool
    {
        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $redis->hDel($hashKey, $nodeId);
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    function alive(ServiceNode $serviceNode): bool
    {
        $info = [
            'service' => $serviceNode->getService(),
            'ip' => $serviceNode->getIp(),
            'port' => $serviceNode->getPort(),
            'version' => $serviceNode->getVersion(),
            'lastHeartbeat' => time(),
            'lastFailTime' => 0
        ];

        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->getObj();

        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $redis->hSet($hashKey, $nodeId, json_encode($info));
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    private function deleteServiceNode($serviceName, $failKey): bool
    {
        /** @var Pool $redisPool */
        $redisPool = $this->pool->getPool();

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $redis->hDel("{$this->redisKey}_{$serviceName}", $failKey);
            return true;
        } catch (\Throwable $throwable) {
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }
}
```

2、在全局事件（即项目根目录的 `EasySwooleEvent.php` 文件中）注册 `Redis` 连接池

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        ###### 注册 Redis 连接池 ######
        $redisConfig = new RedisConfig([
            'host' => '127.0.0.1', // rpc 服务端使用的 Redis 节点地址
            'port' => 6379 // rpc 服务端使用的 Redis 节点端口
        ]);
        RedisPool::getInstance()->register($redisConfig);
    }
}
```

## 控制器聚合调用

```php
<?php

namespace App\HttpController;

use App\RpcServices\NodeManager\RedisManager;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\Protocol\Response;

class Index extends Controller
{
    public function index()
    {
        $redisNodeManager = new RedisManager(RedisPool::getInstance());

        // 使用 Redis 节点管理器
        $config = new \EasySwoole\Rpc\Config($redisNodeManager);
        $config->setNodeId('EasySwooleRpcNode1');

        $rpc = new \EasySwoole\Rpc\Rpc($config);
        $ret = [];
        $client = $rpc->client();

        /**
         * 调用商品列表
         */
        $ctx1 = $client->addRequest('Goods.GoodsModule.list');
        // 设置调用成功执行回调
        $ctx1->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'list' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        /**
         * 调用信箱公共
         */
        $ctx2 = $client->addRequest('Common.CommonModule.mailBox');
        // 设置调用成功执行回调
        $ctx2->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'mailBox' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        /**
         * 获取系统时间
         */
        $ctx2 = $client->addRequest('Common.CommonModule.serverTime');
        // 设置调用成功执行回调
        $ctx2->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'serverTime' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        // 执行调用
        $client->exec();
        $this->writeJson(200, $ret);
    }
}
```

> 注意，控制器中可以这样调用，是因为服务端章节中，在 `EasySwoole` 的全局事件 （EasySwooleEvent.php） 中已经对当前的 `Rpc` 实例定义注册了节点管理器。因此在控制器中调用的时候该 `Rpc` 实例时需要设置对应的节点才可以成功调用。一般来说，在做聚合网关的节点，是不需要注册服务进去的，仅需注册节点管理器即可。

## 客户端

> 当 `rpc` 服务和客户端不在同一服务中时，并且服务端使用的是 `EasySwoole`，客户端使用的 `swoole` 时，可以采用下面这种方式进行调用。

```php
<?php
/**
 * User: XueSi
 * Email: <1592328848@qq.com>
 * Date: 2021/5/30
 * Time: 16:05
 */
require_once __DIR__ . '/vendor/autoload.php';

go (function () {
    ###### 注册 Redis 连接池 ######
    $redisConfig = new \EasySwoole\Redis\Config\RedisConfig([
        'host' => '127.0.0.1', // rpc 服务端使用的 Redis 节点地址
        'port' => 6379 // rpc 服务端使用的 Redis 节点端口
    ]);

    \EasySwoole\RedisPool\RedisPool::getInstance()->register($redisConfig);


    ###### 配置 rpc 客户端 ######
    $redisNodeManager = new \App\RpcServices\NodeManager\RedisManager(\EasySwoole\RedisPool\RedisPool::getInstance());

    // 使用 Redis 节点管理器
    $config = new \EasySwoole\Rpc\Config($redisNodeManager);
    $config->setNodeId('EasySwooleRpcNode1');

    $rpc = new \EasySwoole\Rpc\Rpc($config);
    $ret = [];
    $client = $rpc->client();

    /**
     * 调用商品列表
     */
    $ctx1 = $client->addRequest('Goods.GoodsModule.list');
    // 设置调用成功执行回调
    $ctx1->setOnSuccess(function (\EasySwoole\Rpc\Protocol\Response $response) use (&$ret) {
        $ret[] = [
            'list' => [
                'msg' => $response->getMsg(),
                'result' => $response->getResult()
            ]
        ];
    });

    /**
     * 调用信箱公共
     */
    $ctx2 = $client->addRequest('Common.CommonModule.mailBox');
    // 设置调用成功执行回调
    $ctx2->setOnSuccess(function (\EasySwoole\Rpc\Protocol\Response $response) use (&$ret) {
        $ret[] = [
            'mailBox' => [
                'msg' => $response->getMsg(),
                'result' => $response->getResult()
            ]
        ];
    });

    /**
     * 获取系统时间
     */
    $ctx2 = $client->addRequest('Common.CommonModule.serverTime');
    // 设置调用成功执行回调
    $ctx2->setOnSuccess(function (\EasySwoole\Rpc\Protocol\Response $response) use (&$ret) {
        $ret[] = [
            'serverTime' => [
                'msg' => $response->getMsg(),
                'result' => $response->getResult()
            ]
        ];
    });

    // 执行调用
    $client->exec();

    var_dump('调用结果：');
    var_dump($ret);
});

swoole_timer_clear_all();
````
