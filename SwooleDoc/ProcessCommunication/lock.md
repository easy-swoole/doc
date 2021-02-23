---
title: easyswoole swoole-lock
meta:
  - name: description
    content: easyswoole swoole-lock
  - name: keywords
    content: easyswoole swoole-lock|easyswoole|swoole|lock
---

# 进程锁Lock

## 介绍
在php代码中超级方便创建一个锁，用于实现数据同步。

5种锁类型:  
`SWOOLE_FILELOCK` 文件锁   
`SWOOLE_RWLOCK` 读写锁     
`SWOOLE_SEM` 信号量    
`SWOOLE_MUTEX` 互斥锁  
`SWOOLE_SPINLOCK` 自旋锁   

:::warning
不要在`onRequest`、`onReceive`等回调函数中创建计数器，内存会持续增长，造成内存泄漏问题。 
协程间不能使用锁，不要在`lock`和`unlock`之间使用协程api。
:::

## 方法

### __construct
作用：构造函数     
方法原型：__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');  
参数说明：
- $type 锁的类型
- $lockfile 文件锁路径 当锁类型为`SWOOLE_FILELOCK`必须写入该值。

:::warning
不要循环创建销毁锁对象，会造成内存泄漏。
:::

### lock
作用：加锁操作，其它进程持有锁的话，该方法会阻塞，等待其它进程执行`unlock()`释放锁。     
方法原型：lock(): bool;

### lockwait
作用：同`lock()`，可以设置超时时间，规定时间内未获得锁返回false，成功加锁返回true。  
方法原型：lockwait(float $timeout = 1.0): bool;  
参数说明：
- $timeout 超时时间 默认为1s

:::warning
锁类型为`SWOOLE_MUTEX`，才能调用方法。
:::


### trylock
作用：加锁操作，不会阻塞，成功返回true，失败返回false。    
方法原型：trylock(): bool;

:::warning
锁类型为`SWOOlE_SEM`，不可使用该方法。
:::

### unlock
作用：释放锁  
方法原型：unlock(): bool;


### lock_read
作用：只读加锁，其它进程可以继续发生读操作。  
方法原型：lock_read(): bool;
:::warning
锁类型为`SWOOLE_RWLOCK`或者`SWOOLE_FILELOCK`支持此方法。
其它进程调用`lock()`或者`trylock()`，调用此方法会阻塞。
:::


### trylock_read
作用：与`lock_read()`相同，但是为非阻塞。     
方法原型：lock_read(): bool;

## 错误示例代码（死锁）
```php
<?php
$lock = new Swoole\Lock();
$count = 2;
while ($count--) {
    go(function () use ($lock) {
        $lock->lock(); // 加锁
        Co::sleep(1);
        $lock->unlock(); // 解锁
    });
}
```

## 简单示例代码
```php
<?php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "我是爹 我要加锁了\n";
$lock->lock(); // 进行加锁操作
if (pcntl_fork() > 0) {
    sleep(1);
    $lock->unlock();
} else {
    echo "我是儿子 我也要加锁了 但是我得等我爹把锁释放了\n"; // 等待
    $lock->lock();
    echo "我是儿子 我爹释放锁了 我加锁了\n"; // 在老爹释放锁之后输出
    $lock->unlock();
    exit("我是儿子 我释放锁了\n");
}
// 1s后 锁将被释放 儿子的等待完成
echo "我是爹 我释放锁了\n";
unset($lock);
echo "锁没了 别玩了\n";
```