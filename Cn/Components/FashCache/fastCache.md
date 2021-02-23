---
title: easyswoole fastcache
meta:
  - name: description
    content: easyswoole fastcache,利用swoole自定义进程实现简单的本机缓存
  - name: keywords
    content:  easyswoole fastcache
---

# FastCache
EasySwoole 提供了一个快速缓存，是基础UnixSock通讯和自定义进程存储数据实现的，提供基本的缓存服务，本缓存为解决小型应用中，需要动不动就部署Redis服务而出现。

## 组件要求

- php: >=7.1.0
- easyswoole/component: ^2.0
- easyswoole/spl: ^1.1

## 安装方法

> composer require easyswoole/fast-cache

## 仓库地址

[easyswoole/fast-cache](https://github.com/easy-swoole/fast-cache)

## 基本使用

### 服务注册

我们在EasySwoole全局的事件中进行注册
```php
use EasySwoole\FastCache\Cache;
//在最新的2.x中，改为config配置文件配置
$config = new \EasySwoole\FastCache\Config();
$config->setTempDir(EASYSWOOLE_TEMP_DIR);
Cache::getInstance($config)->attachToServer(ServerManager::getInstance()->getSwooleServer());
//老版本依旧使用以下即可
Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());
```


::: warning 
 FastCache只能在服务启动之后使用,需要有创建unix sock权限(建议使用vm,docker或者linux系统开发),虚拟机共享目录文件夹是无法创建unix sock监听的
:::

### 客户端调用
服务启动后，可以在任意位置调用
```php
use EasySwoole\FastCache\Cache;
Cache::getInstance()->set('get','a');
var_dump(Cache::getInstance()->get('get'));
```

###  相关方法
> 设置临时目录
- public function setTempDir(string $tempDir): Cache

> 设置缓存进程数  
- public function setProcessNum(int $num): Cache
  
> 设置缓存进程所在服务名   
- public function setServerName(string $serverName): Cache
  
> 设置定时回调，可用于数据定时落地   
- public function setOnTick($onTick): Cache

> 设置定时回调间隔  
- public function setTickInterval($tickInterval): Cache 
 
> 设置进程启动回调，可以用于数据落地恢复   
- public function setOnStart($onStart): Cache   

> 设置进程关闭回调，可以用于数据落地   
- public function setOnShutdown(callable $onShutdown): Cache  

> 设置指定 key 的值    
- public function set($key, $value, ?int $ttl = null, float $timeout = 1.0)

> 获取指定 key 的值
- public function get($key, float $timeout = 1.0)

> 删除指定 key的值
- public function unset($key, float $timeout = 1.0)

> 获取所有key的值
- public function keys($key = null, float $timeout = 1.0): ?array

> 清空所有进程的数据
- public function flush(float $timeout = 1.0)

> 推入队列
- public function enQueue($key, $value, $timeout = 1.0)

> 从队列中取出
- public function deQueue($key, $timeout = 1.0)

> 获取队列当前长度
- public function queueSize($key, $timeout = 1.0)

> 释放队列
- public function unsetQueue($key, $timeout = 1.0)

> 返回当前队列的全部key名称
- public function queueList($timeout = 1.0): ?array

> 清空所有队列
- public function flushQueue(float $timeout = 1.0): bool

> 设置一个key的过期时间
- public function expire($key, int $ttl, $timeout = 1.0)

> 移除一个key的过期时间   
- public function persist($key, $timeout = 1.0)

> 查看某个key的ttl   
- public function ttl($key, $timeout = 1.0)

> 将哈希表 key 中的字段 field 的值设为 value
- function hSet($key, $field, $value, float $timeout = 1.0)

> 获取存储在哈希表中指定字段的值
- function hGet($key, $field = null, float $timeout = 1.0)

> 删除一个哈希表字段
- function hDel($key, $field = null, float $timeout = 1.0)

> 清空所有
- function hFlush(float $timeout = 1.0)

> 获取所有哈希表中的字段
- function hKeys($key, float $timeout = 1.0)

> 迭代哈希表中的键值对
- function hScan($key, $cursor = 0, $limit = 10, float $timeout = 1.0)

> 只有在字段 field 不存在时，设置哈希表字段的值
- function hSetnx($key, $field, $value, float $timeout = 1.0)

> 查看哈希表 key 中，指定的字段是否存在
- function hExists($key, $field, float $timeout = 1.0)

> 获取哈希表中字段的数量
- function hLen($key, float $timeout = 1.0)

> 为哈希表 key 中的指定字段的整数值加上
- function hIncrby($key, $field, $value, float $timeout = 1.0)

> 同时将多个 field-value对设置到哈希表 key 中
- function hMset($key, $fieldValues, float $timeout = 1.0)

> 获取所有给定字段的值
- function hMget($key, $fields, float $timeout = 1.0)

> 获取哈希表中所有值
- function hVals($key, float $timeout = 1.0)

> 获取在哈希表中指定 key 的所有字段和值
- function hGetAll($key, float $timeout = 1.0)




### 落地重启恢复数据方案

FastCache提供了3个方法,用于数据落地以及重启恢复,在`EasySwooleEvent.php`中的`mainServerCreate`回调事件中设置以下方法:


::: warning 
 设置回调要在注册cache服务之前，注册服务之后不能更改回调事件。 
:::

```php
<?php

use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\CacheProcessConfig;
use EasySwoole\FastCache\SyncData;
use EasySwoole\Utility\File;

// 每隔5秒将数据存回文件
Cache::getInstance()->setTickInterval(5 * 1000);//设置定时频率
Cache::getInstance()->setOnTick(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
    $data = [
        'data'  => $SyncData->getArray(),
        'queue' => $SyncData->getQueueArray(),
        'ttl'   => $SyncData->getTtlKeys(),
	 // queue支持
        'jobIds'     => $SyncData->getJobIds(),
        'readyJob'   => $SyncData->getReadyJob(),
        'reserveJob' => $SyncData->getReserveJob(),
        'delayJob'   => $SyncData->getDelayJob(),
        'buryJob'    => $SyncData->getBuryJob(),
    ];
    $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
    File::createFile($path,serialize($data));
});

// 启动时将存回的文件重新写入
Cache::getInstance()->setOnStart(function (CacheProcessConfig $cacheProcessConfig) {
    $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
    if(is_file($path)){
        $data = unserialize(file_get_contents($path));
        $syncData = new SyncData();
        $syncData->setArray($data['data']);
        $syncData->setQueueArray($data['queue']);
        $syncData->setTtlKeys(($data['ttl']));
        // queue支持
        $syncData->setJobIds($data['jobIds']);
        $syncData->setReadyJob($data['readyJob']);
        $syncData->setReserveJob($data['reserveJob']);
        $syncData->setDelayJob($data['delayJob']);
        $syncData->setBuryJob($data['buryJob']);
        return $syncData;
    }
});

// 在守护进程时,php easyswoole stop 时会调用,落地数据
Cache::getInstance()->setOnShutdown(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
    $data = [
        'data'  => $SyncData->getArray(),
        'queue' => $SyncData->getQueueArray(),
        'ttl'   => $SyncData->getTtlKeys(),
         // queue支持
        'jobIds'     => $SyncData->getJobIds(),
        'readyJob'   => $SyncData->getReadyJob(),
        'reserveJob' => $SyncData->getReserveJob(),
        'delayJob'   => $SyncData->getDelayJob(),
        'buryJob'    => $SyncData->getBuryJob(),
    ];
    $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
    File::createFile($path,serialize($data));
});

Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());

```

## 消息队列支持

如具体查看消息队列，请查看[FastCacheQueue](fastCacheQueue.html)
