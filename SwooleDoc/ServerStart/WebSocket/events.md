---
title: easyswoole swoole-websocket回调事件
meta:
  - name: description
    content: easyswoole swoole-websocket回调事件
  - name: keywords
    content: easyswoole swoole-websocket回调事件|easyswoole|swoole
---

## 回调事件
`websocket server`继承于`htto server/tcp server`,大致回调事件和`server`一致,可查看[tcp回调事件](/Cn/Swoole/ServerStart/Tcp/events.md)/[http回调事件](/Cn/Swoole/ServerStart/Http/events.md)    
以下事件为`websocket server`专属,或者在`websocket server`环境下有不同解释   
### onHandShake
`websocket`建立连接后进行握手将调用此回调
回调事件原型:onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);  
参数说明:  
- $request  请求握手时的http请求数据
- $response  请求握手时,可以给http请求响应数据.

::: warning
- `websocket`基于`http`,在建立`websocket`之前,`websocket`会先发送一个`http`请求,所以我们可以再回调中获取到这个`http`请求的数据,并且给与`http`响应   
- 不注册`onHandShake`事件,底层将会自动进行握手.(所以可以不需要注册).
- 在该事件中,需要对此`http`连接的数据进行解析,最后响应`101`状态码以及拼接的`Sec-WebSocket-Accept`值,详细握手逻辑可查看[websocket](/Cn/NoobCourse/NetworkrPotocol/Tcp/websocket.md)  
- 注册该事件之后,将不会触发`onOpen`事件,但是可以通过`$server->defer`方法调用`onOpen`逻辑.  
:::
自定义握手实现例子:  
```php
<?php
$websocketServer = new Swoole\WebSocket\Server("0.0.0.0", 9501);
//客户端握手成功事件
$websocketServer->on('open', function (Swoole\WebSocket\Server $websocketServer, $request) {
    echo "{$request->fd} 已经握手成功.\n";
});
//自定义握手事件
$websocketServer->on('handshake', function (\swoole_http_request $request, \swoole_http_response $response) {
    //在这里,我们可以做拒绝握手的判断,比如缺少token,登陆状态失效等
    if (0) {
        $response->end();
        return false;
    }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    //这里需要将sec-websocket-key和那串固定的代码拼接,然后sha1+base64编码,再给返回回去
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(sha1(
        $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
        true
    ));

    $headers = [
        'Upgrade'               => 'websocket',
        'Connection'            => 'Upgrade',
        'Sec-WebSocket-Accept'  => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }
    
    $response->status(101);//握手成功必须响应101
    $response->end();
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

### onOpen
当客户端与服务器建立`websocket`连接并握手成功,则调用该回调.  
回调事件原型:onOpen(Swoole\Websocket\Server $server, Swoole\Http\Request $request);   
参数说明:
- $server  $websocketServer对象
- $request  保存了客户端握手时的http请求信息
::: warning
当注册了onOpen事件后,禁止注册`onHandShake`事件.    
:::
### onMessage
当服务器收到来自客户端的`websocket`数据帧时会回调此函数。
回调事件原型:onMessage(Swoole\Websocket\Server $server, Swoole\Websocket\Frame $frame)    
参数说明:  
- $server $websocketServer对象
- $frame 数据帧对象,包含了客户端的fd,发送的数据等,可查看[websocket其他](/Cn/Swoole/ServerStart/WebSocket/other.md)
::: warning
客户端发送的 `ping` 帧不会触发 `onMessage`事件,底层会自动回复 `pong` 包
:::
