---
title: easyswoole swoole-websocket方法
meta:
  - name: description
    content: easyswoole swoole-websocket方法
  - name: keywords
    content: easyswoole swoole-websocket方法|easyswoole|swoole
---

## websocket方法
`websocket server`继承于`http Server`,大致方法和`http server`一致(同时,`http server`又继承于`server`),可查看[http方法](/Cn/Swoole/ServerStart/Http/method.md)    
以下方法为`websocket server`专属,或者在`websocket server`环境下有不同解释
### push
向 `websocket` 客户端推送数据,最大不得超过 `2M`.
方法原型:push(int $fd, string $data/Swoole\WebSocket\Frame $frame, int $opcode = 1, bool $finish = true): bool;  
参数介绍:  
- $fd  客户端标识id
- $data/$frame  需要发送的数据/也可传入Swoole\WebSocket\Frame对象,当传入对象时,后面的参数直接无效.
- $opcode  发送的数据内容格式`WEBSOCKET_OPCODE_TEXT(文本)/WEBSOCKET_OPCODE_BINARY(二进制)`
- $finish  是否发送完成,默认true

### exist
判断 `webSocket` 客户端是否存在.  
方法原型:exist(int $fd): bool;
参数介绍:
- $fd 客户端标识id
::: warning
连接存在并已完成 `WebSocket` 握手时将返回 `true`,否则为`false`.
:::
### pack
打包`websocket`消息,打包之后,可直接通过`send`发送此消息.  
方法原型:pack(string $data, int $opCode = 1, bool $finish = true, bool $mask = false): string;  
参数介绍:  
- $data 消息内容
- $opCode 指定发送数据内容的格式`WEBSOCKET_OPCODE_TEXT(文本)/WEBSOCKET_OPCODE_BINARY(二进制)`
- $finish 帧是否完成
- $mask 是否设置掩码
::: warning
websocket的消息,是非粘包性的,原因是websocket每一条消息,都有消息帧代表发送完成.    
::: 
```php
<?php
$websocketServer = new Swoole\WebSocket\Server("0.0.0.0", 9501);
//客户端握手成功事件
$websocketServer->on('open', function (Swoole\WebSocket\Server $websocketServer, $request) {
    echo "{$request->fd} 已经握手成功.\n";
});
//客户端发送消息事件
$websocketServer->on('message', function (Swoole\WebSocket\Server $websocketServer, $frame) {
    //将数据pack,pack之后可以直接使用send进行响应
    $data = $websocketServer::pack('test2');
    var_dump($data);//打印出的数据为�test2(问号因为是二进制封包,所以显示不全,可以自行测试)
    $websocketServer->send($frame->fd,$data);
});
//客户端关闭事件
$websocketServer->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
//开启websocket服务
$websocketServer->start();
```

### unpack
将`websocket`请求的数据包解析.   
方法原型:unpack(string $data): Swoole\WebSocket\Frame|false;  
参数介绍:  
- $data 参数内容
::: warning
同 `pack` 函数,通过该方法,直接可以使用`tcp server`接收`websocket`客户端数据,并解析.   
:::
### disconnect
主动关闭`websocket`客户端.  
方法原型:disconnect(int $fd, int $code = 1000, string $reason = ""): bool;  
参数介绍:   
- $fd 客户端标识id
- $code 关闭连接的状态码(需要根据`RFC6455`规范,取值为1000/4000-4999)
- $reason 关闭连接的原因,字节最大为125

### isEstablished
检查连接是否为有效的 `WebSocket` 客户端连接.  
方法原型:isEstablished(int $fd): bool;  
参数介绍:  
- $fd 客户端标识id
::: warning
此方法将判断该客户端是否连接成功,并且已经通过了`websocket`握手.  

:::
