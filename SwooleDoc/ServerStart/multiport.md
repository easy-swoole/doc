---
title: easyswoole swoole-多端口监听
meta:
  - name: description
    content: easyswoole swoole-多端口监听
  - name: keywords
    content: easyswoole swoole-多端口监听|easyswoole|swoole
---


## swoole多端口监听
swoole可以实现多端口监听,每个端口注册不一样的处理方式,例如注册9501端口处理tcp协议,9502处理websocket协议等等.  

### 注册新端口
通过`listen`方法进行注册多端口.  
```php
//返回port对象
$port1 = $server->listen("0.0.0.0", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("0.0.0.0", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("0.0.0.0", 9503, SWOOLE_SOCK_TCP|SWOOLE_SSL);
```

## 给不同端口配置不同的参数.
```php
//port1对象调用set方法配置
$port1->set([
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0,
    'package_max_length' => 800000,
]);
//$port3对象的配置
$port3->set([
    'open_eof_split' => true,
    'package_eof' => "\r\n",
    'ssl_cert_file' => '/www/easyswoole/ssl.cert',
    'ssl_key_file' => '/www/easyswoole/ssl.key',
]);
```

### 给不同的端口设置不同的回调
如果没配置相应的回调事件,将会继承主服务的回调配置

```php
//设置每个port的回调函数
$port1->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});

$port1->on('receive', function ($serv, $fd, $from_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});
//如果没配置onClose,将会继承主服务的onClose配置

$port2->on('packet', function ($serv, $data, $addr) {
    var_dump($data, $addr);
});

```

### websocket/http多端口监听 
需要注意的是,由于`Swoole\Http\Server` 和 `Swoole\WebSocket\Server`对象都是继承于`Swoole\Server`,  
如果主服务是`Swoole\Server`,则无法多端口监听`http/websocket`,如果有`http/websocket`+`tcp`多端口监听的需求,必须将主服务配置为`http/websocket`,然后使用`Swoole\Http\Server/Swoole\WebSocket\Server`去调用`listen`方法进行多端口监听.    
::: warning
同理,`Swoole\WebSocket\Server`继承于`Swoole\Http\Server`,如果有`websocket+http`的需求,主服务必须是`Swoole\WebSocket\Server`.  
但`websocket`事件和`http`事件不冲突,主服务器为`Swoole\WebSocket\Server`时,无需配置额外的子端口,直接注册`onRequest事件`即可直接使用http.  
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
    echo "{$frame->fd} 发送了数据:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $websocketServer->push($frame->fd, "this is server");
});
//客户端关闭事件
$websocketServer->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

//当浏览器发送http请求时,将会到这里回调
$websocketServer->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

    var_dump($request->get);//在终端打印get参数
    var_dump($request->post);//在终端打印post参数
    //发送header头,不能直接通过header函数发送
    $response->header("Content-Type", "text/html; charset=utf-8");
    //向浏览器响应数据
    $response->write("<h1>easyswoole</h1>");
    $response->write("<h1>easy学swoole</h1>");
    $response->write("<h1>你是第{$request->fd}个访问者</h1>");
    //结束最后的响应
    $response->end("<hr>");
});
//开启新的tcp子端口.用于处理tcp请求
$tcpPort = $websocketServer->listen('0.0.0.0',9502,SWOOLE_SOCK_TCP);
$tcpPort->set([
    'open_websocket_protocol' => false, //关闭这个tcp子端口的websocket协议解析,否则tcp客户端连接这个端口没办法正常收发tcp包
]);
//设置每个port的回调函数

//监听连接进入事件,当客户端连接成功时,会分配一个fd(自增id),然后会调用这个回调函数
$tcpPort->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件,当客户端发送数据到服务器时,会调用这个回调函数
$tcpPort->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件,当客户端关闭连接时,会调用这个回调函数
$tcpPort->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});


//开启websocket服务(包括http,tcp)
$websocketServer->start();

``` 

### tcp协议多协议端口复合设置
在上面我们可以看到,当开启`websocket服务`后,新监听的端口默认都是`websocket`服务器,需要通过`$port->set`进行关闭`websocket协议解析`,同样的配置有以下几种:  
- open_http_protocol  启用 `Http` 协议处理.
- open_websocket_protocol  启用`websocket`协议处理
- open_http2_protocol  启用`http2`协议处理 
- open_mqtt_protocol   启用`mqtt`协议处理 
::: warning
当你需要同时监听`websocket`+`http`时,`http`子端口需要配置`open_websocket_protocol=false,open_http_protocol=true`
:::



### 可选回调说明
如果子服务没有注册相应的回调事件,将会默认继承主服务的事件回调.  
子服务可以设置的回调函数有:  
#### TCP 服务器
- onConnect
- onClose
- onReceive
#### UDP 服务器
- onPacket
- onReceive
#### HTTP 服务器
- onRequest
#### WEBSOCKET 服务器
- onMessage
- onOpen
- onHandshake
- onClose (但是需要自己判断此链接是不是websocket链接)

### 获取不同端口的连接.  
通过主服务 `$server->ports`,可获取全部的监听端口数组.通过里面的数组,即可获取当前端口的连接.    
```php
$websocket = $server->ports[0];//0是主服务,还可以获取1,2,3子服务的数据
foreach ($websocket->connections as $fd) {
    var_dump($fd);
    if ($server->exist($fd)) {
        $server->push($fd, "this is server onReceive");
    }
}
```
