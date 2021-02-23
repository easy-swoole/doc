---
title: easyswoole swoole-websocket简单运行
meta:
  - name: description
    content: easyswoole swoole-websocket简单运行
  - name: keywords
    content: easyswoole swoole-websocket简单运行|easyswoole|swoole
---

## 简单运行
新增`websocket.php`文件.  
```php
<?php
$websocketServer = new Swoole\WebSocket\Server("0.0.0.0", 9501);
//客户端握手成功事件
$websocketServer->on('open', function (Swoole\WebSocket\Server $websocketServer, $request) {
    echo "{$request->fd} 已经握手成功.\n";
});
//客户端发送消息事件
$websocketServer->on('message', function (Swoole\WebSocket\Server $websocketServer, $frame) {
    echo "{$frame->fd} 发送了数据:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $websocketServer->push($frame->fd, "this is server");
});
//客户端关闭事件
$websocketServer->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
//开启websocket服务
$websocketServer->start();
```
