---
title: EasySwoole session
meta:
  - name: description
    content: swoole|swoole session|easyswoole session
  - name: keywords
    content: swoole|swoole session|easyswoole session
---

# EasySwoole Session 组件

由于在 `Swoole` 协程下，`php` 自带的 `session` 函数是不能使用的。为此，`EasySwoole` 提供了独立的 `session` 组件，实现 `php` 的 `session` 功能。

::: tip
  `Session` 组件目前最新稳定版本为 `3.x`。针对 `2.x` 版本的组件使用文档请看 [Session 2.x](/HttpServer/session_2.x.md)，其他旧版本的组件使用文档请以 [Github](https://github.com/easy-swoole/session) 为准。
:::

## 组件要求
- php: >=7.1.0
- easyswoole/spl: ^1.3
- easyswoole/utility: ^1.1
- easyswoole/component: ^2.1

## 安装方法
::: tip
  从框架 `3.4.4` 版本开始，框架默认自带该组件，不用再次再装，其他版本请使用 composer 安装，安装方法如下所示。
:::

> composer require easyswoole/session=3.x

## 仓库地址
[easyswoole/session=3.x](https://github.com/easy-swoole/session)

## 基本使用

### 注册 session handler
使用 `session` 前，需要先注册 `session handler`。接下来的示例使用的 `session handler` 是 `EasySwoole` 内置的 `session handler`，开箱即用。

注册步骤如下：
1. 首先我们定义一个 `session` 工具类继承自 `session` 组件的 `\EasySwoole\EasySwoole\Session` 类。用户可以自行定义类继承并实现。下面为提供的一个参考工具类。

新增 `App\Tools\Session.php`，内容如下：

```php
<?php

namespace App\Tools;

use EasySwoole\Component\Singleton;

class Session extends \EasySwoole\Session\Session
{
    use Singleton;
}
```

2. 注册 `session handler`。修改 `EasySwoole` 全局 `event` 文件(即框架根目录的 `EasySwooleEvent.php` 文件)，在 `mainServerCreate` 全局事件和 `HTTP` 的 全局 `HTTP_GLOBAL_ON_REQUEST` 事件中注册 `session handler`。

具体实现代码如下:

```php
<?php

namespace EasySwoole\EasySwoole;

use App\Tools\Session;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\FileSession;
use EasySwoole\Utility\Random;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        // 可以自己实现一个标准的 session handler，下面使用组件内置实现的 session handler
        // 基于文件存储，传入 EASYSWOOLE_TEMP_DIR . '/Session' 目录作为 session 数据文件存储位置
        Session::getInstance(new FileSession(EASYSWOOLE_TEMP_DIR . '/Session'));

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response) {
            // TODO: 注册 HTTP_GLOBAL_ON_REQUEST 回调，相当于原来的 onRequest 事件

            // 获取客户端 Cookie 中 easy_session 参数
            $sessionId = $request->getCookieParams('easy_session');
            if (!$sessionId) {
                $sessionId = Random::character(32); // 生成 sessionId
                // 设置向客户端响应 Cookie 中 easy_session 参数
                $response->setCookie('easy_session', $sessionId);
            }

            // 存储 sessionId 方便调用，也可以通过其它方式存储
            $request->withAttribute('easy_session', $sessionId);

            Session::getInstance()->create($sessionId); // 创建并返回该 sessionId 的 context
        });

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response) {
            // TODO: 注册 HTTP_GLOBAL_AFTER_REQUEST 回调，相当于原来的 afterRequest 事件

            // session 数据落地【必不可少这一步】
            Session::getInstance()->close($request->getAttribute('easy_session'));

            // gc 会清除所有 session，切勿操作
            // Session::getInstance()->gc(time());
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

### 在 `EasySwoole` 中使用 `session`
注册 `session handler` 之后，我们就可以在 `EasySwoole 控制器` 的任意位置使用了。

简单使用示例代码如下:

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Session\Context;

class Session extends Controller
{
    protected function session(): ?Context
    {
        // 封装一个方法，方便我们快速获取 session context
        $sessionId = $this->request()->getAttribute('easy_session');
        return \App\Tools\Session::getInstance()->create($sessionId);
    }

    // 将值保存在 session 中
    public function set()
    {
        // $this->session()->set('key', 'value');
        // 把 'test_session_key' 作为键，time() 的值作为值，保存在 session 中
        $this->session()->set('test_session_key', time());

        // 响应客户端
        $this->writeJson(200, 'success!');
    }

    // 获取 session 中的值
    public function get()
    {
        // $this->session()->get('key');
        // 从 session 中获取 key 为 'test_session_key' 的值
        $ret = $this->session()->get('test_session_key');

        // 响应客户端
        $this->writeJson(200, $ret);
    }

    // 获取 session 中所有数据
    public function all()
    {
        // 获取 session 中所有数据
        $ret = $this->session()->allContext();

        // 响应客户端
        $this->writeJson(200, $ret);
    }

    // 删除 session 中的值
    public function del()
    {
        // $this->session()->del('key');
        // 删除 session 中 key 为 'test_session_key' 的值
        $this->session()->del('test_session_key');

        // 再次获取 session 中所有数据并响应客户端
        $this->writeJson(200, $this->session()->allContext());
    }

    // 清空 session 中所有数据
    public function flush()
    {
        // 清空 session 中所有数据
        $this->session()->flush();

        // 再次获取 session 中所有数据并响应客户端
        $this->writeJson(200, $this->session()->allContext());
    }

    // 重新设置(覆盖) session 中的数据
    public function setData()
    {
        // 重新设置(覆盖) session 中的数据
        $ret = $this->session()->setData([
            'test_session_key' => 1,
            'test_session_key1' => 2
        ]);

        // 再次获取 session 中所有数据并响应给客户端
        $this->writeJson(200, $ret->allContext());
    }
}
```
然后访问 `http://127.0.0.1:9501/session/set` (示例请求地址)就可以进行测试设置 `session`，访问 `http://127.0.0.1:9501/session/flush` (示例请求地址)就可以清空所有 `session` 数据。其他示例请用户自行测试。