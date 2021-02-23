---
title: easyswoole swoole-协程cps并发调用
meta:
  - name: description
    content: easyswoole swoole-协程cps并发调用
  - name: keywords
    content: easyswoole swoole-协程cps并发调用|easyswoole|swoole|coroutine|channel
---

# 并发调用

> 使用 go+channel 实现并发请求

## 原理

- 在回调事件中需要并发多个http请求，可使用go函数创建多个协程
- 并创建了一个 chan，使用 use 闭包引用语法，传递给子协程
- 主协程循环调用 chan->pop，等待子协程完成任务，底层自动进入挂起状态
- 并发的多个子协程其中某个完成请求时，调用 chan->push 将数据推送给主协程
- 子协程完成请求后退出，主协程从挂起状态中恢复，继续向下执行代码

## 简单示例代码

```php
<?php
$server = new Swoole\Http\Server("127.0.0.1", 9503, SWOOLE_BASE);

$server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $chan = new chan(2);
    go(function () use ($chan) {
        $cli = new Swoole\Coroutine\Http\Client('www.easyswoole.com', 80);
        $cli->get('/');
        $chan->push($cli->body);
    });

    go(function () use ($chan) {
        $cli = new Swoole\Coroutine\Http\Client('blog.gaobinzhan.com', 443,true);
        $cli->get('/');
        $chan->push($cli->body);
    });

    $result = [];
    for ($i = 0; $i < 2; $i++)
    {
        $result[] = $chan->pop();
    }
    var_dump($result);
    $response->end('<h1>EasySwoole</h1>');
});
$server->start();

```

使用[waitGroup](/Cn/Swoole/Coroutine/waitGroup.md)更加简单