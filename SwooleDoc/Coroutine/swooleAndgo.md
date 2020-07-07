---
title: easyswoole swoole协程与go协程的区别
meta:
  - name: description
    content: easyswoole swoole协程与go协程的区别
  - name: keywords
    content: easyswoole swoole协程与go协程的区别|easyswoole|swoole|coroutine
---

# Swoole协程

`Swoole` 的协程调度器，大家应该都知道吧。是属于单线程的，不存在数据同步问题，同一时间只会有一个协程去执行。
对 `cpu` 多核利用，需依赖于 `Swoole` 多进程机制。

继4.x 版本使用了 `c` 栈 + `php` 栈的协程实现方案。我们创建的 `Server` 程序每次请求的事件回调函数中会创建一个新协程（称之为初代协程），处理完成后协程退出。

协程创建的时候需要一个全新的内存段作为`c` 和 `php` 的栈，底层默认分配`2M(c)`虚拟内存+`8k(php)`内存，这是对于 `php7.2+` 版本。

这个 `c` 栈主要用于保存底层函数的调用的局部变量数据，解决 `call_user_func`、`array_map` 等 `c` 函数调用在协程切换时未能还原问题。

`php` 栈主要保存函数调用的局部变量数据，主要是`zval`结构体，`php`中标量类型(整型，浮点，布尔，字符)这些是直接保存在`zval`结构体内，而其它的变量类型是使用引用计数去管理的，在堆上存储。

`c` 栈切换使用了 `boost.context 1.60` 汇编代码，用于保存寄存器，切换指令序列，由 `jump_fcontext` 这个 `ASM` 函数提供。

主协程即为 `Reactor` 协程，负责整个 `EventLoop` 的运行。实现事件监听，在 `io` 事件完成后唤醒其它工作协程。

协程挂起：
在工作协程执行一些 `io` 操作，底层会将 `io` 事件注册到 `EventLoop`，让出执行权。
- 嵌套创建的非初代协程，会逐个让出到父协程，直到回到主协程。
- 在主协程上创建的初代协程，会立即回到主协程。
- 主协程的 `Reactor` 会继续处理其它的事件。

协程恢复：
当主协程的 `Reactor` 接收到新的 `IO` 事件，底层会挂起主协程，并恢复 `IO` 事件对应的工作协程。该工作协程挂起或退出时，会再次回到主协程。

Swoole4.x 协程实现方案：
![SwooleCoroutine](/Images/Swoole/SwooleCoroutine.png)

- API层也就是我们使用的一些协程函数 `go()`，`Co::yield()`，`Co::resume()`。
- 其次，Swoole协程需要管理 `c` 栈 + `php` 栈，`Coroutine` 管理 `c` 栈，`PHPCoroutine` 管理 `php` 栈。
- 其中的 `Coroutine()`，`yield()`，`resume()` 实现了 `c` 栈的创建及换入换出。
- `create_func()`，`on_yield()`，`on_resume()` 实现了 `php` 栈的创建以及换入换出。
- Swoole4在管理 `c` 栈，用到了 `boost.context`，实现了 `c` 栈的上下文创建及切换。
- Swoole4对 `boost.context` 进行了简单封装，即`Context`层，`Context()`，`SwapIn()` 以及 `SwapOut()` 对应 `c` 栈的创建以及换入换出。

```php
<?php
// 用swoole创建一个http服务
$server = new Swoole\Http\Server('0.0.0.0', 9501);

// 调用onrequest事件回调时，底层 c 函数 coro_create 去创建一个协程 并且保存这个时间点的cpu寄存器状态和zendvm stack的信息
$server->on('Request', function($request, $response) {
    // 我们去创建一个mysql的协程客户端
    $mysql = new Swoole\Coroutine\MySQL();
    
    // 调用 mysql->connect 会发生io操作，底层调用c函数 coro_save 保存当前协程状态 包括 zendvm 上下文及协程信息
    // 调用 coro_yield 让出执行权，挂起当前请求，让出控制权之后，会进入 eventloop 处理其它事件，这时swoole会继续处理其它客户端发来的 request
    $res = $mysql->connect([
        'host'     => '127.0.0.1',
        'user'     => 'root',
        'password' => 'gaobinzhan',
        'database' => 'test'
    ]);
    
    // io 事件完成后，不管来连接成功或者失败，底层调用c函数 coro_resume 恢复对应协程，恢复zendvm上下文，继续向下执行php代码
    if ($res == false) {
        $response->end("mysql连接失败！");
        return;
    }
    
    // mysql->query 的执行过程和 mysql->connect 一致，也会进行一次协程切换调度
    $ret = $mysql->query('show tables', 2);
    
    // 所有操作完成后，调用 end 方法返回结果，并销毁此协程。
    $response->end('EasySwoole 简单学swoole');
});

// 启动服务
$server->start();
```

# Go协程
`Groutine` 是轻量级线程。
`Groutine` 的 `Stack` 初始化大小为2k。
`Go` 协程是基于多线程的，可以利用多核cpu，同一时间可能会有多个协程在执行。
`Go` 在语言层面就已经支持协程，只要发生io操作，网络请求都会发生协程切换。

GMP调度：

![GMP调度](/Images/Swoole/GoGMP.png)

**M**：系统线程

**P**：Go实现的协程处理器

**G**：协程

从图中可看出，**Processor** 在不同的系统线程中，每个 **Processor** 都挂着准备运行的协程队列。

**Processor** 依次运行协程队列中的协程。

这时候问题就来了，假如一个协程运行的时间特别长，把整个 **Processor** 都占住了，那么在队列中的协程是不是就会被延迟的很久？

在Go启动的时候，会有一个守护线程来去做一个计数，计每个 **Processor** 运行完成的协程的数量，当一段时间内发现，某个 **Processor** 完成协程的数量没有发生变化的时候，就会往这个正在运行的协程任务栈插入一个特别的标记，协程在运行的时候遇到非内联函数，就会读到这个标记，就会把自己中断下来，然后插到这个等候协程队列的队尾，切换到别的协程进行运行。

当某一个协程被系统中断了，例如说 **io** 需要等待的时候，为了提高整体的并发，**Processor** 会把自己移到另一个可使用的系统线程当中，继续执行它所挂的协程队列，当这个被中断的协程被唤醒完成之后，会把自己加入到其中某个 **Processor** 的队列里，会加入到全局等待队列中。

当一个协程被中断的时候，它在寄存器里的运行状态也会保存到这个协程对象里，当协程再次获得运行状态的时候，重写写入寄存器，继续运行。

# 总结

并行：同一时刻，同一个cpu只能执行一个任务，要同时执行多个，需要多个cpu。     
并发：cpu切换时间任务非常快，就会觉得有很多任务在同时执行。     
协程是轻量级的线程，开销非常小。        
Swoole的协程客户端需要在协程的上下文环境中去使用。        
Swoole协程调度器是单线程的，需要开启多进程模型。      