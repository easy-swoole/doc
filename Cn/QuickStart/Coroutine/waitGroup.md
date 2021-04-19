---
title: easyswoole实现waitGroup
meta:
  - name: description
    content: easyswoole实现waitGroup
  - name: keywords
    content: easyswoole实现waitGroup|swoole实现waitGroup
---

# WaitGroup

`EasySwoole` 基于 `Swoole` 的 `Channel` 封装实现了 `Golang` 的 `sync.WaitGroup` 功能。具体使用示例可以看下文：

## 提供的方法
- `add` 方法增加计数
- `done` 表示任务已完成
- `wait` 等待所有任务完成恢复当前协程的执行
- `WaitGroup` 对象可以复用，`add`、`done`、`wait` 之后可以再次使用

## 使用示例代码

```php
<?php
go(function () {
    $ret = [];

    $wait = new \EasySwoole\Component\WaitGroup();

    $wait->add();
    // 启动第 1 个协程
    go(function () use ($wait, &$ret) {
        // 模拟耗时任务 1
        \co::sleep(0.1);
        $ret[] = time();
        $wait->done();
    });

    $wait->add();
    // 启动第 2 个协程
    go(function () use ($wait, &$ret) {
        // 模拟耗时任务 2
        \co::sleep(2);
        $ret[] = time();
        $wait->done();
    });

    // 挂起当前协程，等待所有任务完成后恢复
    $wait->wait();

    // 这里 $ret 包含了 2 个任务执行结果
    var_dump($ret);
});
```
