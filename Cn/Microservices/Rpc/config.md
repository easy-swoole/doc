---
title: easyswoole rpc配置
meta:
  - name: description
    content: easyswoole rpc配置
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---

# RPC 组件配置及使用

## 配置

### 主配置

```php
<?php
// 构造方法内用户可传入 节点管理器实现类(实现 `NodeManagerInterface` 接口的类) 默认为 `MemoryManager`
$config = new \EasySwoole\Rpc\Config();

// 设置服务名称
$config->setServerName('User'); // 默认 EasySwoole

// 设置节点id
$config->setNodeId(\EasySwoole\Utility\Random::character(10)); // 可忽略 构造函数已经设置

// 【必须设置】设置异常处理器，对 Service-Worker 和 AssistWorker 的异常进行处理，防止未捕获导致进程退出
$config->setOnException(function (\Throwable $throwable) {

});
```

### 服务端

```php
<?php
/** @var \EasySwoole\Rpc\Config $config */
$serverConfig = $config->getServer();

// 【必须设置】设置本机ip 外网 或者 内网 ip 向其他服务端同步本机信息
$serverConfig->setServerIp('127.0.0.1');

// 设置工作进程数量
$serverConfig->setWorkerNum(4);

// 设置监听地址及端口 端口可被复用
$serverConfig->setListenAddress('0.0.0.0');
$serverConfig->setListenPort('9600');

// 设置服务端最大接受包大小
$serverConfig->setMaxPackageSize(1024 * 1024 * 2);

// 设置接收客户端数据时间
$serverConfig->setNetworkReadTimeout(3);
```

### 广播配置

```php
<?php
 /** @var \EasySwoole\Rpc\Config $config */
$assistConfig = $config->getAssist();

// 服务定时自刷新到节点管理器
$assistConfig->setAliveInterval(5000);

// 广播进程设置
$serviceFinderConfig = $assistConfig->getUdpServiceFinder();

// 监听地址和端口
$serviceFinderConfig->setEnableListen(true);
$serviceFinderConfig->setListenAddress('0.0.0.0');
$serviceFinderConfig->setListenPort(9600);

// 设置广播地址
$serviceFinderConfig->setEnableBroadcast(true);
// 255.255.255.255 udp 广播地址 
$serviceFinderConfig->setBroadcastAddress(['127.0.0.1:9600', '127.0.0.1:9601','255.255.255.255:9600']);
$serviceFinderConfig->setBroadcastInterval(5000); // 5s 广播一次

// 设置广播秘钥 
$serviceFinderConfig->setEncryptKey('EasySwoole');
```


### 客户端配置

```php
<?php
// 如果只是暴露rpc服务 不进行调用别的rpc服务 可不用设置
/** @var \EasySwoole\Rpc\Config $config */
$clientConfig = $config->getClient();

// 传输最大数据包大小
$clientConfig->setMaxPackageSize(1024 * 1024 * 2);

// 设置全局回调函数  成功及失败 $response->getStatus !== 0 全部为失败
$clientConfig->setOnGlobalSuccess(function (\EasySwoole\Rpc\Protocol\Response $response){

});
$clientConfig->setOnGlobalFail(function (\EasySwoole\Rpc\Protocol\Response $response){

});
```





## 注册服务

### 注册 rpc 服务

`EasySwooleEvent`事件`mainServerCreate`注册
```php
<?php

###### 配置服务端 ######
// 构造方法内用户可传入 节点管理器实现类(实现 `NodeManagerInterface` 接口的类) 默认为 `MemoryManager`
$config = new \EasySwoole\Rpc\Config();
// 设置服务名称
$config->setServerName('User'); // 默认 EasySwoole
// 设置节点id，可忽略，构造函数已经设置
$config->setNodeId(\EasySwoole\Utility\Random::character(10)); // 

// 【必须设置】设置异常处理器，对 Service-Worker 和 AssistWorker 的异常进行处理，防止未捕获导致进程退出
$config->setOnException(function (\Throwable $throwable) {

});

$serverConfig = $config->getServer();

// 【必须设置】设置本机ip
$serverConfig->setServerIp('127.0.0.1');


/** 
 * 注册服务 
 */
$rpc = new \EasySwoole\Rpc\Rpc($config);

// 创建 ServiceOne 服务 
$serviceOne = new \EasySwoole\Rpc\Tests\Service\ServiceOne();
// 在 ServiceOne 服务中添加 ModuleOne 模块
$serviceOne->addModule(new \EasySwoole\Rpc\Tests\Service\ModuleOne());
// 在 ServiceOne 服务中添加 ModuleTwo 模块
$serviceOne->addModule(new \EasySwoole\Rpc\Tests\Service\ModuleTwo());

// 创建 ServiceTwo 服务 
$serviceTwo = new \EasySwoole\Rpc\Tests\Service\ServiceTwo();
// 在 ServiceTwo 服务中添加 ModuleOne 模块
$serviceTwo->addModule(new \EasySwoole\Rpc\Tests\Service\ModuleOne());
// 在 ServiceTwo 服务中添加 ModuleTwo 模块
$serviceTwo->addModule(new \EasySwoole\Rpc\Tests\Service\ModuleTwo());

// 添加服务到服务管理器
$rpc->serviceManager()->addService($serviceOne);
$rpc->serviceManager()->addService($serviceTwo);


// 注册服务
$http = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();

$rpc->attachServer($http);
```

::: tip 用户可以自行实现 `Redis` 节点管理器实现类(实现 `NodeManagerInterface` 接口即可)，来完成 `rpc` 服务端的配置。下文将介绍使用默认节点管理器(即 `MemoryManager`)
完成 `rpc` 服务端的配置、`rpc` 服务的注册及服务调用。
:::


## 节点管理器

```php
<?php
/** 节点管理器 */

// 用户在调用rpc过程中 当发现节点不可用 可自行调用下线

// 构造方法内用户可传入节点管理器实现`NodeManagerInterface` 默认`MemoryManager`
$config = new \EasySwoole\Rpc\Config();
$rpc = new \EasySwoole\Rpc\Rpc($config);

$nodeManager = $rpc->getConfig()->getNodeManager();

// 获取服务的所有节点
$nodeManager->getNodes('serviceOne', 1);

// 随机获取服务的一个节点
$nodeManager->getNode('serviceOne', 1);

// 下线一个服务节点
$nodeManager->offline(new \EasySwoole\Rpc\Server\ServiceNode());

// 刷新一个服务节点
$nodeManager->alive(new \EasySwoole\Rpc\Server\ServiceNode());

// 宕机一个服务节点
$nodeManager->failDown(new \EasySwoole\Rpc\Server\ServiceNode());
```

