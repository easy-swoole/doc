---
title: easyswoole swoole-定时器
meta:
  - name: description
    content: easyswoole swoole-定时器
  - name: keywords
    content: easyswoole swoole-定时器|easyswoole|swoole|timer
---

# 定时器

毫秒级定时器，按照指定的周期来调用函数

## 原理和性能

实现原理是基于系统上的 epoll_wait 和 setitimer，信息储存在内存中，所以对定时器的`增加`、`删除`，是内存操作，`无I/O消耗`

## 注意事项

::: warning
- tick定时器内执行时长若大于间隔周期，中间过期周期会被丢弃（不会并发执行）
- tick 回调中默认创建协程，可以直接使用其他协程客户端
- 定时器在当前进程有效
- 定时器在执行的过程中可能存在一定误差（底层会自动校正，影响很小）
:::

## 方法列表

Timer类下拥有：tick、after、clear三个基础方法，由于Swoole历史遗留原因，还拥有函数别名。

- Swoole\Timer::tick() 别名 swoole_timer_tick()，每xx时间执行一次（多次执行）
- Swoole\Timer::after() 别名 swoole_timer_after()，延迟一定时间后执行（执行一次），不会堵塞主流程代码
- Swoole\Timer::clear()	别名 swoole_timer_clear()，清除定时器

以下方法 swoole >= 4.4 可用

- Swoole\Timer::clearAll(): bool; 清除当前进程所有定时器
- Swoole\Timer::info(int $id): array; 定时器信息
- Swoole\Timer::list(); 当前进程定时器列表 （foreach遍历返回id 然后使用info查询）
- Swoole\Timer::stats(): array;  查看定时器状态
- Swoole\Timer::set(array $array): void;  设置定时器内部参数（自动协程）

### 传参说明和示例

#### tick

```php
/**
 * @param $msec 毫秒，4.2.10 以下版本有最大值限制:86400000
 * @param $callback_function 闭包函数 要执行的程序
 * @param $params 传递给闭包函数的参数，非必选
 */ 
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int;

闭包函数触发时，第二个参数为该定时器的id，后续参数为用户传递变量（非必选）
```

示例

```php
Swoole\Timer::tick(500, function(){
    echo "easyswoole\n";
});

Swoole\Timer::tick(500, function($timer_id, $name){
    echo "easyswoole - {$name}\n";
}, 'Siam');
```

#### after

```php
/**
 * @param $msec 毫秒，4.2.10 以下版本有最大值限制:86400000
 * @param $callback_function 闭包函数 要执行的程序
 * @param $params 传递给闭包函数的参数，非必选
 */ 
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int;
```

示例

```php
$name = 'Siam';

Swoole\Timer::after(1000, function() use ($name) {
    echo "easyswoole, 1秒后自动起飞 -- {$name} \n";
});
```

#### clear

```php
/**
 * @param $timer_id 要清除的定时器id
 */ 
Swoole\Timer::clear(int $timer_id): bool;
```

过于简单，没有示例！


#### 关闭自动协程

```php
Swoole\Timer::set([
  'enable_coroutine' => false,
]);
```

