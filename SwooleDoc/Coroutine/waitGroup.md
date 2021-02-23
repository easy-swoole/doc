---
title: easyswoole swoole-协程waitGroup
meta:
  - name: description
    content: easyswoole swoole-协程waitGroup
  - name: keywords
    content: easyswoole swoole-协程waitGroup|easyswoole|swoole|coroutine|waitGroup
---

# WaitGroup

> 我们可以用 `channel` 实现协程间通讯、依赖管理、协程同步以及 `Golang` 的 `sync.WaitGroup `功能。


## 方法

- `add` 增加任务计数
- `done` 说明任务已完成
- `wait` 将等待所有任务完成，恢复目前协程执行
- `WaitGroup` 复用对象，`add`、`done`、`wait` 之后可以再次使用

## 简单示例代码

```php
<?php
Swoole\Coroutine::create(function () {
    $wait = new \Swoole\Coroutine\WaitGroup();
    $result = [];


    // start one coroutine
    $wait->add();
    go(function () use ($wait, &$result) {
        $cli = new \Swoole\Coroutine\Http\Client('www.easyswoole.com', 80);
        $cli->get('/');
        $result['easyswoole'] = $cli->body;
        $cli->close();

        $wait->done();
    });

    //start two coroutine
    $wait->add();
    go(function () use ($wait, &$result) {
        $cli = new \Swoole\Coroutine\Http\Client('blog.gaobinzhan.com', 443, true);
        $cli->get('/archives/42.html');

        $result['gaobinzhan'] = $cli->body;
        $cli->close();

        $wait->done();
    });

    $wait->wait();//挂起当前协程，等待所有任务完成后执行下面代码
    //这里 $result 包含了 2 个任务执行结果
    var_dump($result);
});
```