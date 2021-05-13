---
title: EasySwoole 全局变量
meta:
  - name: description
    content: swoole|swoole 全局变量|easyswoole 全局变量
  - name: keywords
    content: swoole|swoole 全局变量|easyswoole 全局变量
---

# 全局变量

在 `swoole` 协程当中，我们都知道类似 ```$_GET```、```$_SESSION``` 这样的全局变量是不能安全使用的。原因是协程切换下会带来数据污染问题。

## 惊喜

`EasySwoole` 在 `spl` 包中，实现了一个 ```SplContextArray```，并在主进程的位置，替换了这些全局变量，使得这些数据的访问是安全的，并在请求结束后自动清理。从而我们可以在使用一些 `FPM` 环境下的组件包时没有影响。

::: tip
  注意：该特性下面的注册和使用示例需要你的框架 `easyswoole/http` 组件版本大于等于 2.0.0。如果用户`easyswoole/http` 组件版本在 1.6 ~ 1.7.19 之间请查看 [全局变量旧版本用法](/HttpServer/global_1.x.md)
:::

## 注册

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\GlobalParam\Hook;
use EasySwoole\Session\FileSession;
use EasySwoole\Session\Session;


class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        // 配置 session，设置 session 数据文件存储目录为 EASYSWOOLE_TEMP_DIR
        # $sesionHandler = new FileSession(EASYSWOOLE_TEMP_DIR . '/Session');

        $globalParamHook = new Hook();

        // 如果不需要使用 session 请勿注册
        // 使用时请先参考 session 章节 https://www.easyswoole.com/HttpServer/session.html，新增 \App\Tools\Session 类文件。
        # \App\Tools\Session::getInstance($sesionHandler);
        # $globalParamHook->enableSession(Session::getInstance());

        $globalParamHook->register();

        // onRequest v3.4.x+
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) use ($globalParamHook) {
            // 替换全局变量
            $globalParamHook->onRequest($request, $response);
        });

        // afterRequest v3.4.x+
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {

        });
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

> 事件注册完毕后，即可使用 ```$_GET```、```$_COOKIE```、```$_POST```、```$_FILES```、```$_SERVER```、```$_SESSION```。


## 使用

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        var_dump($_GET['a']);
        var_dump($_SERVER);
    }
}
```

## 注意

该特性需要 `2.0.0` 版本以上的 `http` 组件库

```
"easyswoole/http": "^2.0.0"
```
