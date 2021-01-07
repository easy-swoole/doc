---
title: Easyswoole框架设计原理 - 全局事件
---

# 全局事件

## bootstrap

`bootstrap`允许在框架未初始化之前，允许其他初始化业务。版本限制`EasySwoole3.2.5+`

在安装之后产生的`easyswoole`启动脚本文件中：

将会自动判断应用根目录下是否有`bootstrap.php`文件，如果有则加载此文件。

## initialize

框架初始化事件，框架已经完成的工作：
- 加载配置文件
- 初始化`Log/Temp`目录

开发者可进行准备工作：
- 修改`Logger`处理器
- 修改`Trigger`处理器
- 修改`Error`处理器
- 修改`Shutdown`处理器
- 修改`HttpException`全局处理器
- 设置`Http`全局`OnRequest`及`AfterRequest`事件

具体可查看[SysConst.php](https://github.com/easy-swoole/easyswoole/blob/3.x/src/SysConst.php)

示例代码：

```php
// TODO: Implement initialize() method.
date_default_timezone_set('Asia/Shanghai');

// 错误级别
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::ERROR_REPORT_LEVEL,E_ALL);

// error_handler
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::ERROR_HANDLER,function ($errorCode, $description, $file = null, $line = null){
    // 开发者对错误进行处理
});

// shutdown
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::SHUTDOWN_FUNCTION,function (){

});

// onRequest v3.4.x+
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST,function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response){

});
// afterRequest v3.4.x+
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST,function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response){

});
```

## mainServerCreate

主服务创建完成事件，框架已经完成的工作：
- `bootstrap/initialize`加载完成
- 主`SwooleServer`创建成功
- 主`SwooleServer`注册了默认的`onRequest/onWorkerStart/onWorkerStop/onWorkerExit`事件。

可进行操作：
- 注册回调事件
- 注册子服务
- `SwooleTable/Atomic`
- 创建自定义进程
- 注册连接池

### 注册回调事件

`注册WorkerStart`事件：
```php
/** @var \EasySwoole\EasySwoole\Swoole\EventRegister $register **/
$register->add($register::onWorkerStart,function (\Swoole\Server $server,int $workerId){
     var_dump($workerId.'start');
});
```

`注册OnMessage`事件（`Websocket`服务）：

```php
/** @var \EasySwoole\EasySwoole\Swoole\EventRegister $register **/
$register->set($register::onMessage,function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame){

});
```

:::tip
set方法和add方法是不同的, set将会覆盖之前配置的事件回调, 而add是增加一个新的回调。
:::

### Table && Atomic

具体调用方式请看具体章节：

[Table](/Components/Component/tableManager.html)

[Atomic](/Components/Component/atomic.html)

### 添加自定义进程

具体查看[自定义进程](/Components/Component/process.html)

```php
\EasySwoole\Component\Process\Manager::getInstance()->addProcess(new Test('test_process'));
```

### 添加子服务

```php
/** @var \Swoole\Server\Port $subPort **/
$subPort = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer()->addListener('0.0.0.0',9503,SWOOLE_TCP);
$subPort->on('receive',function (\Swoole\Server $server, int $fd, int $reactor_id, string $data){
    var_dump($data);
});
// 配置 具体查看swoole文档
$subPort->set([
    
]);
```

### 注册连接池

如果在自定义进程或者自定义命令中需要使用到连接池,建议在`initialize`注册,代码中执行`\EasySwoole\EasySwoole\Core::getInstance()->initialize();`


```php
// 连接池
$config = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
\EasySwoole\ORM\DbManager::getInstance()->addConnection(new \EasySwoole\ORM\Db\Connection($config));
//创建一个协程调度器
$scheduler = new \Swoole\Coroutine\Scheduler();
$scheduler->add(function () {
    $builder = new \EasySwoole\Mysqli\QueryBuilder();
    $builder->raw('select version()');
    \EasySwoole\ORM\DbManager::getInstance()->query($builder, true);
    //这边重置ORM连接池的pool,避免链接被克隆到子进程，造成链接跨进程公用。
    //DbManager如果有注册多库链接，请记得一并getConnection($name)获取全部的pool去执行reset
    //其他的连接池请获取到对应的pool，然后执行reset()方法
    \EasySwoole\ORM\DbManager::getInstance()->getConnection()->getClientPool()->reset();
});
//执行调度器内注册的全部回调
$scheduler->start();
//清理调度器内可能注册的定时器，不要影响到swoole server 的event loop
\Swoole\Timer::clearAll();
```

## 开发者必读

开发者在`EasySwoole`主服务启动前调用协程`api`，必须如下操作：

```php
use Swoole\Coroutine\Scheduler;
$scheduler = new Scheduler();
$scheduler->add(function() {
    /*  调用协程API */
});
$scheduler->start();
//清除全部定时器
\Swoole\Timer::clearAll();
```