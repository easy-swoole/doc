---
title: EasySwoole框架设计原理 - mainServerCreate事件解析
meta:
  - name: keywords
    content: easyswoole mainServerCreate事件|easyswoole mainServerCreate
---

# mainServerCreate 事件(即主服务创建事件)

## 函数原型
```php
/**
 * @param \EasySwoole\EasySwoole\Swoole\EventRegister $register
 */
public static function mainServerCreate(EventRegister $register)
{

}
```

## 已完成工作
在执行主服务创建事件时，框架此时已经完成的工作有：
- `bootstrap/initialize` 事件加载完成
- 主 `SwooleServer` 创建成功
- 主 `SwooleServer` 注册了默认的 `onRequest/onWorkerStart/onWorkerStop/onWorkerExit` 事件。

## 开发者可进行的操作有：
- 注册主服务回调事件
- 添加子服务监听
- `SwooleTable/Atomic`
- 创建自定义进程
- 启用前(在 mainServerCreate 事件中)调用协程 API

## 注册主服务回调事件
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

## 添加子服务监听
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

## Table && Atomic

具体调用方式请看具体章节：

[Table](/Components/Component/tableManager.html)

[Atomic](/Components/Component/atomic.html)

## 创建自定义进程
> 具体详细操作可到 [基础使用 -> 自定义进程](/Components/Component/process)中查看

```php
\EasySwoole\Component\Process\Manager::getInstance()->addProcess(new Test('test_process'));
```
> `Test` 是 `EasySwoole\Component\Process\AbstractProcess` 抽象类的子类

## 启用前(在 mainServerCreate 事件中)调用协程 API
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