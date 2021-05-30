---
title: easyswoole rpc服务注册中心
meta:
  - name: description
    content: easyswoole rpc服务注册中心
  - name: keywords
    content: easyswoole服务注册中心|swoole服务注册|swoole微服务|swoole分布式
---

# EasySwoole RPC 自定义注册中心

`EasySwoole` 默认为通过 `UDP` 广播 + 自定义进程定时刷新自身节点信息的方式来实现无主化/注册中心的服务发现。在服务正常关闭的时候，自定义定时进程的`onShutdown` 方法会执行 `deleteServiceNode` 方法来实现节点下线。在非正常关闭的时候，心跳超时也会被节点管理器踢出。

有些情况，不方便用 `UDP` 广播的情况下，那么 `EasySwoole` 支持你自定义一个节点管理器，来变更服务发现方式。

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

::: warning 
 即使关闭了 `UDP` 定时广播，`EasySwoole Rpc` 的 `` 进程依旧会每 5 秒执行一次 `serviceAlive` 用于更新自身的节点心跳信息。
:::
