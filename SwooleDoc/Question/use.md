---
title: easyswoole swoole-使用问题
meta:
  - name: description
    content: easyswoole swoole-使用问题
  - name: keywords
    content: easyswoole swoole-使用问题|easyswoole|swoole
---


# 使用问题

## 怎么维护tcp长连接

`swoole` 提供了两组配置，[tcp_keepalive](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#open_tcp_keepalive) 和 [heartbeat](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#heartbeat_check_interval)

## 不要在send后 立即close

`send` 完后立即 `close` ，服务器端还是客户端都不安全

`send` 操作成功后只是表示数据写入到操作系统缓存区，不是说对端接受到了数据，操作系统有没有发送成功到对端，对端是否成功接收到数据，是没有办法确切保证的。

## 如何正确重启服务

`swoole` 提供了柔性终止及重启的机制，需要向 `Server` 发送信号或者调用 [reload](/Cn/Swoole/ServerStart/Tcp/method.html#reload) 方法

需要注意：新修改的代码必须要在 `OnWorkerStar` 事件中重载方可生效

`reload` 需要配合 [max_wait_time](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#max_wait_time) 和 [reload_async](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#reload_async) 才能实现异步安全重启

假如没有设置此特效，worker 进程收到重启信号或者达到了 [max_request](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#max_request)，会立即停止服务，进行重新拉起。所面临的问题 假如在worker进程内有事件监听，这时候异步任务会被丢弃。

设置上述所说此特性，worker会在完成事件后进行 reload

还有一种可能就是 worker 一直不退出 在约定时间[max_wait_time](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#max_wait_time) 和 [reload_async](/Cn/Swoole/ServerStart/Tcp/serverSetting.html#reload_async)没有退出，底层会强行终止

代码：

```php
$server = new Swoole\Server("0.0.0.0", 9501);
$server->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {

    Swoole\Timer::tick(2000, function () {
        echo 'easyswoole 牛逼';
    });
});

$server->start();
```

没有设置 reload_async 那么定时器会被丢弃

当进程退出 会触发 `onWorkerExit` 事件 我们可以去清理一些长连接

## client has already been bound to another coroutine

一个 `tcp` 连接，`swoole`底层不允许多个协程对 `tcp` 进行读/写操作，底层会抛出错误

```bash
Fatal error: Uncaught Swoole\Error: Socket#5 has already been bound to another coroutine#2, reading or writing of the same socket in coroutine#3 at the same time is not allowed
```

复现错误的代码：

```php
Swoole\Coroutine::create(function() {
    $cli = new Swoole\Coroutine\Http\Client('www.easyswoole.com', 80);
    go(function () use ($cli) {
        $cli->get("/");
    });
    go(function () use ($cli) {
        $cli->get('/');
    });
});
```

## Call to undefined function Co\run() 或者 Call to undefined function go() 

通过 Co\run 或者 go 来创建协程容器

说明关闭了协程短名称

- 打开就行了啊 哈哈哈 怎么打开 👉[点我](/Cn/Swoole/Other/swooleINI.html)
- 用 `Coroutine::create()` 方法

## 能共用 1个 redis连接 或者 mysql连接 吗？？？

答案是不可以哟！！！而且是绝对不可以！！自个别瞎胡闹啊

必须每个进程单独创建，如果共用会导致返回的结果无法被保证是被哪个进程处理的，会造成数据错乱！！

- 在 `Swoole\Server` 要在 `onWorkerStart` 中创建连接对象
- 在 `Swoole\Process` 要在 `star` 后，所触发的子进程回调函数中去创建
- 对使用 `pcntl_fork` 的程序也会造成以上的问题

正确示例代码：

```php
<?php
$server = new Swoole\Server("0.0.0.0", 9502);

$server->on('workerStart', function($server, $id) {
    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);
    $server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $from_id, $data) {
});

$server->start();
```

## 对于连接已关闭的问题

`NOTICE    swFactoryProcess_finish (ERRNO 1004): send 165 byte failed, because connection[fd=10231] is closed`

`NOTICE    swFactoryProcess_finish (ERROR 1005): connection[fd=10231] does not exists`

以上的错误大家都见过吧 这是服务端响应时，客户端切断了连接。

场景：

- 对于浏览器疯狂刷新页面的用户 肯定是没加载完就刷掉了
- ab压测到一半 取消了压测
- wrk基于时间的压测

不要慌 镇定 上面的场景属于正常情况，但是其它情况 无缘无故的发送连接中断，赶紧去看看吧。

## connected 属性和连接状态不一致

对于 swoole4.x 之后的版本 connected这个属性 不会实时更新了，[isConnected](/Cn/Swoole/Client/method.html)方法也不可信了

原因：因为协程与同步阻塞编程模型一样，同步阻塞不会有实时更新连接的概念。    
注意：虽说以前的异步版本支持实时更新 `connected` 这个属性，但是捏，我偷偷告诉你啊，不可靠，哈哈哈哈。有可能连接会在你检查后就立马断开了！