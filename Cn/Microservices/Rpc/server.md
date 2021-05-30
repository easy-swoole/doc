---
title: easyswoole rpc服务端
meta:
  - name: description
    content: easyswoole rpc服务端
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---


# Rpc-Server

## 场景
例如在一个商场系统中，我们将商品库和系统公告两个服务切分开到不同的服务器当中。当用户打开商场首页的时候，
我们希望 `App` 向某个网关发起请求，该网关可以自动地帮我们请求商品列表和系统公共等数据，合并返回。

## 前提

先实现一个自定义节点管理器，这里使用 `Redis` 来实现。下面是一个参考实现示例，用户也可自行封装：

新建 `App\RpcServices\NodeManager.php` 文件：

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

## 服务定义

每一个 `Rpc` 服务其实就是一个 `EasySwoole\Rpc\Service\AbstractService` 类，在服务下面我们又分为多个子模块，每个子模块提供不同的服务。 如下：

## 定义商品服务

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Protocol\Request;
use EasySwoole\Rpc\Service\AbstractService;

class Goods extends AbstractService
{
    /**
     *  重写onRequest(比如可以对方法做ip拦截或其它前置操作)
     *
     * @param Request $request
     * @return bool
     */
    protected function onRequest(Request $request): bool
    {
        return true;
    }

    function serviceName(): string
    {
        return 'Goods';
    }
}
```

### 定义商品服务的子模块

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class GoodsModule extends AbstractServiceModule
{
    function moduleName(): string
    {
        return 'GoodsModule';
    }

    function list()
    {
        $this->response()->setResult([
            [
                'goodsId' => '100001',
                'goodsName' => '商品1',
                'prices' => 1124
            ],
            [
                'goodsId' => '100002',
                'goodsName' => '商品2',
                'prices' => 599
            ]
        ]);
        $this->response()->setMsg('get goods list success');
    }

    function exception()
    {
        throw new \Exception('the GoodsModule exception');

    }

    protected function onException(\Throwable $throwable)
    {
        $this->response()->setStatus(-1)->setMsg($throwable->getMessage());
    }
}
```

## 定义公共服务

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractService;

class Common extends AbstractService
{
    function serviceName(): string
    {
        return 'Common';
    }
}
```

### 定义公共服务的子模块

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class CommonModule extends AbstractServiceModule
{
    function moduleName(): string
    {
        return 'CommonModule';
    }

    public function mailBox()
    {
        $this->response()->setResult([
            [
                'mailId'=>'100001',
                'mailTitle'=>'系统消息1',
            ],
            [
                'mailId'=>'100001',
                'mailTitle'=>'系统消息1',
            ],
        ]);
        $this->response()->setMsg('get mail list success');
    }

    public function serverTime()
    {
        $this->response()->setResult(time());
        $this->response()->setMsg('get server time success');
    }
}
```

## 服务注册

在 `EasySwoole` 全局事件（即项目根目录的 `EasySwooleEvent` 文件）中，进行服务注册。至于节点管理、服务类定义等具体用法请看对应章节。

```php
<?php

namespace EasySwoole\EasySwoole;

use App\RpcServices\NodeManager\RedisManager;
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
        $redisConfig = new RedisConfig();
        RedisPool::getInstance()->register($redisConfig);

        ###### 注册 rpc 服务 ######
        /** rpc 服务端配置 */
        // 构造方法内用户可传入节点管理器实现 `NodeManagerInterface`
        $redisNodeManager = new RedisManager(RedisPool::getInstance());
        $config = new \EasySwoole\Rpc\Config($redisNodeManager);

        // 设置节点id 'EasySwooleRpcNode1'
        $config->setNodeId('EasySwooleRpcNode1');

        // 设置服务名称
        $config->setServerName('EasySwoole'); // 默认 EasySwoole
        // 【必须设置】设置异常处理器 对 Service-Worker 和 AssistWorker 的异常进行处理，防止未捕获导致进程退出
        $config->setOnException(function (\Throwable $throwable) {

        });

        $serverConfig = $config->getServer();

        // 【必须设置】设置本机ip
        $serverConfig->setServerIp('127.0.0.1');

        // 【可选操作】设置工作进程数量，默认为 4
        # $serverConfig->setWorkerNum(4);
        // 【可选操作】设置 rpc 服务端监听地址及端口，监听地址默认为 '0.0.0.0'，端口默认为 9600
        # $serverConfig->setListenAddress('0.0.0.0');
        # $serverConfig->setListenPort(9600);
        // 【可选操作】设置服务端最大接受包大小，默认为 1024 * 1024 * 2 (即2M)
        # $serverConfig->setMaxPackageSize(1024 * 1024 * 2);
        // 【可选操作】设置接收客户端数据时间，默认为 3s
        # $serverConfig->setNetworkReadTimeout(3);

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

        // 注册 rpc 服务
        $rpc->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

> 为了方便测试，我把两个服务放在同一台机器中注册。实际生产场景应该是 `N` 台机注册商品服务，`N` 台机器注册公告服务，把服务分开。

