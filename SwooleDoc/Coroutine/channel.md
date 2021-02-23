---
title: easyswoole swoole-协程channel
meta:
  - name: description
    content: easyswoole swoole-协程channel
  - name: keywords
    content: easyswoole swoole-协程channel|easyswoole|swoole|coroutine|channel
---

# Channel

## 介绍

通道，用于协程间的通讯。支持多（生产者/消费者）协程，底层实现了协程的自动切换和调度。     
原理：
- 通道php的array类似，仅占用内存，无io消耗。
- 底层使用php引用计数实现，无内存拷贝，不会产生额外性能消耗。
- channel 基于引用计数实现，零拷贝。

## 方法

### __construct

作用：构造方法     
方法原型：__construct(int $capacity = 1)     
参数说明：
- $capacity 设置容量 大于等于1的整数

> 必须在`onWorkerStart`之后创建使用  
> 不同的进程间内存是隔离的，只能在同一进程的不同协程内进行 push 和 pop 操作

### push

作用：向通道写入数据     
方法原型：push(mixed $data, float $timeout = -1): bool;      
参数说明：
- $data push 数据 任意类型的 PHP 变量，包括匿名函数和资源 (切勿写入空数据)
- $timeout 设置超时时间

> 在通道已满的情况下，push 会挂起当前协程，在$timeout时间内，没有任何消费者消费数据，将发生超时，底层会恢复当前协程，push 调用立即返回 false，写入失败


### pop

作用：从通道内读取数据     
方法原型：pop(float $timeout = -1): mixed;       
参数说明：
- $timeout 设置超时时间

### stats

作用：获取通道状态   
方法原型：stats(): array;    
返回值：
- `consumer_num` 消费者数量，表示通道已空，有`N`个协程正在等待其他协程调用 `push` 方法生产数据
- `producer_num` 生成者数量，表示通道已满，有`N`个协程正在等待其他协程调用 `pop` 方法消费数据
- `queue_num` 通道中的元素数量

### close

作用：关闭通道。    
方法原型：close(): bool;

### length

作用：通道中的元素数量       
方法原型：length(): int;

### isEmpty

作用：判断通道是否为空     
方法原型：isEmpty(): bool;

### isFull

作用：判断通道是否已满     
方法原型：isFull(): bool;

## 属性

### capacity

作用：获取通道缓冲区容量。       
原型：capacity: int;


### errCode

作用：获取错误码。       
原型：errCode: int;

|值|常量|作用|
|----|----|----|
|0|SWOOLE_CHANNEL_OK|成功|
|-1|SWOOLE_CHANNEL_TIMEOUT|超时 pop 失败时 (超时)|
|-2|SWOOLE_CHANNEL_CLOSED|channel已经关闭，继续操作channel|


## 简单示例代码

```php
<?php
Swoole\Coroutine::create(function () {
    $chan = new Swoole\Coroutine\Channel(1);
    // 写入数据
    $chan->push('hello easyswoole!');

    echo '当前通道元素数量' . $chan->length() . "\n";;

    Swoole\Coroutine::create(function () use ($chan) {
        // 读取数据
        echo $chan->pop() . "\n";
        // 通道为空 挂起
        $chan->pop();
    });
    echo '当前通道元素数量' . $chan->length() . "\n";;

    $stats = $chan->stats();

    echo '等待其它协程push的消费者数量' . $stats['consumer_num'] . "\n";
});
```