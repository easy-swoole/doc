---
title: easyswoole请求拦截之中间件实现原理
meta:
  - name: description
    content: easyswoole请求拦截之中间件实现原理
  - name: keywords
    content: easyswoole中间件|easyswoole请求拦截|easyswoole权限验证
---

# 请求拦截

`EasySwoole` 的控制器并没有提供类似中间件的说法，而是提供了控制器中的 ```onRequest``` 事件进行验证。

例如，我们需要对 ```/api/user/*``` 下的路径进行 `cookie` 验证。那么有以下两种方案：

## 全局 Request 及 Response 事件

在 [全局 Initialize 事件](/FrameDesign/event/initialize.md) 中注册.

```php
public static function initialize()
{
    date_default_timezone_set('Asia/Shanghai');

    // onRequest v3.4.x+
    \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
        $cookie = $request->getCookieParams('user_cookie');
        // 对 cookie 进行判断，比如在数据库或者是 redis 缓存中，存在该 cookie 信息，说明用户登录成功
        $isLogin = true;
        if ($isLogin) {
            // 返回 true 表示继续往下执行控制器 action
            return true;
        } else {
            // 这一步可以给前端响应数据，告知前端未登录
            $data = Array(
                "code" => 200,
                "result" => null,
                "msg" => '请先登录'
            );
            $response->withHeader('Content-Type', 'application/json;charset=utf-8');
            $response->withStatus(200);
            $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            // 返回 false 表示不继续往下执行控制器 action
            return false;
        }
    });

    // afterRequest v3.4.x+
    \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {

    });
}
```

`3.4.x` 版本之前：可在项目根目录的 `EasySwooleEvent.php` 中看到 `onRequest` 及 `afterRequest` 方法。

## 定义 Base 控制器

```php
<?php

namespace App\HttpController\Api\User;

use EasySwoole\Http\AbstractInterface\Controller;

abstract class Base extends Controller
{
    protected function onRequest(?string $action): ?bool
    {
        $cookie = $this->request()->getCookieParams('user_cookie');
        // 对 cookie 进行判断，比如在数据库或者是 redis 缓存中，存在该 cookie 信息，说明用户登录成功
        $isLogin = true;
        if ($isLogin) {
            // 返回 true 表示继续往下执行控制器 action
            return true;
        } else {
            // 这一步可以给前端响应数据，告知前端未登录
            $this->writeJson(401, null, '请先登录');
            // 返回 false 表示不继续往下执行控制器 action
            return false;
        }
    }
}
```

后续，只要 ```/api/user/*``` 路径下的控制器，都继承自 `Base` 控制器，都可以自动实现对 `cookie` 拦截了

> 行为权限校验也是如此，可以判断某个用户是否对该控制器的 `action` 或者请求路径有没有权限