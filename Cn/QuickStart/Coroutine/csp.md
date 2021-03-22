---
title: easyswoole协程Csp并发编程
meta:
  - name: description
    content: easyswoole协程Csp并发编程
  - name: keywords
    content: easyswoole协程Csp并发编程|swoole Csp并发编程
---

# Csp 并发模式

使用 `子协程(go)` + `通道(channel)` 实现 `Csp` 并发模式并发执行。

当我们需要并发执行某些不相干的请求，并得到结果的时候，例如：

```php
$sql1->exec();
$sql2->exec();
$sql2->exec();
```

在以上的代码中，我们没办法最大的节约时间，因为 `sql` 语句都是顺序执行的，因此我们引入了 `Csp` 并发编程的概念。

## 示例代码

```php
<?php
go(function () {
    $channel = new \Swoole\Coroutine\Channel();
    go(function () use ($channel) {
        // 模拟执行sql
        \co::sleep(0.1);
        $channel->push(1);
    });
    go(function () use ($channel) {
        // 模拟执行sql
        \co::sleep(0.1);
        $channel->push(2);
    });
    go(function () use ($channel) {
        // 模拟执行sql
        \co::sleep(0.1);
        $channel->push(3);
    });

    $i = 3;
    while ($i--) {
        var_dump($channel->pop());
    }
});
```

::: tip
  当然，在以上的代码中，我们没有充分地考虑超时等情况
:::

## 进一步封装

```php
<?php
go(function () {
    $csp = new \EasySwoole\Component\Csp();
    $csp->add('t1', function () {
        \co::sleep(0.1);
        return 't1 result';
    });
    $csp->add('t2', function () {
        \co::sleep(0.1);
        return 't2 result';
    });

    var_dump($csp->exec());
});
```

::: warning 
  `exec` 方法提供了一个默认参数：超时时间(默认为 `5s`)，当调用 `$csp->exec()` 后，最长等待 `5s` 左右会返回结果。如果你在 `t2` 函数中 `co::sleep(6)`，那么 `5s` 后，返回的数据中不会包含 `t2` 函数的返回数据。
:::
