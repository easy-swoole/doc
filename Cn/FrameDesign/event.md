---
title: Easyswoole框架设计原理 - 全局事件
---

# 全局事件

## bootstrap 事件

`bootstrap` 事件允许在框架未初始化之前，先进行初始化其他需要的业务代码。该事件是在 `EasySwoole 3.2.5版本之后` 新增的。

在框架安装之后产生的 `easyswoole` 启动脚本文件中，将会自动判断框架根目录下是否有 `bootstrap.php` 文件，如果有则加载此文件。

目前框架最新版本的 `bootstrap.php`(即 `bootstrap` 事件)会在框架安装时在项目根目录中自动生成。所以如果用户想要执行自己需要的初始化业务代码：如 `注册命令行支持`、`全局通用函数`、`启动前调用协程 API `等功能，就可以在 `bootstrap.php` 中进行编写实现。

> 注：`EasySwoole 3.4.x` 版本之前 `bootstrap.php` 文件需要用户在项目根目录下自行创建该文件 `bootstrap.php`。

> 注：如果你是框架旧版升级到框架新版，需要删除框架根目录的 `easyswoole` 文件，然后重新运行 `php ./vendor/easyswoole/easyswoole/bin/easyswoole install` 进行重新安装(报错或者其他原因请重新看 [框架安装章节-执行安装步骤](/QuickStart/install))，重新安装完成之后，即可正常使用 `bootstrap` 事件

### 在框架启用前(即在 bootstrap 事件中)调用协程 API
使用示例如下：
```
<?php
// 全局 bootstrap 事件
date_default_timezone_set('Asia/Shanghai');

use Swoole\Coroutine\Scheduler;
$scheduler = new Scheduler();
$scheduler->add(function() {
    /* 调用协程 API */
});
$scheduler->start();
// 清除全部定时器
\Swoole\Timer::clearAll();
```


## initialize 事件

框架初始化事件，在执行 `initialize` 初始化事件时，`EasySwoole` 框架此刻已经完成了如下工作：
- 加载配置文件
- 初始化 `Log/Temp` 目录，完成系统默认 `Log/Temp` 目录的定义

### 函数原型
```
public static function initialize(): void
{
}
```

### 开发者自定义处理
开发者可以在 `initialize` 事件可以进行如下修改：
- 修改框架默认使用的 `error_report` 级别，使用自定义的 `error_report` 级别
- 修改框架默认使用的 `Logger` 处理器，使用自定义的 `Logger` 处理器
- 修改框架默认使用的 `Trigger` 处理器，使用自定义的 `Trigger` 处理器
- 修改框架默认使用的 `Error` 处理器，使用自定义的 `Error` 处理器
- 修改框架默认使用的 `Shutdown` 处理器，使用自定义的 `Shutdown` 处理器
- 修改框架默认使用的 `HttpException` 全局处理器，使用自定义的 `HttpException` 全局处理器
- 设置 `Http` 全局 `OnRequest` 及 `AfterRequest` 事件
- 注册数据库、Redis 连接池

具体可查看 [SysConst.php](https://github.com/easy-swoole/easyswoole/blob/3.x/src/SysConst.php)

使用示例代码：

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

        // 开发者自定义设置 错误级别
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::ERROR_REPORT_LEVEL, E_ALL);

        // 开发者自定义设置 日志处理类(该类需要实现 \EasySwoole\Log\LoggerInterface，开发者可自行查看并实现，方便开发者自定义处理日志)
        $logDir = EASYSWOOLE_LOG_DIR; // 定义日志存放目录
        $loggerHandler = new \EasySwoole\Log\Logger($logDir); // 定义日志处理对象
        \EasySwoole\Component\Di::getInstance()->set(SysConst::LOGGER_HANDLER, $loggerHandler);

        // 开发者自定义设置 Trace 追踪器(该类需要实现 \EasySwoole\Trigger\TriggerInterface，开发者可自行查看并实现，方便开发者自定义处理 Trace 链路)
        // Trace 追踪器需要依据上面的 logger_handler
        \EasySwoole\Component\Di::getInstance()->set(SysConst::TRIGGER_HANDLER, new \EasySwoole\Trigger\Trigger($loggerHandler));

        // 开发者自定义设置 error_handler
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::ERROR_HANDLER, function ($errorCode, $description, $file = null, $line = null) {
            // 开发者对错误进行处理
        });

        // 开发者自定义设置 shutdown
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::SHUTDOWN_FUNCTION, function () {
            // 开发者对 shutdown 进行处理
        });
        
        // 开发者自定义设置 HttpException 全局处理器
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_EXCEPTION_HANDLER, function ($throwable, Request $request, Response $response) {
            $response->withStatus(\EasySwoole\Http\Message\Status::CODE_INTERNAL_SERVER_ERROR);
            $response->write(nl2br($throwable->getMessage() . "\n" . $throwable->getTraceAsString()));
            Trigger::getInstance()->throwable($throwable);
        });
        

        // 开发者自定义设置 onRequest v3.4.x+
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
            // v3.4.x 之前的版本 onRequest 事件在 EasySwoolEvent.php 中已定义，不必重新设置
        });

        // 开发者自定义设置 afterRequest v3.4.x+
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
            // v3.4.x 之前的版本 afterRequest 事件在 EasySwoolEvent.php 中已定义，不必重新设置
        });
        
      
        // 注册数据库连接及连接池(详见：https://www.easyswoole.com/Components/Orm/install.html)
        // 注册 Redis 连接及连接池(详见：https://www.easyswoole.com/Components/Redis/introduction.html)
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

