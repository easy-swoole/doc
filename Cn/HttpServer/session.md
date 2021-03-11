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
  `Session` 组件目前最新稳定版本为 `3.x`。针对 `2.x` 版本的组件使用文档请看[Session 2.x](/HttpServer/session_2.x.md)，其他旧版本的组件使用文档请以 [Github](https://github.com/easy-swoole/session) 为准。
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

```php
<?php
/**
 * User: XueSi <1592328848@qq.com>
 * Date: 2021-03-10
 * Time: 20:26
 */
namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Session\Session;
use EasySwoole\Session\SessionHandlerInterface;
use EasySwoole\Spl\SplContextArray;
use EasySwoole\Utility\Random;
use Swoole\Coroutine;

class SessionTools extends Session
{
    use Singleton;

    /** @var string $name session 名称前缀 */
    private $name;

    /** @var SessionHandlerInterface session handler */
    protected $handler;

    /** @var \EasySwoole\Session\Context|null 存放 session 数据 */
    private $sessionDataContext;

    /** @var SplContextArray $sessionConfigContext 存放 session 配置 */
    private $sessionConfigContext;

    protected $timeout = 3.0;

    private $callTimes = 0;
    private $gc_cycle_times = 50000;
    private $autoClear = true;
    // 默认活跃时间为一星期
    private $maxLifeTime = 3600 * 24 * 7;

    public function __construct(SessionHandlerInterface $handler, float $timeout, $sessionName = 'easy_session')
    {
        if ($timeout) {
            $this->timeout = $timeout;
        }
        parent::__construct($handler, $this->timeout);
        $this->name = $sessionName;
        $this->handler = $handler;
        $this->sessionConfigContext = new SplContextArray(false);
    }

    function sessionId(string $sid = null): string
    {
        if ($sid) {
            if (!$this->sessionConfigContext['isStart']) {
                $this->sessionConfigContext['sid'] = $sid;
                $this->sessionDataContext = $this->create($this->sessionConfigContext['sid'], $this->timeout);
            } else {
                throw new \Exception('can not modify sid after session start');
            }
        } else {
            if (empty($this->sessionConfigContext['sid'])) {
                $this->sessionConfigContext['sid'] = $this->name . strtolower(Random::character(32));
                $this->sessionDataContext = $this->create($this->sessionConfigContext['sid'], $this->timeout);
            }
        }
        return $this->sessionConfigContext['sid'];
    }

    function start()
    {
        if (!$this->sessionConfigContext['isStart']) {

            // gc准确计数
            $this->callTimes++;
            if ($this->gc_cycle_times && $this->callTimes > $this->gc_cycle_times) {
                $this->callTimes = 0;
                Coroutine::create(function () {
                    $this->gc($this->maxLifeTime, $this->timeout);
                });
            }

            try {
                $ret = $this->create($this->sessionConfigContext['sid'], $this->timeout);
                $data = $ret->allContext();
                if (is_array($data)) {
                    foreach ($data as $key => $val) {
                        $this->sessionDataContext->set(strval($key), $val);
                    }
                }
                $this->sessionConfigContext['isStart'] = true;
            } catch (\Throwable $throwable) {
                // 防止 context 内存泄漏
                $this->close($this->sessionConfigContext['sid'], $this->timeout);
                throw $throwable;
            }
            if ($this->autoClear) {
                Coroutine::defer(function () {
                    $this->close($this->sessionConfigContext['sid'], $this->timeout);
                });
            }
        }
        return $this->sessionConfigContext['isStart'];
    }

    // 将值保存在 session 中，以 $key 作为键，$data 作为值
    function set(string $key, $data)
    {
        $this->start();
        if ($this->sessionDataContext) {
            $this->sessionDataContext->set($key, $data);
        }
    }

    // 获取 session 中 key 为 $key 的值
    function get(string $key)
    {
        $this->start();
        if ($this->sessionDataContext) {
            return $this->sessionDataContext->get($key);
        }
        return null;
    }

    // 删除 session 中 key 为 $key 的值
    function del(string $key)
    {
        $this->start();
        if ($this->sessionDataContext) {
            $this->sessionDataContext->del($key);
        }
    }

    // 清空 session 中的数据
    function flush()
    {
        $this->start();
        if ($this->sessionDataContext) {
            $this->sessionDataContext->flush();
        }
    }

    // 设置(覆盖) session 中的数据
    function setData(array $data)
    {
        $this->start();
        if ($this->sessionDataContext) {
            $this->sessionDataContext->setData($data);
        }
    }

    // 获取 session 中的所有数据
    function all(): array
    {
        $this->start();
        if ($this->sessionDataContext) {
            return $this->sessionDataContext->allContext();
        }
        return [];
    }
}
```

2. 注册 `session handler`。修改 `EasySwoole` 全局 `event` 文件(即框架根目录的 `EasySwooleEvent.php` 文件)，在 `mainServerCreate` 全局事件和 `HTTP` 的 全局 `HTTP_GLOBAL_ON_REQUEST` 事件中注册 `session handler`。

具体实现代码如下:

```php
<?php

namespace EasySwoole\EasySwoole;

use App\Utility\SessionTools;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\FileSession;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response): bool {
            // TODO: 注册 HTTP_GLOBAL_ON_REQUEST 回调，相当于原来的 onRequest 事件
            // 获取客户端 Cookie 中 easy_session 参数
            $cookie = $request->getCookieParams('easy_session');
            if (empty($cookie)) {
                $sid = SessionTools::getInstance()->sessionId();
                // 设置客户端 Cookie 中 easy_session 参数
                $response->setCookie('easy_session', $sid);
            } else {
                SessionTools::getInstance()->sessionId($cookie);
            }
            return true;
        });

        Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response): void {
            // TODO: 注册 HTTP_GLOBAL_AFTER_REQUEST 回调，相当于原来的 afterRequest 事件
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 可以自己实现一个标准的 session handler，下面为组件内置实现的 session_handler
        // 基于文件存储，传入 EASYSWOOLE_TEMP_DIR 目录作为 session 数据文件存储位置
        $handler = new FileSession(EASYSWOOLE_TEMP_DIR);
        
        // 设置 session 数据文件存储前缀为 'easy_session'，超时时间为 3s
        SessionTools::getInstance($handler, 3.0, 'easy_session');
    }
}
```

### 在 `EasySwoole` 中使用 `session`
注册 `session handler` 之后，我们就可以在 `EasySwoole 控制器` 的任意位置使用了。

简单使用示例代码如下:
```php
<?php
/**
 * User: XueSi <1592328848@qq.com>
 * Date: 2021-03-10
 * Time: 20:26
 */
namespace App\HttpController;

use App\Utility\SessionTools;
use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    public function index()
    {
        if (SessionTools::getInstance()->get('a')) {
            var_dump(SessionTools::getInstance()->get('a'));
        } else {
            SessionTools::getInstance()->set('a', time());
        }
    }

    // 获取 session 中的所有数据
    public function getAll()
    {
        var_dump(SessionTools::getInstance()->all());
    }

    // 获取 session 中某个数据
    public function getval()
    {
        var_dump(SessionTools::getInstance()->get('a'));
    }

    // 删除 session 中的某个数据
    public function delval()
    {
        SessionTools::getInstance()->del('a');
        var_dump(SessionTools::getInstance()->get('a'));
    }

    // 清空所有 session 数据
    function des()
    {
        SessionTools::getInstance()->flush();
    }
}
```
然后访问 `http://127.0.0.1:9501/index` (示例请求地址)就可以进行测试设置 `session`，访问 `http://127.0.0.1:9501/des` (示例请求地址)就可以清空所有 `session` 数据，