---
title: EasySwoole框架设计原理 - onRequest事件解析
meta:
  - name: keywords
    content: easyswoole onRequest事件|easyswoole onRequest
---

# onRequest 事件(即收到请求事件)

## 使用场景及原理
当 `EasySwoole` 收到任何的 `HTTP` 请求时，均会执行该事件。可以使用该事件可以对 `HTTP` 请求全局拦截，包括对请求进行允许跨域等操作。

## 使用方式说明
框架对 `onRequest` 事件的实现在 `3.4.x 及以后的版本` 中做了新的改动，实现方式由原来旧版本在主服务创建事件(`mainServerCreate 事件`)中定义改变为在 [initialize 事件](/FrameDesign/event/initialize.md) 中使用 `Di` 方式注入。目前最新稳定版本框架(`3.4.x`)，具体实现及使用方式 (在 `EasySwooleEvent.php` 中的 `initialize` 事件中注入) 如下：
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

        // 实现 onRequest 事件
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response): bool {
            ###### 对请求进行拦截 ######
            // 不建议在这拦截请求，可增加一个控制器基类进行拦截
            // 如果真要拦截，判断之后 return false; 即可
            /*
            $code = $request->getRequestParam('code');
            if (0){ // empty($code)验证失败
                $data = array(
                    "code" => \EasySwoole\Http\Message\Status::CODE_BAD_REQUEST,
                    "result" => [],
                    "msg" => '验证失败'
                );
                $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $response->withHeader('Content-type', 'application/json;charset=utf-8');
                $response->withStatus(\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST);
                return false;
            }
            return true;
            */


            ###### 处理请求的跨域问题 ######
            $response->withHeader('Access-Control-Allow-Origin', '*');
            $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->withHeader('Access-Control-Allow-Credentials', 'true');
            $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            if ($request->getMethod() === 'OPTIONS') {
                $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
                return false;
            }
            return true;
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        
    }
}
```

::: tip
旧版本(`3.4.x` 之前版本)框架的 `onRequest` 事件的实现如下所示：
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
        
    }

    // 注册 onRequest 事件回调
    public static function onRequest(Request $request, Response $response): bool
    {
        ###### 对请求进行拦截 ######
        // 不建议在这拦截请求，可增加一个控制器基类进行拦截
        // 如果真要拦截，判断之后 return false; 即可
        /*
        $code = $request->getRequestParam('code');
        if (0){ // empty($code)验证失败
            $data = array(
                "code" => \EasySwoole\Http\Message\Status::CODE_BAD_REQUEST,
                "result" => [],
                "msg" => '验证失败'
            );
            $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $response->withHeader('Content-type', 'application/json;charset=utf-8');
            $response->withStatus(\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST);
            return false;
        }
        return true;
        */


        ###### 处理请求的跨域问题 ######
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
            return false;
        }
        return true;
    }
}
```

## 注意事项
::: warning
若在该事件中，执行 `$response->end()`，则该次请求不会进入路由匹配阶段。
:::