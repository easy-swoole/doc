---
title: easyswoole SyncInvoker
meta:
  - name: description
    content: easyswoole SyncInvoker
  - name: keywords
    content: easyswoole SyncInvoker|swoole SyncInvoker|swoole同步转协程
---
# SyncInvoker 组件

## 使用场景

`Swoole4.x` 后，提供了非常强大的协程能力，让我们可以更好地压榨服务器性能，提高并发。然而，目前 `PHP` 在 `Swoole` 协程生态上，并不是很完善，比如：没有协程版本的 `MonogoDB` 客户端，而为了避免在 `Worker` 进程中调用了同步阻塞的 `Api`，例如在 `Http` 回调中使用了同步的 `MonogoDB` 客户端，导致 `Worker` 进程退化为同步阻塞，导致无法完全地发挥协程的优势。所以 `EasySwoole` 提供了一个同步程序协程调用转化驱动。

## 设计原理

启动自定义进程监听 `UnixSocket`，然后在 `Worker` 进程中调用协程客户端发送命令到自定义进程并处理，然后把处理结果返回给 `Worker`进程中的协程客户端。

## 组件要求
- php: >= 7.1.0
- ext-swoole: >= 4.4.23
- easyswoole/component: ^2.0
- opis/closure: ^3.5

## 安装方法
> composer require easyswoole/sync-invoker

## 仓库地址
[easyswoole/sync-invoker](https://github.com/easy-swoole/sync-invoker)

## 基本使用
首先定义一个驱动工作实例（可以定义多个），示例代码如下：
```php
<?php

namespace App\Utility;

use EasySwoole\SyncInvoker\AbstractDriver;

class MyInvokerDriver extends AbstractDriver
{
    private $stdclass;

    function __construct()
    {
        $this->stdclass = new \stdClass();
        parent::__construct();
    }

    public function test($a, $b)
    {
        $this->response($a + $b);
    }

    public function a()
    {
        $this->response('this is a');
    }

    public function getStdClass()
    {
        return $this->stdclass;
    }
}
```

然后注册一个对应的调用器，示例代码如下：
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\SyncInvoker\SyncInvoker;

// 注册一个对应的调用器
class MyInvoker extends SyncInvoker
{
    use Singleton;
}
```

最后在 `EasySwoole全局事件` 中 的 `mainServerCreate` 事件中进行注册
```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $invokerConfig = \App\Utility\MyInvoker::getInstance()->getConfig();

        // 以下这些配置都是可选的，可以使用组件默认的配置
        /*
        $invokerConfig->setServerName('EasySwoole'); // 设置服务名称，默认为 'EasySwoole'
        $invokerConfig->setWorkerNum(3); // 设置 Worker 进程数，默认为 3
        $invokerConfig->setTempDir(EASYSWOOLE_ROOT . '/Temp'); // 设置 unixSocket 存放目录，默认为 系统临时文件存放目录('/tmp')
        $invokerConfig->setMaxPackageSize(2 * 1024 * 1024); // 设置最大允许发送数据大小，默认为 2M
        $invokerConfig->setTimeout(3.0); // 设置服务调用超时时间，默认为 3.0 秒
        $invokerConfig->setAsyncAccept(true); // 设置异步接收数据，默认为 异步接收(不建议修改)
        $invokerConfig->setOnWorkerStart(function (\EasySwoole\SyncInvoker\Worker $worker) {
            var_dump('worker start at Id ' . $worker->getArg()['workerIndex']);
        }); // 设置服务启动时执行的事件回调
        */

        $invokerConfig->setDriver(new \App\Utility\MyInvokerDriver()); // 设置驱动工作实例【必须配置】

        // 注册 Invoker
        \App\Utility\MyInvoker::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

在框架服务启动后，即可在框架的任意位置调用 Invoker 服务了，使用示例如下：

例如在控制器中进行调用：
```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    public function index()
    {
        $ret = \App\Utility\MyInvoker::getInstance()->invoke()->test(1, 2);
        var_dump($ret);
        var_dump(\App\Utility\MyInvoker::getInstance()->invoke()->a());
        $ret = \App\Utility\MyInvoker::getInstance()->invoke()->callback(function (\App\Utility\MyInvokerDriver $driver) {
            $std = $driver->getStdClass();
            if (isset($std->time)) {
                return $driver->response($std->time);
            } else {
                $std->time = time();
                return $driver->response('new set time');
            }
        });
        var_dump($ret);
    }
}

/**
 * 输出结果：
 * int(3)
 * string(9) "this is a"
 * string(12) "new set time"
 * int(3)
 * string(9) "this is a"
 * int(1611071672)
 */
```

## 注意事项

- 尽量使用函数名调用方式，闭包方式调用会存在部分闭包函数序列化失败问题
- 传递参数，返回结果尽量用数组或者字符串传递，资源对象无法序列化
