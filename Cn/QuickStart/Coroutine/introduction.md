---
title: easyswoole 基础使用-什么是协程
meta:
  - name: description
    content: easyswoole 基础使用-什么是协程
  - name: keywords
    content: easyswoole 基础使用-什么是协程
---

# 协程

协程不是进程或线程，其执行过程更类似于子例程，或者说不带返回值的函数调用。   

一个程序可以包含多个协程，可以对比与一个进程包含多个线程，因而下面我们来比较协程和线程。而多个线程相对独立，有自己的上下文，切换受系统控制；

而协程也相对独立，有自己的上下文，但是其切换由自己控制，由当前协程切换到其他协程由当前协程来控制。

![协程](/Images/Passage/QuickStart/Coroutine.png)

## 协程执行顺序

原生 `php` 代码:
```php
<?php
function task1()
{
    for ($i = 0; $i <= 300; $i++) {
        // 写入文件，大概要 3000 微秒
        usleep(3000);
        echo "写入文件{$i}\n";
    }
}

function task2()
{
    for ($i = 0; $i <= 500; $i++) {
        // 发送邮件给 500 名会员，大概 3000 微秒
        usleep(3000);
        echo "发送邮件{$i}\n";
    }
}

function task3()
{
    for ($i = 0; $i <= 100; $i++) {
        // 模拟插入 100 条数据，大概 3000 微秒
        usleep(3000);
        echo "插入数据{$i}\n";
    }
}

task1();
task2();
task3();
```
在这个代码中，我们主要做了 3 件事：写入文件、发送邮件、及插入数据。

再看下面这段代码：

```php
<?php
function task1($i)
{
    // 使用 $i 标识 写入文件,，大概要3000微秒
    if ($i > 300) {
        return false;// 超过 300 不用写了
    }
    echo "写入文件{$i}\n";
    usleep(3000);
    return true;
}

function task2($i)
{
    // 使用 $i 标识 发送邮件，大概要 3000 微秒
    if ($i > 500) {
        return false;// 超过 500 不用发送了
    }
    echo "发送邮件{$i}\n";
    usleep(3000);
    return true;
}

function task3($i)
{
    // 使用 $i 标识 插入数据，大概要 3000 微秒
    if ($i > 100) {
        return false;// 超过 100 不用插入
    }
    echo "插入数据{$i}\n";
    usleep(3000);
    return true;
}

$i = 0;
$task1Result = true;
$task2Result = true;
$task3Result = true;
while (true) {
    $task1Result && $task1Result = task1($i);
    $task2Result && $task2Result = task2($i);
    $task3Result && $task3Result = task3($i);
    if ($task1Result === false && $task2Result === false && $task3Result === false) {
        break;// 全部任务完成,退出循环
    }
    $i++;
}
```
这段代码也是做了 3 件事，写入文件、发送邮件和插入数据。但是和上面的不同的是，这段代码将这 3 件事交叉执行，每个任务执行完一次之后，切换到另一个任务，如此循环。类似于这样的执行顺序，就是协程。
> 协程是指一种用代码实现任务交叉执行的逻辑，协程可以使得代码 1 中的 3 个函数交叉运行，在实现了协程的框架中，我们不需要通过代码 2 的方法实现任务交叉执行。直接可让代码 1 中的 while(1)，执行一次后切换。


## 协程的实现

在 `php` 中，实现协程主要使用 2 种方式: 
 * `yield` 生成器实现 (详细原理可查看 [http://www.php20.cn/article/148](http://www.php20.cn/article/148))
 * `swoole` 扩展实现
 
`swoole` 实现协程代码:

```php
<?php
function task1()
{
    for ($i = 0; $i <= 300; $i++) {
        // 写入文件，大概要 3000 微秒
        usleep(3000);
        echo "写入文件{$i}\n";
        Co::sleep(0.001);// 挂起当前协程，0.001 秒后恢复 // 相当于切换协程
    }
}

function task2()
{
    for ($i = 0; $i <= 500; $i++) {
        // 发送邮件给 500 名会员，大概 3000 微秒
        usleep(3000);
        echo "发送邮件{$i}\n";
        Co::sleep(0.001);// 挂起当前协程，0.001 秒后恢复 // 相当于切换协程
    }
}

function task3()
{
    for ($i = 0; $i <= 100; $i++) {
        // 模拟插入 100 条数据，大概 3000 微秒
        usleep(3000);
        echo "插入数据{$i}\n";
        Co::sleep(0.001);// 挂起当前协程，0.001 秒后恢复 // 相当于切换协程
    }
}

$pid1 = go('task1');// go 函数是 swoole 的开启协程函数，用于开启一个协程
$pid2 = go('task2');
$pid3 = go('task3');
```
以上代码，即可实现切换函数。

> 为什么要用 `sleep` 挂起协程实现切换呢？因为 `swoole` 的协程是自动的，当协程内遇上 `I/O` 操作 (mysql、redis) 等时，`swoole` 的协程会自动切换，运行到下一个协程任务中 (切换后，I/O继续执行)，直到下一个协程任务完成或者被切换 (遇上 I/O)，如此反复，直到所有协程任务完成，则任务完成。

## 协程与进程

由上面的 `协程执行顺序` 中的代码 2，我们很容易发现，协程其实只是运行在一个进程中的函数，只是这个函数会被切换到下一个执行，可以这么说：
> 协程只是一串运行在进程中的任务代码，只是这些任务代码可以交叉运行。
> 注意，协程并不是多任务并行，属于多任务串行，每个进程在一个时间只执行了一个任务。
