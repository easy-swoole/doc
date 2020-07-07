---
title: easyswoole swoole-atomic
meta:
  - name: description
    content: easyswoole swoole-atomic
  - name: keywords
    content: easyswoole swoole-atomic|easyswoole|swoole|atmoic
---

# 无锁计数器Atomic
## 介绍
`Atomic`由`Swoole`底层提供的原子计数器，方便整数无锁增减。
- 在不同进程间操作计数。
- 不需要加锁。
- 默认32位无符号类型，64有符合整型请使用`Swoole\Atomic\Long`。

:::warning
不要在`onRequest`、`onReceive`等回调函数中创建计数器，内存会持续增长，造成内存泄漏问题。 
:::


## 方法

### __construct()
作用：构造方法 创建一个计数对象.   
方法原型：__construct(int $initValue = 0);    
参数说明：   
- $initValue  初始化的数值 默认为0

:::warning
最大支持42亿，对负数不支持。
:::

代码:
```php
$atomic = new Swoole\Atomic();
```

### add()
作用：计数器增加数值  
方法原型：add(int $addValue = 1);    
参数说明：
- $addValue 增加的数值 默认为1 （必须使用整数）

:::warning
相加超过42亿 会溢出 数值并且会被丢弃
:::

代码:
```php
$atomic->add(); // 操作成功返回结果数值
```

### sub()
作用：计数器减少数值  
方法原型：sub(int $subValue = 1);    
参数说明：
- $subValue 减少的数值 默认为1 （必须使用整数）

:::warning
相减低于0 会溢出 数值并且会被丢弃
:::

代码:
```php
$atomic->sub(); // 操作成功返回结果数值
```


### get()
作用：返回当前计数器数值.   
方法原型：get(): int;  

代码:
```php
$atomic->get();
```

### set()
作用：指定当前计数器数值.    
方法原型：set(int $value): int;  
参数说明：
- $value 要设置的数值

代码:
```php
$atomic->set(1024);
```

### cmpset()
作用：当前数值等于第一个参数 就把当前数值设置为第二个参数.    
方法原型：cmpset(int $cmpValue, int $setValue): bool;  
参数说明：
- $cmpValue 当前数值等于$cmpValue 返回true 并设置为$setValue 否则返回false
- $setValue 当前数值等于$cmpValue 返回true 并设置为$setValue 否则返回false

代码:
```php
$atomic->cpmset(1024,2048);
```

### wait()
作用：将计数器设置为等待状态 可实现等待、通知、锁的功能    
方法原型：wait(float $timeout = 1.0): bool;  
参数说明：
- $timeout 设置等待超时时间 (-1 永不超时 等待被唤醒)

返回值：
超时返回false 成功返回true（被其它进程唤醒）

:::warning
wait是阻塞进程的，不要在协程环境中使用。  
计数器数值为0或1的时候，才可以使用wait/wakeup方法。  
使用Swoole\Atomic\Long，不可使用此方法。  
:::

代码:
```php
$atomic = new Swoole\Atomic;
$pid = pcntl_fork();
if ($pid > 0) {
    echo "我是父进程 start\n";
    $atomic->wait(1.5);
    echo "我是父进程 end\n";
} else {
    echo "我是子进程 start\n";
    sleep(1);
    $atomic->wakeup();
    echo "我是子进程 end\n";
}
```

### wakeup()
作用：唤醒计数器为等待状态的其它进程.        
方法原型：wakeup(int $n = 1): bool;  
参数说明：
- $n 唤醒进程的数量

:::tip
原子计数为0，说明无进程处于等待状态，wakeup会立即返回true。     
原子计数为1，说明有进程处于等待状态，wakeup会唤醒并且返回true。    
被唤醒的进程会将原子计数器数值设置为0，可以再次调用wakeup唤醒其它等待的进程。    
:::

代码:
```php
$atomic->wakeup();
```



## 简单示例代码
```php
<?php

$server = new Swoole\Server('0.0.0.0', 9502);

$atomic = new Swoole\Atomic();

$server->atomic = $atomic;
$server->on('receive', function (\Swoole\Server $server, $fd) {
    $server->atomic->add(); // 进行 +1 操作 会返回 2
    $server->atomic->set(1024); // 指定计数器数值为1024
    $server->atomic->get(); // 获取数值 会返回 1024
});

$server->start();
```