---
title: easyswoole swoole-自定义进程
meta:
  - name: description
    content: easyswoole swoole-自定义进程
  - name: keywords
    content: easyswoole swoole-自定义进程|easyswoole swoole-进程池|easyswoole|swoole|process/pool
---

# Process

Swoole所提供的进程管理模块，替代php的`pcntl`

:::warning
此模块比较底层，操作系统进程管理的封装，需要使用者有 `linux` 系统多进程编程经验
:::

`PHP` 自带 `pcntl`，有很多不足，如：
- 没有提供进程间通信功能
- 不支持重定向标准输入输出
- 只提供`fork`这样的原始接口，使用起来容易出错

`Process` 使我们在多进程编程方面更加轻松

`Process` 提供的特性：
- 方便实现进程间通讯
- 支持重定向标准输入输出，在子进程 `echo` 不会打印屏幕，直接写入管道，读键盘输入可以重定向为管道读取数据
- 提供了 `exec` 接口，创建的进程可以执行其它程序，与原 `PHP` 父进程之间进行方便的通信
- 在协程环境中无法使用此模块，具体实现参考[协程进程管理](/Cn/Swoole/Coroutine/procOpen.md)

## 属性

### pid
子进程的`PID` `Swoole\Process->pid: int;`

### pipe
文件描述符 `Swoole\Process->pipe;`

## 方法

### __construct
作用：构造方法        
方法原型：__construct(callable $function, bool $redirectStdinStdout = false, int $pipeType = SOCK_DGRAM, bool $enableCoroutine = false);             
参数：
- $function 子进程创建成功后要执行的函数
- $redirectStdinStdout 重定向子进程的标准输入输出 在子进程 `echo` 不会打印屏幕，直接写入管道，读键盘输入可以重定向为管道读取数据
- $pipeType 启用`$redirectStdinStdout` 强制为1 如果子进程内没有进程间通信，可以设置为 0
- $enableCoroutine 在回调中启用协程，可以直接在子进程的函数中使用协程 API

### start
作用：执行`fork`系统调用     
方法原型：Swoole\Process->start(): int|false;
> 子进程会继承父进程的内存和文件句柄
> 子进程在启动时会清除从父进程继承的 事件 信号 定时器

### exportSocket
作用：将 `unixSocket` 导出为[Coroutine\Socket](/Cn/Swoole/Coroutine/Client/socket.md)对象。   
方法原型：Swoole\Process->exportSocket(): Swoole\Coroutine\Socket|false; 
> 多次调用返回的对象是一个
> 导出的 `socket` 是一个新的 `fd`,当关闭导出的 `socket` 时不会影响进程原有的管道。
> 由于为 `Coroutine\Socket` 所以 `$enableCoroutine` 必须为true
> 父进程想用 `Coroutine\Socket` 对象，需要手动 `Co\run()` 以创建协程容器

### name
作用：修改进程名称   
方法原型：Swoole\Process->name(string $name): bool;      
> 在 `start` 之后的子进程回调函数中使用

### exec
作用：执行一个外部程序，是 `exec` 系统调用的封装。       
方法原型：Swoole\Process->exec(string $execFile, array $args);       
参数：
- $execFile 可执行文件的绝对路径
- $args exec的参数列表

### close
作用：用于关闭创建好的 `unixSocket`        
方法原型：Swoole\Process->close(int $which): bool;       
参数：
- $which `unixSocket` 是全双工的，指定关闭哪一端 0：同时关闭读和写 1：关闭读 2：关闭写

### exit
作用：退出子进程        
方法原型：Swoole\Process->exit(int $status = 0);     
参数：
- $status 退出进程的状态码 0 表示正常结束，会继续执行清理工作

> 清理工作有 `php` 的 `shutdown_function` 对象的 `__destruct` 其它扩展的 `RSHUTDOWN` 函数
> 父进程中，执行 `Process::wait` 可以得到子进程退出的事件和状态码。

### kill
作用：向指定 `pid` 进程发生信号。        
方法原型：Swoole\Process::kill(int $pid, int $signo = SIGTERM): bool;        
参数：
- $pid 进程id
- $signo 发送的信号 0 可以检测进程是否存在，不会发送信号

### signal
作用：设置异步信号监听     
方法原型：Swoole\Process::signal(int $signo, callable $callback): bool;      
参数：
- $signo 信号
- $callback 回调函数 如果为null，表示移除信号监听

### wait
作用：回收结束运行的子进程       
方法原型：Swoole\Process::wait(bool $blocking = true): array|false;      
参数：
- $blocking 指定是否阻塞等待

### daemon
作用：使当前进程变为守护进程      
方法原型：Swoole\Process::daemon(bool $nochdir = true, bool $noclose = true): bool;      
参数：
- $nochdir 是否切换当前目录为根目录
- $noclose 否要关闭标准输入输出文件描述符

### alarm
作用：高精度定时器       
方法原型：Swoole\Process::alarm(int $time, int $type = ITIMER_REAL): bool;       
参数：
- $time 定时器间隔时间 负数表示清除定时器
- $type 定时器类型
 - 0 真实时间，触发 `SIGALAM` 信号
 - 1 用户态cpu时间，触发 `SIGVTALAM` 信号
 - 2 用户态 + 内核态时间，触发 `SIGPROF` 信号

> 不能和timer同时使用

### setAffinity
作用：设置cpu亲和性，可以将进程绑定到指定的cpu上     
方法原型：Swoole\Process::setAffinity(array $cpus): bool;        
参数：
- $cpus 绑定 `cpu` 核 `[0,1,3]` 表示绑定 `CPU0/CPU1/CPU3`

## 简单示例代码
```php
$process = new \Swoole\Process(function (\Swoole\Process $process) {
    $socket = $process->exportSocket();
    echo $socket->recv();
    $socket->send("啧啧啧" . PHP_EOL);
    echo "我要出去玩了！\n";
}, false, 1, true);

$process->start();

Swoole\Coroutine::create(function () use ($process) {
    $socket = $process->exportSocket();
    $socket->send("你好儿子" . PHP_EOL);
    echo $socket->recv();
});
\Swoole\Process::wait(true);
```