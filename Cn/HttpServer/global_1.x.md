---
title: EasySwoole 全局变量
meta:
  - name: description
    content: swoole|swoole 全局变量|easyswoole 全局变量
  - name: keywords
    content: swoole|swoole 全局变量|easyswoole 全局变量
---

# 全局变量 (针对 easyswoole/http 在 1.6 ~ 2.0)

在 `swoole` 协程当中，我们都知道类似 ```$_GET```、```$_SESSION``` 这样的全局变量是不能安全使用的。原因是协程切换下会带来数据污染问题。

## 惊喜

`EasySwoole` 在 `spl` 包中，实现了一个 ```SplContextArray```，并在主进程的位置，替换了这些全局变量，使得这些数据的访问是安全的，并在请求结束后自动清理。从而我们可以在使用一些 `FPM` 环境下的组件包时没有影响。

## 注册

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\GlobalParamHook;
use EasySwoole\Session\SessionFileHandler;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        
        // 配置 session，设置 session 数据文件存储目录为 EASYSWOOLE_TEMP_DIR
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        
        GlobalParamHook::getInstance()->hookDefault();
        
        // 如果不需要 session 请勿注册
        GlobalParamHook::getInstance()->hookSession($handler, 'easy_session', 'session_dir');

        // onRequest v3.4.x+
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
            // 替换全局变量
            GlobalParamHook::getInstance()->onRequest($request, $response);
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

`EasySwoole 3.4.x` 版本之前：可在项目根目录的 `EasySwooleEvent.php` 中看到 `onRequest` 及 `afterRequest` 方法。

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
        if (isset($_SESSION['b'])) {
            var_dump('last session is ' . $_SESSION['b']);
        }
        $_SESSION['b'] = time();
    }
}
```

## 注意

该特性需要 1.6 版本以上的 http 组件库
```
"easyswoole/http": "^1.6"
```
