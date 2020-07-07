---
title: easyswoole swoole-协程服务端
meta:
  - name: description
    content: easyswoole swoole-协程服务端
  - name: keywords
    content: easyswoole swoole-协程服务端|easyswoole|swoole|coroutine|
---

# 服务端(协程风格)

## 介绍
`Swoole\Coroutine\Server` 是完全协程化实现的服务器

## 优点
- 没有异步风格的并发问题
- 无需要设置事件回调，建立连接、接收数据、发送数据、关闭连接都是顺序的。
- 动态控制服务的开启，异步风格的服务在 start() 被调用后啥也不能干。

代码(异步风格)：
```php
<?php
$server = new Swoole\Server("127.0.0.1", 9501);

$server->on('Connect', function ($server, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);
    Co::sleep(10);//模拟connect比较慢的情况
    $redis->set($fd,$fd);
});

$server->on('Receive', function ($server, $fd, $from_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);
    var_dump($redis->get($fd));//假如这里的连接比上面的快 将会发生 数据没有被set进去 导致逻辑出错
});

$server->on('Close', function ($server, $fd) {
});

$server->start();
```
从代码层面可以看出 异步风格服务无法保证 `OnConnect` 完全执行结束后才进入 `OnReceive`，因此在开启协程化，io操作都会发生协程调度，异步风格服务无法保证调度顺序。


## 缺点

- 协程风格的服务不会自动创建多个进程 需要配合`Process\Pool`模块使用
- 协程风格编写必须要有socket编程经验
- 封装层级没有异步风格服务高，有些功能需要自己实现，比如`reload`需要自己监听信号来做逻辑