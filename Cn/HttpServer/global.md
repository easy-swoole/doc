---
title: EasySwoole 全局变量
meta:
  - name: description
    content: swoole|swoole 全局变量|easyswoole 全局变量
  - name: keywords
    content: swoole|swoole 全局变量|easyswoole 全局变量
---

# 全局变量

在swoole协程当中，我们都知道类似```$_GET```,```$_SESSION```,这样的全局变量是不能安全使用的。原因是协程切换下会带来数据污染问题。

## 惊喜

Easyswoole在spl包中，实现了一个```SplContextArray```,并在主进程的位置，替换了这些全局变量，使得，这些数据的访问是安全的，并在请求结束后自动清理。从而我们可以尽可能的去使用一些FPM包

## 注册

```php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Swoole\EventRegister;
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
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        GlobalParamHook::getInstance()->hookDefault();
        //如果不需要session请勿注册
        GlobalParamHook::getInstance()->hookSession($handler,'easy_session','session_dir');
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        GlobalParamHook::getInstance()->onRequest($request,$response);
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
```

> 事件注册完毕后，即可使用```$_GET```,```$_SESSION```,```$_POST```,```$_COOKIE```


## 使用

```
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

## 注意

该特性需要1.6版本以上的http组件库
```
"easyswoole/http": "^1.6"
```