### 启用前(即在 initialize 事件中)调用协程 API

开发者在 `EasySwoole` 主服务启动前调用协程 `api`，必须使用如下操作：
```php
$scheduler = new \Swoole\Coroutine\Scheduler();
$scheduler->add(function() {
    /* 调用协程API */
});
$scheduler->start();
// 清除全部定时器
\Swoole\Timer::clearAll();
```

具体使用示例：
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
        
        $scheduler = new \Swoole\Coroutine\Scheduler();
        $scheduler->add(function() {
            /* 调用协程API */
        });
        $scheduler->start();
        // 清除全部定时器
        \Swoole\Timer::clearAll();
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

### 在 initialize 事件中调用连接池
`initialize` 事件在 `EasySwoole` 生命周期中属于 `主进程`，因此在主进程中创建了连接池可能会导致以下问题：
- 创建了全局的定时器
- 创建了全局的 `EventLoop`
- 创建的连接被跨进程公用，因此我们以服务启动前调用数据库 `ORM` 为例：

服务启动前调用数据库 `ORM`：

> 下文 `\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL')` 获取的 MYSQL 配置，详细参考 [配置文件](/QuickStart/config.md)

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
        $config = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
        \EasySwoole\ORM\DbManager::getInstance()->addConnection(new \EasySwoole\ORM\Db\Connection($config));
        // 创建一个协程调度器
        $scheduler = new \Swoole\Coroutine\Scheduler();
        $scheduler->add(function () {
            $builder = new \EasySwoole\Mysqli\QueryBuilder();
            $builder->raw('select version()');
            \EasySwoole\ORM\DbManager::getInstance()->query($builder, true);
            // 这边重置 ORM 连接池的 pool，避免连接被克隆到子进程，造成连接跨进程公用。
            // DbManager 如果有注册多库连接，请记得一起 getConnection($name) 获取全部的 pool 去执行 reset
            // 其他的连接池请获取到对应的 pool，然后执行 reset() 方法

            // ORM 1.4.31 版本之前请使用 getClientPool() 
            // DbManager::getInstance()->getConnection()->getClientPool()->reset();
            \EasySwoole\ORM\DbManager::getInstance()->getConnection()->__getClientPool()->reset();
        });
        //执行调度器内注册的全部回调
        $scheduler->start();
        //清理调度器内可能注册的定时器，不要影响到swoole server 的event loop
        \Swoole\Timer::clearAll();
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```


## mainServerCreate 事件(即主服务创建事件)

### 函数原型
```php
/**
 * @param \EasySwoole\EasySwoole\Swoole\EventRegister $register
 */
public static function mainServerCreate(EventRegister $register)
{

}
```

### 已完成工作
在执行主服务创建事件时，框架此时已经完成的工作有：
- `bootstrap/initialize` 事件加载完成
- 主 `SwooleServer` 创建成功
- 主 `SwooleServer` 注册了默认的 `onRequest/onWorkerStart/onWorkerStop/onWorkerExit` 事件。

### 开发者可进行的操作有：
- 注册主服务回调事件
- 添加子服务监听
- `SwooleTable/Atomic`
- 创建自定义进程

### 注册主服务回调事件
例如：为主服务注册 `onWorkerStart` 回调事件：
```php
/** @var \EasySwoole\EasySwoole\Swoole\EventRegister $register **/
$register->add($register::onWorkerStart, function (\Swoole\Server $server,int $workerId){
     var_dump($workerId . 'start');
});
```

例如：为主服务增加 `onMessage` 回调事件（前提是主服务类型为 `WebSocket` 服务）：
```php
// 给 server 注册相关事件，在 WebSocket 服务模式下 message 事件必须注册 
/** @var \EasySwoole\EasySwoole\Swoole\EventRegister $register **/
$register->set($register::onMessage,function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame){

});
```

:::tip
`set` 方法和 `add` 方法是不同的, set 将会覆盖之前配置的事件回调, 而 add 是增加一个新的回调。
:::

### 添加子服务监听
例如：添加一个 `tcp` 子服务监听
```php
/** @var \Swoole\Server\Port $subPort **/
$subPort = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer()->addListener('0.0.0.0', 9503, SWOOLE_TCP);
$subPort->on('receive', function (\Swoole\Server $server, int $fd, int $reactor_id, string $data){
    var_dump($data);
});
// 配置 具体查看 Swoole 文档
$subPort->set([
    
]);
```
> 具体可参考 [TCP](/Socket/tcp)

### Table && Atomic

具体调用方式请看具体章节：

[Table](/Components/Component/tableManager.html)

[Atomic](/Components/Component/atomic.html)

### 创建自定义进程
> 具体详细操作可到 [基础使用 -> 自定义进程](/Components/Component/process)中查看

```php
\EasySwoole\Component\Process\Manager::getInstance()->addProcess(new Test('test_process'));
```
> `Test` 是 `EasySwoole\Component\Process\AbstractProcess` 抽象类的子类



