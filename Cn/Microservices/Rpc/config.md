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

### 服务端配置

```php
<?php
// 构造方法内用户可传入 节点管理器实现类(实现 `NodeManagerInterface` 接口的类) 默认为 `MemoryManager`
$config = new \EasySwoole\Rpc\Config();


/** 
 * 服务端配置 
 */
// 设置服务名称
$config->setServerName('User'); // 默认 EasySwoole

// 设置节点id
$config->setNodeId(\EasySwoole\Utility\Random::character(10)); // 可忽略 构造函数已经设置

// 【必须设置】设置异常处理器，对 Service-Worker 和 AssistWorker 的异常进行处理，防止未捕获导致进程退出
$config->setOnException(function (\Throwable $throwable) {

});

$serverConfig = $config->getServer();

// 【必须设置】设置本机ip
$serverConfig->setServerIp('127.0.0.1');

// 设置工作进程数量
$serverConfig->setWorkerNum(4);

// 设置监听地址及端口
$serverConfig->setListenAddress('0.0.0.0');
$serverConfig->setListenPort('9600');

// 设置服务端最大接受包大小
$serverConfig->setMaxPackageSize(1024 * 1024 * 2);

// 设置接收客户端数据时间
$serverConfig->setNetworkReadTimeout(3);


/**
 * 广播设置
 */
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
$serviceFinderConfig->setBroadcastAddress(['127.0.0.1:9600', '127.0.0.1:9601']);
$serviceFinderConfig->setBroadcastInterval(5000); // 5s 广播一次

// 设置广播秘钥
$serviceFinderConfig->setEncryptKey('EasySwoole');
```

广播配置需要在配置服务端时，进行配置。


### 客户端配置
```php
<?php
// 构造方法内用户可传入 节点管理器实现类(实现 `NodeManagerInterface` 接口的类) 默认为 `MemoryManager`
$config = new \EasySwoole\Rpc\Config();

/** 
 * 客户端设置
 * 
 * 如果只是暴露rpc服务 不进行调用别的rpc服务 可不用设置
 */
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
$serviceOne = new \App\RpcServices\ServiceOne();
// 在 ServiceOne 服务中添加 ModuleOne 模块
$serviceOne->addModule(new \App\RpcServices\ModuleOne());
// 在 ServiceOne 服务中添加 ModuleTwo 模块
$serviceOne->addModule(new \App\RpcServices\ModuleTwo());

// 创建 ServiceTwo 服务 
$serviceTwo = new \App\RpcServices\ServiceTwo();
// 在 ServiceTwo 服务中添加 ModuleOne 模块
$serviceTwo->addModule(new \App\RpcServices\ModuleOne());
// 在 ServiceTwo 服务中添加 ModuleTwo 模块
$serviceTwo->addModule(new \App\RpcServices\ModuleTwo());

// 添加服务到服务管理器
$rpc->serviceManager()->addService($serviceOne);
$rpc->serviceManager()->addService($serviceTwo);


// 注册服务
# $http = new \Swoole\Http\Server('0.0.0.0', 9501);
$http = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();

$rpc->attachServer($http);
```

::: tip
  用户可以自行实现 `Redis` 节点管理器实现类(实现 `NodeManagerInterface` 接口即可)，来完成 `rpc` 服务端的配置。下文将介绍使用默认节点管理器(即 `MemoryManager`)完成 `rpc` 服务端的配置、`rpc` 服务的注册及服务调用。
:::


## 调用

### 客户端调用

```php
<?php
$rpc = new \EasySwoole\Rpc\Rpc($config);

$client = $rpc->client();

// 添加请求(调用 ServiceOne 服务的 ModuleOne 模块的 action 方法) 
$ctx1 = $client->addRequest('ServiceOne.ModuleOne.action');
// 设置请求参数
$ctx1->setArg(['a','b','c']);
$ctx1->setOnSuccess(function (\EasySwoole\Rpc\Protocol\Response $response){
    // 调用成功获得响应
});
$ctx1->setOnFail(function (\EasySwoole\Rpc\Protocol\Response $response){
    // 调用失败获得响应
});


// 添加请求(调用 ServiceTwo 服务的 ModuleOne 模块的 action 方法) 
$ctx2 = $client->addRequest('ServiceTwo.ModuleOne.action');
// 设置请求参数
$ctx2->setArg(['a','b','c']);
$ctx2->setOnSuccess(function (\EasySwoole\Rpc\Protocol\Response $response){
    // 调用成功获得响应
});
$ctx2->setOnFail(function (\EasySwoole\Rpc\Protocol\Response $response){
    // 调用失败获得响应
});
```

