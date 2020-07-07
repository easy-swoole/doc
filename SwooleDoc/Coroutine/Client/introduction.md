---
title: easyswoole swoole-协程客户端
meta:
  - name: description
    content: easyswoole swoole-协程客户端
  - name: keywords
    content: easyswoole swoole-协程客户端|easyswoole|swoole|coroutine
---

## 协程客户端
我们知道,在全协程环境下,如果是阻塞io的话,将会导致整个进程阻塞,从而并发降低.  
所以swoole提供了一系列的协程异步io客户端,在使用这些客户端时,将自动产生协程切换,从而不影响其他协程的运行,同时不会中断该进程.   
## 当前支持的协程客户端.  
- 协程 `TCP/UDP` 客户端
- 协程 `HTTP` 客户端
- 协程 `HTTP2` 客户端
- 协程 `PostgreSQL` 客户端
- 协程 `Socket` 客户端
- 协程 `Redis` 客户端,不建议使用,可使用[EasySwoole/Redis协程客户端](/Cn/Components/Redis/introduction.md) 
- 协程 `MySql` 客户端


## 超时配置
### 协程全局设置超时
通过`\Swoole\Coroutine::set()`方法可为全局协程客户端设置超时时间.   
```php
<?php
\Swoole\Coroutine::set([
    'socket_timeout' => 3,//socket读写超时时间 默认-1
    'socket_connect_timeout' => 1,//socket连接超时时间 默认1
    'socket_read_timeout' => 1,//socket读超时时间 默认-1
    'socket_write_timeout' => 1,//socket写超时时间 默认-1
]);

```
### 通过协程客户端设置超时
通过不同的协程客户端提供的`set`方法,也可以设置成超时时间.  
::: warning
协程客户端设置的超时优先级高于协程全局设置.  
:::
```php
<?php
$tcpClient = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);

$tcpClient->set([
    'timeout' => 0.5,//连接/发送/接收超时时间
    'connect_timeout' => 1.0,//连接超时,优先级>timeout
    'write_timeout' => 10.0,//发送超时,优先级>timeout
    'read_timeout' => 0.5,//接收超时,优先级>timeout
]);
//Socket 通过 setOption 配置
$socket = new \Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = ['sec'=>1, 'usec'=>500000];
$socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);//接受数据超时时间
$socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);//连接超时和发送数据超时的配置

```

::: warning
- http/http2/tcp客户端配置都一致
- sw原生Redis客户端没有`write_timeout/read_timeout` 配置
:::


### 直接通过执行方法传入超时时间
通过协程客户端执行方法中的参数传入超时时间.即可设置本次执行的超时时间,此方法超时时间优先级最高.  

```php
<?php

$client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);

$client->connect('127.0.0.1',9501,3);//最后一个参数为超时时间,也就是连接超时时间
$client->send('aaa');
$client->recv(1);//传入超时时间,也就是读取数据的超时时间
```



