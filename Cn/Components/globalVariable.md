---
title: easyswoole全局变量
meta:
  - name: description
    content: easyswoole全局变量
  - name: keywords
    content: easyswoole global variable|swoole global variable|easyswoole 全局变量|swoole 全局变量
---

# 全局变量

在swoole协程当中，我们都知道类似`$_GET`,`$_SESSION`,这样的全局变量是不能安全使用的。原因是协程切换下会带来数据污染问题。

## 组件要求

- php: >=7.1.0
- ext-json: *
- ext-libxml: *
- ext-simplexml: *
- ext-swoole: >=4.0
- easyswoole/component: ^2.1
- easyswoole/spl: ^1.0
- easyswoole/utility: ^1.1
- nikic/fast-route: ^1.3
- psr/http-message: ^1.0

## 安装方法

> composer require easyswoole/http

## 仓库地址

[easyswoole/http](https://github.com/easy-swoole/http)

## 基本使用

- 事件注册

修改全局event `(EasySwooleEvent.php)`，注册`mainServerCreate`和`onRequest`事件

```php
<?php

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Di;use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\GlobalParamHook;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\SessionFileHandler;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        
        // 3.4.x+ 需要在这里注册
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST,function (Request $request, Response $response){
            GlobalParamHook::getInstance()->onRequest($request, $response);
            return true;
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        GlobalParamHook::getInstance()->hookDefault();
        //如果不需要session请勿注册(easy_session为session名称, session_dir为存储的位置) 
        GlobalParamHook::getInstance()->hookSession($handler, 'easy_session', 'session_dir');
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // 3.3.x 在这里注册
        GlobalParamHook::getInstance()->onRequest($request, $response);
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}

```

- 使用

注册之后就可以在easyswoole控制器的任意位置使用了。

```php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        var_dump($_GET['a']);
        if(isset($_SESSION['b'])){
            var_dump('last session is '.$_SESSION['b']);
        }
        $_SESSION['b'] = time();
    }
}
```

::: tip
Easyswoole在spl包中，实现了一个SplContextArray,并在主进程的位置，替换了这些全局变量，使得，这些数据的访问是安全的，并在请求结束后自动清理。从而我们可以尽可能的去使用一些FPM包
:::

::: warning
该特性需要1.6版本以上的http组件库
:::

> "easyswoole/http": "^1.6"