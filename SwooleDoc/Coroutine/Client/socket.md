---
title: easyswoole swoole-协程socket客户端
meta:
  - name: description
    content: easyswoole swoole-协程socket客户端
  - name: keywords
    content: easyswoole swoole-协程socket客户端|easyswoole|swoole|coroutine
---

# socket客户端

## 介绍
`Swoole\Coroutine\Socket`可以实现更细粒度的`io`操作。   
可用`Co\Socket`短命名来简化类名。  
`Swoole\Coroutine\Socket`提供的`io`操作是同步编程风格，底层自动使用协程调度器来实现异步`io`。

## 属性

### fd
socket对应的文件描述符。

### errCode
返回的错误码

## 方法

### __controller
作用：构建`Co\Socket`对象。     
方法原型：__construct(int $domain, int $type, int $protocol);    
参数说明：
- $domain 协议域（`AF_INET`、`AF_UNIX`、`AF_INET6`）   
- $type 类型（`SOCK_STREAM`,`SOCK_RAW`,`SOCK_DGRAM`）   
- $protocol 协议（`IPPROTO_TCP`、`IPPROTO_UDP`、`IPPROTO_STCP`、`IPPROTO_TIPC`，`0`）   

### setOption
作用：设置配置     
方法原型：setOption(int $level, int $optName, mixed $optVal): bool;  
参数说明：
- $level 指定协议级别
- $optName 套接字选项，参考[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)
- $optVal 选项的值 根据 `level` 和 `optname` 决定


### getOption
作用：获取配置。    
方法原型：getOption(int $level, int $optName): mixed;    
参数说明：
- $level 指定协议级别
- $optName 套接字选项，参考[socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)


### setProtocol
作用：让`socket`有协议处理能力     
方法原型：setProtocol(array $settings): bool;        
参数说明：
- $settings [配置选项](/Cn/Swoole/ServerStart/Tcp/serverSetting.html)

### bind
作用：绑定地址和端口  
方法原型：bind(string $address, int $port = 0): bool;
参数说明：
- $address 绑定的地址
- $port 绑定的端口号 默认为0 系统会随便绑定一个可用端口

### listen
作用：监听socket     
方法原型：listen(int $backlog = 0): bool;
参数：
- $backlog 监听队列的长度 默认为0 系统使用epoll实现了异步io 不会阻塞

### accept
作用：接受客户端发起的连接       
方法原型：accept(float $timeout = -1): Coroutine\Socket|false;   
参数说明：
- $timeout 设置超时 默认为-1 为永不超时   

### connect
作用：连接到目标服务器     
方法原型：connect(string $host, int $port = 0, float $timeout = -1): bool;   
参数说明：
- $host 目标服务器地址（可传入ip地址 unix socket 路径 域名）
- $port 目标服务器端口
- $timeout 超时时间


### checkLiveness
作用：检查连接是否存活     
方法原型：checkLiveness(): bool;

### send
作用：向对端发送数据      
方法原型：send(string $data, float $timeout = -1): int|false;
参数说明：
- $data 要发送的数据
- $timeout 设置超时时间


### sendAll
作用：发送数据 与`send()` 不同的是 `sendAll()` 尽可能发送完整的数据 直到成功发送或遇到错误中止     
方法原型：sendAll(string $data, float $timeout = -1): int|false;
参数说明：
- $data 要发送的数据
- $timeout 设置超时时间

### peek
作用：窥视读缓冲区数据     
方法原型：peek(int $length = 65535): string|false;   
参数说明：
- int $length 拷贝窥视的数据内存大小

### recv
作用：接收数据     
方法原型：recv(int $length = 65535, float $timeout = -1): string|false;      
参数说明：
- $length 接收数据的内存大小
- $timeout 设置超时时间   

### recvAll
作用：接收数据 与`recv()` 不同的是 `recvAll()` 尽可能接收完整的数据 直到接收成功或遇到错误中止     
方法原型：recvAll(int $length = 65535, float $timeout = -1): string|false;      
参数说明：
- $length 接收数据的内存大小
- $timeout 设置超时时间   

### recvPacket
作用：通过 setProtocol 方法设置协议的 Socket 对象，调用此方法接收一个完整的协议数据包   
方法原型：recvPacket(float $timeout = -1): string|false;
参数说明：
- $timeout 指定超时时间

### sendto
作用：向指定地址和端口发送数据 `SOCK_DGRAM`类型的socket       
方法原型：sendto(string $address, int $port, string $data): int|false;
参数说明：
- $address 目标ip或unixSocket路径
- $port 目标端口（广播可以为0）


### recvfrom
作用：接收数据 `SOCK_DGRAM`类型的socket   
方法原型：recvfrom(array &$peer, float $timeout = -1): string|false;
参数说明：
- $peer 对端地址和端口 调用成功会返回数组（包括address和port两个元素）
- $timeout 设置超时时间

代码：
```php
<?php
Swoole\Coroutine::create(function () {
    $socket = new Co\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    $peer = null;
    $data = $socket->recvfrom($peer);
    echo "来源 地址{$peer['address']} 端口{$peer['port']} : $data\n";
    $socket->sendto($peer['address'], $peer['port'], "我收到了你发来的: $data");
});
Swoole\Coroutine::create(function () {
    $socket = new Co\Socket(AF_INET, SOCK_DGRAM);
    $socket->sendto('127.0.0.1', 9601, 111);
    $peer = null;
    $data = $socket->recvfrom($peer);
    var_dump($peer);
    var_dump($data);
});
```

### getsockname
作用：获取socket的地址和端口   
方法原型：getsockname(): array|false;

### getpeername
作用：获取socket对端的地址和端口 仅用于`SOCK_STREAM`类型  
方法原型：getpeername(): array|false;

### close
作用：关闭socket     
方法原型：close(): bool;


### 简单示例代码
```php
<?php
Swoole\Coroutine::create(function () {
    $socket = new Co\Socket(AF_INET, SOCK_STREAM, 0);
    $socket->bind('0.0.0.0', 9601);
    $socket->listen();
    $client = $socket->accept();
    var_dump($client->recv());
    $client->send('哈哈哈哈哈');
});
?>
<?php
Swoole\Coroutine::create(function () {
    $socket = new Co\Socket(AF_INET, SOCK_STREAM, 0);
    $socket->connect('127.0.0.1', 9601);
    $socket->send("hello easyswoole");

    $data = $socket->recv();
    var_dump($data);

    if (empty($data)) {
        $socket->close();
    }
});
```