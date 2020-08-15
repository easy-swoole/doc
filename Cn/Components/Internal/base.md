---
title: easyswoole基础组件
meta:
  - name: description
    content: easyswoole基础组件
  - name: keywords
    content: easyswoole component

---

# 基础组件

easyswoole 基础组件提供了以下组件：

- Singleton -- 单例
- CoroutineSingleton -- 协程单例
- ReadyScheduler -- 就绪等待
- CoroutineRunner -- 协程执行器
- TableManager -- Swoole Table
- Atomic -- Atomic 计数器
- ChannelLock -- Channel Lock协程锁

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.4.2
- easyswoole/spl: ^1.1
- easyswoole/utility: ^1.0

## 安装方法

> composer require easyswoole/component

## 仓库地址

[easyswoole/component](https://github.com/easy-swoole/component)

## 基本使用

核心类

> 单例

```php
use \EasySwoole\Component\Singleton;
```

> 协程单例

```php
use \EasySwoole\Component\CoroutineSingleTon;
```

> 就绪等待

```php
use \EasySwoole\Component\ReadyScheduler;
```

> 协程执行器

```php
use \EasySwoole\Component\CoroutineRunner\Runner;
use \EasySwoole\Component\CoroutineRunner\Task;
```

> Swoole Table

```php
use \EasySwoole\Component\TableManager;
```

> Atomic 计数器

```php
use \EasySwoole\Component\AtomicManager;
```

> Channel Lock协程锁

```php
use \EasySwoole\Component\ChannelLock;
```
