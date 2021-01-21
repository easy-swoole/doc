---
title: easyswoole redis协程连接池
meta:
  - name: description
    content: easyswoole redis协程连接池
  - name: keywords
    content: easyswoole redis协程连接池|swoole redis连接池
---
# Redis-Pool
Redis-Pool 基于 [pool连接池管理](https://github.com/easy-swoole/pool),[redis协程客户端](https://github.com/easy-swoole/redis) 封装的组件


## 安装

```shell
composer require easyswoole/redis-pool
```


## 连接池注册

使用连接之前注册redis连接池:

```php
//redis连接池注册(config默认为127.0.0.1,端口6379)
\EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig(),'redis');
// config是空配置,用户需手动配置. 

//redis集群连接池注册
\EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ]
),'redisCluster');
```

## 连接池配置
当注册好时,将返回连接池的poolConf用于配置连接池:

```php
$redisPoolConfig = \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig());
//配置连接池连接数
$redisPoolConfig->setMinObjectNum(5);
$redisPoolConfig->setMaxObjectNum(20);

$redisClusterPoolConfig = \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ]
));
//配置连接池连接数
$redisPoolConfig->setMinObjectNum(5);
$redisPoolConfig->setMaxObjectNum(20);
```

## 使用连接池

```php
//defer方式获取连接
$redis = \EasySwoole\RedisPool\RedisPool::defer();
$redisCluster = \EasySwoole\RedisPool\RedisPool::defer();
$redis->set('a', 1);
$redisCluster->set('a', 1);

//invoke方式获取连接
\EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) {
    var_dump($redis->set('a', 1));
});
\EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) {
    var_dump($redis->set('a', 1));
});

//获取连接池对象
$redisPool = \EasySwoole\RedisPool\RedisPool::getInstance()->getPool();
$redisClusterPool = \EasySwoole\RedisPool\RedisPool::getInstance()->getPool();

$redis = $redisPool->getObj();
$redisPool->recycleObj($redis);
```
！！！注意，在未指定连接池名称是，注册的连接池名称为默认的```default```

## 方法

### register

```php
\EasySwoole\RedisPool\RedisPool::getInstance()->register();
```

参数：
- $config ```new \EasySwoole\Redis\Config\RedisConfig() || new \EasySwoole\Redis\Config\RedisClusterConfig()```
- $name 连接池名称 默认`default`
- $cask 用户自定义`redis-client` 可忽略

返回：
- 注册成功返回`EasySwoole\Pool\Config`,可设置[连接池](Components/Pool/introduction.md)的配置.

### defer

```php
\EasySwoole\RedisPool\RedisPool::defer();
```

参数：
- $name 连接池名称 默认`default`
- $timeout 取出连接超时时间

返回：
- 成功返回连接池内对象 失败为`null` 

### invoke

```php
\EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) {
    var_dump($redis->set('a', 1));
});
```

参数：
- $call 执行的闭包函数，闭包函数参数为连接池对象
- $name 连接池名称 默认`default`
- $timeout 取出连接超时时间

返回：
- 成功返回闭包函数内返回的结果，失败返回`null`

### getPool

```php
\EasySwoole\RedisPool\RedisPool::getInstance()->getPool();
```

参数：
- $name 连接池名称 默认`default`

返回：
- 成功返回`EasySwoole\RedisPool\Pool`,失败返回`null`.
