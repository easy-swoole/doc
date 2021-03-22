---
title: easyswoole协程环境介绍
meta:
  - name: description
    content: easyswoole协程环境介绍
  - name: keywords
    content: easyswoole协程环境介绍|swoole协程
---

# 协程

::: tip
从 `4.0` 版本开始 `Swoole` 提供了完整的协程（`Coroutine`）+ 通道（`Channel`）特性，带来全新的 `CSP 编程模型`。应用层可使用完全同步的编程方式，底层自动实现异步IO。
:::

协程可以理解为纯用户态的线程，其通过协作而不是抢占来进行切换。相对于进程或者线程，协程所有的操作都可以在用户态完成，创建和切换的消耗更低。`Swoole` 可以为每一个请求创建对应的协程，根据 `IO` 的状态来合理的调度协程，这会带来了以下优势：
- 开发者可以无感知的用同步的代码编写方式达到 `异步IO` 的效果和性能，避免了传统异步回调所带来的离散的代码逻辑和陷入多层回调中导致代码无法维护；
- 同时由于底层封装了协程，所以对比传统的 `PHP` 层协程框架，开发者不需要使用 `yield` 关键词来标识一个 `协程IO操作`，所以不再需要对 `yield` 的语义进行深入理解以及对每一级的调用都修改为 `yield`，这极大地提高了开发效率；
- 可以满足大部分开发者的需求。对于私有协议，开发者可以使用协程的 `TCP` 或者 `UDP` 接口去方便的封装。


# 注意事项

- 全局变量：协程使得原有的异步逻辑同步化，但是在协程中的切换是隐式发生的，所以在协程中切换的前后不能保证 `全局变量` 以及 `static 变量` 的一致性。
- `swoole` 协程与 `xdebug、xhprof、blackfire` 等 `zend` 扩展不兼容，例如不能使用 `xhprof` 对 `协程 server` 进行性能分析采样。

# 在 EasySwoole 中使用和创建协程

当提示类似 `PHP Fatal error:  Uncaught Swoole\Error: API must be called in the coroutine in /root/easyswoole/test_coroutine.php:7` 错误时，说明该 `API` 必须在协程环境下使用。

## 在 `EasySwoole` 框架主进程中使用协程

这里所说的主进程主要指的是在 `EasySwoole` 服务启动前调用协程 `API` 的需求，包括在 `EasySwoole` 的 `bootstrap 事件`、`initialize 事件`、`mainServerCreate 事件` 中使用协程。关于前文提到的事件详细请看 [全局事件](/FrameDesign/event/bootstrap.md)

简单使用示例如下：

```php
<?php
$scheduler = new \Swoole\Coroutine\Scheduler();
$scheduler->add(function() {
    /* 调用协程API */
    
    // 用户可以在这里调用上述协程 API
    
});
$scheduler->start();
// 清除全部定时器
\Swoole\Timer::clearAll();
```

## 在 `EasySwoole` 框架 `Worker` 进程中使用协程

这里所说的 `Worker` 进程是指 `EasySwoole` 服务启动之后的进程中调用协程 `API` 的需求，主要包括在 `Http 控制器`、`自定义进程` 等进程中调用协程 `API`。

简单使用示例如下：

```php
<?php
\Swoole\Coroutine::create(function () {
    /* 调用协程API */
        
    // 用户可以在这里调用上述协程 API
});

// 或者使用如下：
go(function() {
    /* 调用协程API */
            
    // 用户可以在这里调用上述协程 API
});
```