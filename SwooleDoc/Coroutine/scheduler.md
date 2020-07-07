---
title: easyswoole swoole-协程容器(Coroutine\Scheduler)
meta:
  - name: description
    content: easyswoole swoole-协程容器(Coroutine\Scheduler)
  - name: keywords
    content: easyswoole swoole-协程容器(Coroutine\Scheduler)|easyswoole|swoole|coroutine
---

# Scheduler

所有的协程必须在`协程容器`里面创建，`Swoole`程序启动大部分情况会自动创建容器，`Swoole`启动方式三种：
- 调用异步风格服务端程序`start()`,参考[enable_coroutine](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#enable_coroutine)
- 调用进程管理模块`Process`和`Process\Pool`的`start()`
- 最简单的方法直接裸奔，`Co\run()` 如下：
```php
<?php
Co\run(function () {
    $server = new Co\Http\Server('0.0.0.0', 9510, false);
    $server->handle('/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        $response->end("<h1>EasySwoole</h1>");
    });
    $server->handle('/gaobinzhan', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        $response->end("<h1>gaobinzhan</h1>");
    });
    $server->handle('/stop', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo '啧啧啧，怎么才能学好协程呢！';// /stop之后会执行
```

> 在`Swoole v4.4+`版本可用

::: warning
不可以嵌套 `Co\run()`。`Co\run()` 里的逻辑假如有未处理的事件在 `Co\run()` 之后就进行 `EventLoop`，后面的代码将不会得到执行，如果没有事件了将继续向下执行，可再次 `Co\run()`。
:::

## 方法

### set

作用：设置协程运行时函数 是`Coroutine::set`的别名，参考[Coroutine::set](/Cn/Swoole/Coroutine/method.html)    
方法原型：set(array $options): bool;      
示例：
```php
<?php
$scheduler = new \Co\Scheduler();
$scheduler->set(['max_coroutine' => 50]);
```

### add

作用：添加任务     
方法原型：add(callable $fn, ...$args): bool;     
参数：
- $fn 回调函数
- ...$args 可选参数，传递给协程

> 与`go`函数不同，所有的任务将在`start`之后执行 未调用`start`将不执行

### parallel

作用：添加并行任务 创建n个协程 并行执行       
方法原型：parallel(int $num, callable $fn, ...$args): bool;      
参数：
- $num 启动协程的个数
- $fn 回调函数
- ...$args 可选参数，传递给协程

### start

作用：启动程序 遍历协程任务执行        
方法原型：start(): bool;     
返回值：
- 启动成功，执行所有任务，所有协程退出时`start`返回`true`
- 启动失败返回`false`，可能是已经启动了或者已经创建了其他调度器无法再次创建

## 简单示例代码
```php
<?php
$scheduler = new \Co\Scheduler();
$scheduler->set(['max_coroutine' => 50]);

$scheduler->add(function () {
    echo '第一个任务' . PHP_EOL;
});

$scheduler->add(function () {
    echo '第二个任务' . PHP_EOL;
});

$scheduler->add(function ($title) {
    echo assert($title == 'easyswoole') . PHP_EOL;
}, 'easyswoole');

$scheduler->parallel(10, function ($time) {
    Co::sleep($time);
    echo "Co " . Co::getCid() . ': easyswoole' . PHP_EOL;
}, 0.05);

$scheduler->start();
```