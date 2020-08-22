---
title: easyswoole channel Lock
meta:
  - name: description
    content: ChannelLock 通过协程channel特性实现了关于协程级的锁机制。
  - name: keywords
    content: easyswoole ChannelLock
---

# channel Lock

ChannelLock 通过 `协程channel` 特性实现了关于协程级的锁机制。

## 基本使用

### 方法列表

lock: 尝试锁住$lockName
- `$lockName` 锁名
- `$timeout` 超时时间,-1为永久不超时 当调用此函数后,会尝试锁住 `$lockName` ,成功将返回 `true` ,如果之前已经有其他协程锁住了此 `$lockName` ,将会阻塞,直到超时返回 `false` (-1用不超时,代表永远阻塞)

```php
public function lock(string $lockName, float $timeout = -1): bool
```

unlock: 解锁
- `$lockName` 锁名
- `$timeout` 超时时间,-1为永久不超时 解锁 `$lockName`. 成功后将返回 `true` .

```php
public function unlock(string $lockName, float $timeout = -1): bool
```

deferLock: 尝试锁住 `$lockName` ,并在协程结束后自动解锁.
- `$lockName` 锁名
- `$timeout` 超时时间,-1为永久不超时

```php
public function deferLock(string $lockName, float $timeout = -1): bool
```

### 简单示例

```php
go(function (){
    //加锁
    $result = \EasySwoole\Component\ChannelLock::getInstance()->lock('a');
    var_dump($result);
    co::sleep(1);
    //解锁
    $result = \EasySwoole\Component\ChannelLock::getInstance()->unlock('a');
    var_dump($result);
});
```
