---
title: easyswoole swoole-协程tcp服务器
meta:
  - name: description
    content: easyswoole swoole-协程tcp服务器
  - name: keywords
    content: easyswoole swoole-协程tcp服务器|easyswoole|swoole|coroutine|
---

# TCP服务器

## 介绍

`Swoole\Coroutine\Server` 用于创建协程`TCP`服务器，是完全[协程](/Cn/Swoole/Coroutine/introduction.md)化的类。

> 在4.4 以上版本中使用

可使用`Co\Server`短名。

## 方法

### __construct

作用：构造方法     
方法原型：__construct(string $host, int $port = 0, bool $ssl = false, bool $reusePort);  
参数说明：
- $host 监听的地址 支持格式（`IPv4`,`IPv6`,`UnixSocket`）
- $port 监听的端口（为0将随机分配）
- $ssl 是否开启ssl加密
- $reusePort 是否开启端口重用

### set

作用：设置协议处理参数 必须在[start()](/Cn/Swoole/CoroutineServer/tcp.html#start)方法前调用        
方法原型：set(array $option)
参数说明：
- $option [配置选项](/Cn/Swoole/ServerStart/Tcp/serverSetting.html)


### handle

作用：设置连接处理函数 必须在[start()](/Cn/Swoole/CoroutineServer/tcp.html#start)方法前调用     
方法原型：handle(callbale $fn);
参数说明：
- $fn 回调函数

> 服务器在`accept`成功建立连接后，自动创建协程调用`$fn`;    
> `$fn` 在子协程空间运行 无需再次创建协程   
> `$fn` 接受一个参数 为`Swoole\Coroutine\Server\Connection`对象  
> `Swoole\Coroutine\Server\Connection`提供三个方法：   
> - `recv()` 接收数据
> - `send($data)` 发送数据
> - `close()` 关闭连接


- 回调函数内 可获取 socket 属性 参考[协程客户端socket](/Cn/Swoole/Coroutine/Client/socket.md)

`$conn->socket`

### shutdown

作用：终止服务器    
方法原型：shutdown(): bool;

### start

作用：启动服务器    
方法原型：start(): bool;

### 简单示例代码

```php
<?php
Swoole\Coroutine::create(function () {
    $server = new Swoole\Coroutine\Server('127.0.0.1', '9501', false, true);
    
    // 处理请求
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        $data = $conn->recv();
        var_dump($data);
        $conn->send("easyswoole牛逼");
        $conn->close();

    });
    //开始监听端口
    $server->start();
});
```