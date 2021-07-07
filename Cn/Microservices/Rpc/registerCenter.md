---
title: easyswoole rpc服务注册中心
meta:
  - name: description
    content: easyswoole rpc服务注册中心
  - name: keywords
    content: easyswoole服务注册中心|swoole服务注册|swoole微服务|swoole分布式
---

# EasySwoole RPC 自定义注册中心

`EasySwoole` 默认为通过 `UDP` 广播 + 自定义进程定时刷新自身节点信息的方式来实现无主化/注册中心的服务发现。在服务正常关闭的时候，自定义定时进程的`onShutdown`
方法会执行 `deleteServiceNode` 方法来实现节点下线。在非正常关闭的时候，心跳超时也会被节点管理器踢出。

有些情况，比如服务都不在一个网段上，由于udp协议的设置，将会广播不到，只能点对点的进行广播数据，就不是很方便。那么 `EasySwoole` 支持你自定义一个节点管理器，来变更服务注册及发现方式。

下面实现的 `Redis` 节点管理器示例是基于 `easyswoole/redis-pool` 组件 实现，所以请先执行 `composer require easyswoole/redis-pool` 安装 `redis-pool` 组件。关于 `easyswoole/redis-pool` 组件具体用户请查看 [easyswoole/redis-pool 章节](/Components/Redis/pool.md)。

## 例如使用 `Redis` 来实现

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

    public function __construct(Pool $pool, string $hashKey = 'rpc', int $ttl = 30)
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

        $redisPool = $this->pool;

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

        
        $redisPool = $this->pool;;

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
        
        $redisPool = $this->pool;;

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
        
        $redisPool = $this->pool;;

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

        
        $redisPool = $this->pool;;

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
        $redisPool = $this->pool;;

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

```php
 /** @var \EasySwoole\Rpc\Config $config */
$assistConfig = $config->getAssist();

// 服务定时自刷新到节点管理器
$assistConfig->setAliveInterval(5000);
```

> 即使关闭了 `UDP` 定时广播，`EasySwoole Rpc` 的 `AssistWorker` 进程依旧会每 5 秒执行一次 `serviceAlive` 用于更新自身的节点心跳信息。

## 注册

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;use EasySwoole\Redis\Config\RedisConfig;use EasySwoole\RedisPool\Pool;use EasySwoole\RedisPool\RedisPool;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        ###### 注册 rpc 服务 ######
        /** rpc 服务端配置 */
        // 采用了redis 节点管理器 可以关闭udp 广播了。
        $redisM = new RedisManager(new Pool(new RedisConfig(['host' => '127.0.0.1'])));
        $config = new \EasySwoole\Rpc\Config($redisM);
        $config->setNodeId('EasySwooleRpcNode1');
        $config->setServerName('EasySwoole'); // 默认 EasySwoole
        $config->setOnException(function (\Throwable $throwable) {

        });

        $serverConfig = $config->getServer();
        $serverConfig->setServerIp('127.0.0.1');

        // rpc 具体配置请看配置章节
        $rpc = new \EasySwoole\Rpc\Rpc($config);

        // 创建 Goods 服务
        $goodsService = new \App\RpcServices\Goods();
        // 添加 GoodsModule 模块到 Goods 服务中
        $goodsService->addModule(new \App\RpcServices\GoodsModule());
        // 添加 Goods 服务到服务管理器中
        $rpc->serviceManager()->addService($goodsService);

        // 创建 Common 服务
        $commonService = new \App\RpcServices\Common();
        // 添加 CommonModule 模块到 Common 服务中
        $commonService->addModule(new \App\RpcServices\CommonModule());
        // 添加 Common 服务到服务管理器中
        $rpc->serviceManager()->addService($commonService);
        
        // 此刻的rpc实例需要保存下来 或者采用单例模式继承整个Rpc类进行注册 或者使用Di
        
        // 注册 rpc 服务
        $rpc->attachServer(ServerManager::getInstance()->getSwooleServer());
        
    }
}
```
