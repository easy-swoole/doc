---
title: easyswoole\websocket服务
meta:
  - name: description
    content: easyswoole\websocket服务
  - name: keywords
    content: easyswoole websocket服务|swoole websocket|swoole即时通讯|swoole聊天室|php websocket|php聊天室
---
# WebSocket

`WebSocket`是一种在单个`TCP`连接上进行全双工通信的协议。`WebSocket`使得客户端和服务器之间的数据交换变得更加简单，允许服务端主动向客户端推送数据。在`WebSocket`中，浏览器和服务器只需要完成一次握手，两者之间就直接可以创建持久性的连接，并进行双向数据传输。

修改配置文件`MAIN_SERVER.SERVER_TYPE`为`EASYSWOOLE_WEB_SOCKET_SERVER`。

`EasySwooleEvent`中[mainServerCreate](/FrameDesign/event.html#mainServerCreate)事件进行回调注册：

```php
public static function mainServerCreate(\EasySwoole\EasySwoole\Swoole\EventRegister $register)
{
    $register->set($register::onOpen, function ($ws, $request) {
        var_dump($request->fd, $request->server);
        $ws->push($request->fd, "hello, welcome\n");
    });

    $register->set($register::onMessage, function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
        echo "Message: {$frame->data}\n";
        $server->push($frame->fd, "server: {$frame->data}");
    });

    $register->set($register::onClose, function ($ws, $fd) {
        echo "client-{$fd} is closed\n";
    });
}
```