::: tip
  注意，当使用默认实现的节点管理器(即 `MemoryManager`)进行注册服务端时，在进行客户端调用时，请使用 `\EasySwoole\Components\Di` 组件保存 `rpc` 服务端注册服务完成后的实例或在 `Swoole` 中独立使用采用 `use` 引入 `rpc` 服务端注册服务完成后的实例，具体请看下文的使用示例。
:::


## 节点管理器使用介绍
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

## 基础使用

新建 `App\RpcServices\` 文件夹，然后定义如下 `服务类` 和 `模块类`：

1. 定义 ServiceOne 服务

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractService;

class ServiceOne extends AbstractService
{
    function serviceName(): string
    {
        return 'ServiceOne';
    }
}
```

2. 定义 ServiceTwo 服务

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractService;

class ServiceTwo extends AbstractService
{
    function serviceName(): string
    {
        return 'ServiceTwo';
    }
}
```

3. 定义 ModuleOne 模块

```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class ModuleOne extends AbstractServiceModule
{
    function moduleName(): string
    {
        return 'ModuleOne';
    }

    function args()
    {
        $this->response()->setResult($this->request()->getArg());
    }

    function action()
    {
        $this->response()->setMsg("ModuleOne hello action");
    }

    function exception()
    {
        throw new \Exception('the ModuleOne exception');

    }

    protected function onException(\Throwable $throwable)
    {
        $this->response()->setStatus(-1)->setMsg($throwable->getMessage());
    }
}
```

4. 定义 ModuleTwo 模块
   
```php
<?php

namespace App\RpcServices;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class ModuleTwo extends AbstractServiceModule
{
    function moduleName(): string
    {
        return 'ModuleTwo';
    }

    function args()
    {
        $this->response()->setResult($this->request()->getArg());
    }

    function action()
    {
        $this->response()->setMsg("ModuleTwo hello action");
    }

    function exception()
    {
        throw new \Exception('the ModuleTwo exception');

    }

    protected function onException(\Throwable $throwable)
    {
        $this->response()->setStatus(-1)->setMsg($throwable->getMessage());
    }
}
```

### 在 Swoole 中独立使用

```php
<?php

use EasySwoole\Rpc\Config;
use EasySwoole\Rpc\Protocol\Response;
use EasySwoole\Rpc\Rpc;
use Swoole\Http\Server;
use App\RpcServices\ServiceOne;
use App\RpcServices\ModuleOne;

require 'vendor/autoload.php';

// 配置 rpc 服务端
$config = new Config();

// 【必须设置】设置本机ip
$config->getServer()->setServerIp('127.0.0.1');

$rpc = new Rpc($config);

// 创建 ServiceOne 服务
$service = new ServiceOne();
// 添加 ModuleOne 模块到 ServiceOne 服务中
$service->addModule(new ModuleOne());

// 添加 ServiceOne 服务到服务管理器中
$rpc->serviceManager()->addService($service);

$http = new Server('0.0.0.0', 9501);

// 注册 rpc 服务
$rpc->attachServer($http);

// 使用 use 引入 rpc 服务端注册服务完成后的实例
$http->on('request', function ($request, $response) use ($rpc) {

    // 客户端调用
    $client = $rpc->client();

    // 添加请求(调用 ServiceOne 服务的 ModuleOne 模块的 action 方法)
    $ctx2 = $client->addRequest('ServiceOne.ModuleOne.action');

    // 设置请求参数
    $ctx2->setArg('xxx');

    $rpcCallRet = '';

    // 设置调用成功执行回调
    $ctx2->setOnSuccess(function (Response $response) use (&$rpcCallRet) {
        // 调用成功获得响应
        var_dump($response->getMsg());
        $rpcCallRet = $response->getMsg();
    });

    // 设置调用失败执行回调
    $ctx2->setOnFail(function (Response $response) use (&$rpcCallRet) {
        // 调用失败获得响应
        var_dump($response->getMsg());
        $rpcCallRet = $response->getMsg();
    });

    // 执行调用
    $client->exec();

    $response->end('the result of rpc is ' . $rpcCallRet);
});

