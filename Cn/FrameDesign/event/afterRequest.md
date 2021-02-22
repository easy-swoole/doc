---
title: EasySwoole框架设计原理 - afterRequest事件解析
meta:
  - name: keywords
    content: easyswoole afterRequest事件|easyswoole afterRequest
---

# afterRequest 事件(即请求方法结束后执行事件)

## 使用场景及原理
该事件是在请求方法结束后执行。可以在该事件中做 `trace`，对请求进行追踪监视以及获取此次的响应内容。

## 使用方式说明
框架对 `afterRequest` 事件的实现在 `3.4.x 及以后的版本` 中做了新的改动，实现方式由原来旧版本在主服务创建事件(`mainServerCreate 事件`)中定义改变为在 [initialize 事件](/FrameDesign/event/initialize.md) 中使用 `Di` 方式注入。目前最新稳定版本框架(`3.4.x`)，具体实现及使用方式 (在 `EasySwooleEvent.php` 中的 `initialize` 事件中注入) 如下：
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

        // 实现 afterRequest 事件
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response): void {
            
            // 示例：获取此次请求响应的内容
            TrackerManager::getInstance()->getTracker()->endPoint('request');
            $responseMsg = $response->getBody()->__toString();
            Logger::getInstance()->console('响应内容:' . $responseMsg);
            // 响应状态码：
            // var_dump($response->getStatusCode());

            // tracker 结束，结束之后，能看到中途设置的参数，调用栈的运行情况
            TrackerManager::getInstance()->closeTracker();
        });
        
    }

    public static function mainServerCreate(EventRegister $register)
    {
        
    }
}
```

::: tip
旧版本(`3.4.x` 之前版本)框架的 `afterRequest` 事件的实现如下所示：
::: 

```php
<?php
namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    // 注册 afterRequest 事件回调
    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterRequest() method.
        
        // 示例：获取此次请求响应的内容
        TrackerManager::getInstance()->getTracker()->endPoint('request');
        $responseMsg = $response->getBody()->__toString();
        Logger::getInstance()->console('响应内容:' . $responseMsg);
        // 响应状态码：
        // var_dump($response->getStatusCode());
        
        // tracker 结束，结束之后，能看到中途设置的参数，调用栈的运行情况
        TrackerManager::getInstance()->closeTracker();
    }
}
```