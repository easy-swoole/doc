---
title: easyswoole下session的实现
meta:
  - name: description
    content: easyswoole下session的实现
  - name: keywords
    content: easyswoole session|swoole session
---

# Session

在swoole协程下，php自带的session函数是不能使用的。为此，easyswoole提供了session的实现

## 组件要求

- php: >=7.1.0
- easyswoole/component: ^2.1
- easyswoole/spl: ^1.3
- easyswoole/utility: ^1.1

## 安装方法

> composer require easyswoole/session

## 仓库地址

[easyswoole/session](https://github.com/easy-swoole/session)

## 基本使用

- 事件注册

修改全局event `(EasySwooleEvent.php)`，注册`mainServerCreate`和`onRequest`事件

```php
namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\Session;
use EasySwoole\Session\SessionFileHandler;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        //可以自己实现一个标准的session handler
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        //表示cookie name   还有save path
        Session::getInstance($handler,'easy_session','session_dir');
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        $cookie = $request->getCookieParams('easy_session');
        if(empty($cookie)) {
            $sid = Session::getInstance()->sessionId();
            $response->setCookie('easy_session',$sid);
        } else {
            Session::getInstance()->sessionId($cookie);
        }
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }

}

```

- 调用

注册之后就可以在easyswoole控制器的任意位置使用了。

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Session\Session;

class Index extends Controller
{
    function index()
    {
        if(Session::getInstance()->get('a')) {
            var_dump(Session::getInstance()->get('a'));
        } else {
            Session::getInstance()->set('a',time());
        }
    }

    function des()
    {
        Session::getInstance()->destroy();;
    }
}
```

:::tip
自带的文件session实现是无锁的
:::