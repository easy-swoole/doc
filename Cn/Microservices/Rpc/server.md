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

## 服务定义

每一个 `Rpc` 服务其实就是一个 `EasySwoole\Rpc\Service\AbstractService` 类，在服务下面我们又分为多个子模块，每个子模块提供不同的服务。 如下：

### 定义商品服务

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

#### 定义商品服务的子模块

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

### 定义公共服务

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

#### 定义公共服务的子模块

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
        // 获取client 全局参数
        $this->request()->getClientArg();
        // 获取参数
        $this->request()->getArg();
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
        $config = new \EasySwoole\Rpc\Config();
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

> 为了方便测试，我把两个服务放在同一台机器中注册。实际生产场景应该是 `N` 台机注册商品服务，`N` 台机器注册公告服务，把服务分开。