$http->start();
```

访问 `http://localhost:9501/`(示例请求地址) 即可看到结果为 `the result of rpc is ModuleOne hello action`。

### 在 EasySwoole 中使用

1. 在 `mainServerCreate` 事件中注册 `rpc` 服务

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

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
        // 构造方法内用户可传入节点管理器实现`NodeManagerInterface` 默认`MemoryManager`
        $config = new \EasySwoole\Rpc\Config();
        // 设置服务名称
        $config->setServerName('EasySwoole'); // 默认 EasySwoole

        // 【可选操作】设置节点id，可忽略，构造函数已经设置
        # $config->setNodeId(\EasySwoole\Utility\Random::character(10));

        // 设置异常处理器 对Service-Worker 和 AssistWorker的异常进行处理 必须设置 防止未捕获导致进程退出
        $config->setOnException(function (\Throwable $throwable) {

        });
        $serverConfig = $config->getServer();
        // 设置本机ip 必须设置
        $serverConfig->setServerIp('127.0.0.1');

        // 【可选操作】设置工作进程数量，默认为 4
        # $serverConfig->setWorkerNum(4);
        // 【可选操作】设置监听地址及端口，监听地址默认为 '0.0.0.0'，端口默认为 9600
        # $serverConfig->setListenAddress('0.0.0.0');
        # $serverConfig->setListenPort('9600');
        // 【可选操作】设置服务端最大接受包大小，默认为 1024 * 1024 * 2 (即2M)
        # $serverConfig->setMaxPackageSize(1024 * 1024 * 2);
        // 【可选操作】设置接收客户端数据时间，默认为 3s
        # $serverConfig->setNetworkReadTimeout(3);


        /** 配置 rpc */
        $rpc = new \EasySwoole\Rpc\Rpc($config);

        // 创建 ServiceOne 服务
        $service = new \App\RpcServices\ServiceOne();
        // 添加 ModuleOne 模块到 ServiceOne 服务中
        $service->addModule(new \App\RpcServices\ModuleOne());
        // 添加 ServiceOne 服务到服务管理器中
        $rpc->serviceManager()->addService($service);

        // 注册 rpc 服务
        $rpc->attachServer(ServerManager::getInstance()->getSwooleServer());

        // 使用 \EasySwoole\Components\Di 组件保存 rpc 服务端注册服务完成后的实例，方便在框架的其他任何地方进行调用。
        Di::getInstance()->set('rpc', $rpc);
    }
}
```



2. 在控制器中进行服务调用

```php
<?php

namespace App\HttpController;

use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Rpc\Protocol\Response;
use EasySwoole\Rpc\Rpc;

class Index extends Controller
{
    /** @var Rpc */
    protected $rpcService;

    protected function onRequest(?string $action): ?bool
    {
        $this->rpcService = Di::getInstance()->get('rpc');
        return parent::onRequest($action);
    }

    public function index()
    {
        // 客户端调用
        $client = $this->rpcService->client();

        // 添加请求(调用 ServiceOne 服务的 ModuleOne 模块的 action 方法)
        $ctx2 = $client->addRequest('ServiceOne.ModuleOne.action');

        // 设置请求参数
        $ctx2->setArg('xxx');

        $rpcCallRet = '';

        // 设置调用成功执行回调
        $ctx2->setOnSuccess(function (Response $response) use (&$rpcCallRet) {
            // 调用成功获得响应
            var_dump($response->getMsg());
            $rpcCallRet = $response->getMsg();
        });

        // 设置调用失败执行回调
        $ctx2->setOnFail(function (Response $response) use (&$rpcCallRet) {
            // 调用失败获得响应
            var_dump($response->getMsg());
            $rpcCallRet = $response->getMsg();
        });

        // 执行调用
        $client->exec();

        $this->response()->write('the result of rpc is ' . $rpcCallRet);
    }
}
```

访问 `http://localhost:9501/`(示例请求地址) 即可看到结果为 `the result of rpc is ModuleOne hello action`。