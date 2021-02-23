---
title: easyswoole swoole-进程池
meta:
  - name: description
    content: easyswoole swoole-process/pool
  - name: keywords
    content: easyswoole swoole-process/pool|easyswoole swoole-进程池|easyswoole|swoole|process/pool
---


# Process/Pool

## 介绍

进程池，基于`Manager`管理进模块实现。可管理多个进程。模块核心功能为进程管理。和`Process`对比，更加简单，封装层次更高。可以创建协程风格。

> `v4.4.0`版本中增加了对协程的支持。

## 方法

### __construct

作用：构造   
方法原型：__construct(int $workerNum, int $ipcType = 0, int $msgQueueKey = 0, bool $enableCoroutine = false)     
参数：
- $workerNum 工作进程数量
- $ipcType 进程通信方式 开启协程后需要进程通信的话，设置为`SWOOLE_IPC_UNIXSOCK`

|常量|说明|
|----|----|
|SWOOLE_IPC_MSGQUEUE|系统消息队列|
|SWOOLE_IPC_SOCKET|socket通信|
|SWOOLE_IPC_UNIXSOCK|unixSocket通信(v.4.4+)|

- $msgQueueKey 消息队列的key
- $enableCoroutine 是否开启协程 使用协程后将无法设置 `onMessage` 回调

### on

作用：设置进程池的回调函数   
方法原型：on(string $event, callable $function);     
参数：
- $event 指定事件
- $function 回调函数

### listen

作用：监听socket，`ipcType`为`SWOOLE_IPC_SOCKET`才能使用   
方法原型：listen(string $host, int $port = 0, int $backlog = 2048): bool;    
参数：
- $host 监听的地址 支持tcp和unixSocket
- $port 监听的端口 在tcp模式下指定
- $backlog 监听队列的长度

通讯协议：向监听端口发送数据时，客户端必须在请求前增加 4 字节、网络字节序的长度值。协议格式为：
```packet = htonl(strlen(data)) + data;```

### write

作用：向对端写入数据 `ipcType`为`SWOOLE_IPC_SOCKET`才能使用  
方法原型：write(string $data): bool;     
参数：
- $data 写入的数据 

> 此方法为内存操作，没有 IO 消耗，发送数据操作是同步阻塞 IO

### start

作用：启动工作进程   
方法原型：start(): bool;

### getProcess

作用：获取当前工作进程对象      
方法原型：getProcess(int $workerId): Swoole\Process;     
参数：
- $workerId 指定获取 `worker` 可选参数 默认为当前`worker`


## 简单示例代码

```php
<?php
$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);

$pool->on("Message", function ($pool, $message) {
    echo "Message: {$message}\n";
    $pool->write("hello ");
    $pool->write("world!");
});

$pool->listen('unix:/tmp/easyswoole.sock');
$pool->start();
```

```php
<?php
$client = stream_socket_client("unix:///tmp/easyswoole.sock", $errno, $errstr) or die("error: $errstr\n");
$msg = json_encode(['user' => 'easyswoole', 'data' => 'hello']);
fwrite($client, pack('N', strlen($msg)) . $msg);
$data = fread($client,65535);

$length = unpack('N',$data)[1];

var_dump(substr($data,4,$length));
```