---
title: easyswoole 服务限流-atomic
meta:
  - name: description
    content: Easyswoole 服务限流-atomic
  - name: keywords
    content: easyswoole限流器|swoole限流器|easyswoole分布式|easyswoole微服务
---

# AtomicLimit

`EasySwoole` 提供了一个基于 [Atomic](http://swoole.easyswoole.com/ProcessCommunication/atomic.html) 计数器的限流器。

## 原理

通过限制某一个时间周期内的总请求数，从而实现基础限流。举个例子，设置5秒内，允许的最大请求量为200，那么理论平均并发为40，峰值并发为200。

## 组件要求

- php: >= 7.1.0
- easyswoole/component: ^2.0


## 安装方法

> composer require easyswoole/atomic-limit

## 仓库地址

[easy-swoole/atomic-limit](https://github.com/easy-swoole/atomic-limit)

## 在 EasySwoole 中使用

首先在 `EasySwoole` 全局的 `mainServerCreate` 事件（即项目根目录的 `EasySwooleEvent.php` 的 `mainServerCreate` 函数） 中，进行限流器注册

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\AtomicLimit\AtomicLimit;
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
        ###### 配置限流器 ######
        $limit = new AtomicLimit();
        /** 为方便测试，（全局的）限制设置为 10 */
        $limit->setLimitQps(10);
        $limit->attachServer(ServerManager::getInstance()->getSwooleServer());
        Di::getInstance()->set('auto_limiter', $limit);
    }
}
```

在 `App\HttpController\Index.php` 中调用限流器：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\HttpController;

use EasySwoole\AtomicLimit\AtomicLimit;
use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    /** @var AtomicLimit $autoLimiter */
    private $autoLimiter;

    protected function onRequest(?string $action): ?bool
    {
        $this->autoLimiter = Di::getInstance()->get('auto_limiter');

        if ($action == 'test1') {
            # 调用限流器对 http://127.0.0.1:9501/test1 请求限制流量
            if ($this->autoLimiter->access($action, 1)) {
                return true;
            } else {
                $this->writeJson(200, null, 'test1 refuse!');
                return false;
            }
        } else if ($action == 'test2') {
            # 调用限流器对 http://127.0.0.1:9501/test2 请求限制流量
            if ($this->autoLimiter->access($action, 2)) {
                return true;
            } else {
                $this->writeJson(200, null, 'test2 refuse!');
                return false;
            }
        }

        return parent::onRequest($action);
    }

    public function test1()
    {
        $this->writeJson(200, null, 'test1 success!');
    }

    public function test2()
    {
        $this->writeJson(200, null, 'test2 success!');
    }
}
```

::: warning 
 以上代码表示，`index/test1` 这个限流器在每秒内允许的最大流量为 `1`，而 `index/test2` 这个限流器的最大流量为 `2`。
:::

我们也可以在 `EasySwoole` 的 `Base` 控制器的 `onRequest` 方法中，进行请求拦截。例如在全局 `onRequest` 事件中，先进行流量检验，如果校验通过，则进行下一步操作。

## 在 Swoole 中使用

以经典的暴力 `CC` 攻击防护为例子。我们可以限制一个 `ip-url` 的 `qps` 访问。

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

// example url: http://127.0.0.1:9501/index.html?api=1

require_once __DIR__ . '/vendor/autoload.php';

use EasySwoole\AtomicLimit\AtomicLimit;

$http = new swoole_http_server("127.0.0.1", 9501);

###### 配置限流器 ######
$limit = new AtomicLimit();
/** 为方便测试，（全局的）限制设置为3 */
$limit->setLimitQps(3);
$limit->attachServer($http);

$http->on("request", function ($request, $response) use ($http, $limit) {
    $ip = $http->getClientInfo($request->fd)['remote_ip'];
    $requestUri = $request->server['request_uri'];
    $token = $ip . $requestUri;
    /** access 函数允许单独对某个 token 指定qps */
    if ($limit->access($token)) {
        $response->write('request accept');
    } else {
        $response->write('request refuse');
    }
    $response->end();
});

$http->start();
```

::: warning 
 注意，本例子是用一个自定义进程内加定时器来实现计数定时重置，实际上用一个进程来做这件事情有点不值得，因此实际生产可以指定一个 `worker`，设置定时器来实现。
:::