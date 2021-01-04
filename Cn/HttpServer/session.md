---
title: EasySwoole session
meta:
  - name: description
    content: swoole|swoole session|easyswoole session
  - name: keywords
    content: swoole|swoole session|easyswoole session
---

# EasySwoole Session组件

由于在 `Swoole` 协程下，`php` 自带的 `session` 函数是不能使用的。为此，`EasySwoole` 提供了独立的 `session` 组件，实现 `php` 的 `session` 功能。

## 组件要求
- php ： >=7.1.0"
- easyswoole/spl : ^1.3
- easyswoole/utility : ^1.1
- easyswoole/component : ^2.1

## 安装方法
> composer require easyswoole/session=2.x

## 仓库地址
[easyswoole/session=2.x](https://github.com/easy-swoole/session)

## 基本使用

### 注册 session handler
使用 `session` 前，需要先注册 `session handler`。接下来的示例使用的 `session handler` 是 `EasySwoole` 内置的 `session handler`，开箱即用。

注册步骤：

修改 `EasySwoole` 全局的 `event`文件(框架根目录的 `EasySwooleEvent.php` 文件)，在 ```mainServerCreate``` 和 `HTTP` 的 全局 ```HTTP_GLOBAL_ON_REQUEST``` 事件中注册 `session handler`。

具体实现代码如下:
```
<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\Session;
use EasySwoole\Session\SessionFileHandler;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response): bool {
            // TODO: 注册 HTTP_GLOBAL_ON_REQUEST 回调，相当于原来的 onRequest 事件
            $cookie = $request->getCookieParams('easy_session');
            if (empty($cookie)) {
                $sid = Session::getInstance()->sessionId();
                $response->setCookie('easy_session', $sid);
            } else {
                Session::getInstance()->sessionId($cookie);
            }
            return true;
        });

        Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response): void {
            // TODO: 注册 HTTP_GLOBAL_AFTER_REQUEST 回调，相当于原来的 afterRequest 事件
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 可以自己实现一个标准的session handler
        $handler = new SessionFileHandler(EASYSWOOLE_TEMP_DIR);
        // 表示c ookie name   还有 save path
        Session::getInstance($handler, 'easy_session', 'session_dir');
    }
}
```

### 在 `EasySwoole` 中使用 `session`
注册 `session handler` 之后，我们就可以在 `EasySwoole 控制器` 的任意位置使用了。

简单使用示例代码如下:
```
<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Session\Session;

class Index extends Controller
{

    public function index()
    {
        if (Session::getInstance()->get('a')) {
            var_dump(Session::getInstance()->get('a'));
        } else {
            Session::getInstance()->set('a', time());
        }
    }

    function des()
    {
        Session::getInstance()->destroy();
    }
}
```
然后访问 `http://127.0.0.1:9501/index` (示例请求地址)就可以进行测试设置 `session`，访问 `http://127.0.0.1:9501/des` (示例请求地址)就可以销毁 `session`