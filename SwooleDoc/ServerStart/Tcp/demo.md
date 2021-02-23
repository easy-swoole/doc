---
title: easyswoole swoole-tcp服务简单运行
meta:
  - name: description
    content: easyswoole swoole-tcp服务简单运行
  - name: keywords
    content: easyswoole swoole-tcp服务简单运行|easyswoole|swoole
---

## tcp服务简单运行
新增tcp.php文件,通过以下代码即可创建一个简单的tcp服务器:

```php
<?php

//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 9501);

//监听连接进入事件,当客户端连接成功时,会分配一个fd(自增id),然后会调用这个回调函数
$server->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件,当客户端发送数据到服务器时,会调用这个回调函数
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件,当客户端关闭连接时,会调用这个回调函数
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});

echo "服务器启动成功\n";

//启动服务器
$server->start(); 
```

执行命令:  
```
php tcp.php
```

这个时候已经正常启动了一个tcp服务,在下个章节我们会讲到如何测试
