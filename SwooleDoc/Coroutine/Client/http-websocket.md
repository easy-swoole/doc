---
title: easyswoole swoole-协程http/websocket客户端
meta:
  - name: description
    content: easyswoole swoole-协程http/websocket客户端
  - name: keywords
    content: easyswoole swoole-协程http/websocket客户端|easyswoole|swoole|coroutine
---

## http/websocket客户端  
命名空间 `Swoole\Coroutine\Http\Client`.   
基础示例:  
```php
<?php
go(function (){
    $client = new \Swoole\Coroutine\Http\Client('127.0.0.1',9501);
    $client->setHeaders([
        'Host' => 'x.cn',
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 1]);
    $client->get('/');
    var_dump($client);
    $client->close();
});
```

## __construct()
构造方法.  
方法原型:__construct(string $host, int $port, bool $ssl = false);    
参数说明:  
- $host  目标服务器主机(ip,域名)  
- $port  目标端口
- $ssl   是否开启`ssl/tls`加密,也就是https服务器必须开启.   
```php
<?php
go(function (){
    $client = new \Swoole\Coroutine\Http\Client('www.baidu.com',443,true);
    $client->setHeaders([
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->set(['timeout' => 2]);
    $client->get('/');
    var_dump($client);
    $client->close();
});
```
## set()
设置客户端的参数.  
方法原型:set(array $options);    
参数说明:  
- $options  参数数组
::: warning
此客户端与[同步阻塞客户端配置](/Cn/Swoole/Client/setting.md) 参数完全一致,但是额外增加了一些选项,下面将说明.  
:::

### timeout 
请求超时.单位为秒,最小支持毫秒级别.请求超时后底层会自动切断连接.    
```php
$httpClient->set(['timeout'=>3.001]);
```
- 连接超时/服务器关闭连接,`statusCode属性`将设置为-1.  
- 服务器在超时时间内未返回响应数据,`statusCode属性`将设置为-2.
- 设置为`-1`表示永不超时.  

### keep_alive  
`启用/关闭`长连接.  
```php
$httpClient->set(['keep_alive' => false]);
```
### websocket_mask    
`关闭/开启`客户端掩码,>=v4.4.0默认开启,小于v4.4.0默认关闭.  
```php
$httpClient->set(['keep_alive' => false]);
```
::: warning
启用后会对 `WebSocket` 客户端发送的数据使用掩码进行数据转换.但是会导致一定的性能损耗.  
:::
### websocket_compression
只支持>=v4.4.12版本.  

开启后 允许对帧进行 `zlib` 压缩,具体是否能够压缩取决于服务端是否能够处理压缩(根据握手信息决定,参见`RFC-7692`) 需要配合 `flags` 参数 `SWOOLE_WEBSOCKET_FLAG_COMPRESS ` 来真正地对具体的某个帧进行压缩.

## setMethod()  
设置当前请求的请求方法.只生效一次,发送后将清除.  
方法原型:setMethod(string $method): void;    
参数说明:  
- $method  设置的方法.    
```php
$httpClient->set(['keep_alive' => false]);
```
::: warning
必须为符合 `Http` 标准的方法,如果设置错误可能会被`Http`服务器拒绝请求.   
:::
## setHeaders()
设置 `Http`请求头.  
方法原型:setHeaders(array $headers): void;    
参数说明:  
- $headers  键值对数组.  
```php
$client->setHeaders([
    'User-Agent' => 'Chrome/49.0.2587.3',
    'Accept' => 'text/html,application/xhtml+xml,application/xml',
    'Accept-Encoding' => 'gzip',
]);
```
::: warning
此方法设置之后永久生效,重新调用将覆盖上一次的配置.  
:::
## setCookies()
设置`cookie`,将会被`urlencode`编码.
方法原型:setCookies(array $cookies): void;   
参数说明:  
- $cookies  键值对数组
::: warning
设置之后,将会持续保存cookie.  
底层将自动把服务端 `response`的`setCookie`参数合并到`cookies`数组中,可通过`cookies属性`获得当前的`cookie`信息.  
如果继续调用`setCookies`,将丢弃之前设置的cookie.    

:::
## setData() 
设置请求的包体.  
方法原型:setData(string|array $data): void;    
参数说明:   
- $data  设置请求的包体
::: warning
如果设置了请求包体,并且没有设置`method`,底层将默认为`post`.  
如果$data为数组并且`Content-Type=urlencoded`,底层将自动执行`http_build_query`.  
如果使用了 `addFile` 或 `addData` 导致启用了 `form-data` 格式,$data如果为字符串,将会被忽略,为数组,将会追加数组中的字段.  
:::
## addFile()
添加post文件.  
方法原型:addFile(string $path, string $name,string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0): void;    
参数说明:  
- $path 文件路径.  
- $name  表单名称 
- $mimeType 文件的`mime`格式,底层会根据扩展名自动判断.  
- $filename 文件名称. 
- $offset  上传文件的偏移量.默认为文件头.    
- $length  发送的数据大小.默认为整个文件.

```php
<?php
go(function () {
    $client = new \Swoole\Coroutine\Http\Client('www.baidu.com', 443, true);
    $client->setHeaders([
    ]);
    $client->set(['timeout' => -1]);
    $client->addFile(__FILE__, 'file', 'text/plain');
    $client->post('/post', ['name' => 'tioncico']);
    var_dump($client);
    $client->close();
});

```
::: warning
此方法会自动将 `POST` 的 `Content-Type` 将变更为 `form-data`,`addFile` 底层基于 `sendfile`,支持超大文件异步发送.
:::
## addData()
上传字符串格式文件内容.>=v4.1.0  
方法原型:addData(string $data, string $name, string $mimeType = null, string $filename = null): void    
参数说明:  
- $data   数据内容,不能超过`buffer_output_size`,默认2M.  
- $name   表单的名称.  
- $mimeType   文件的`mime`格式,默认为`application/octet-stream`
- $filename  文件名称.
  
```php
<?php
go(function () {
    $client = new \Swoole\Coroutine\Http\Client('www.baidu.com', 443, true);
    $client->setHeaders([
    ]);
    $client->set(['timeout' => -1]);
    $client->addData(Co::readFile(__FILE__), 'file', 'text/plain');
    $client->post('/post', ['foo' => 'bar']);
    var_dump($client);
    $client->close();
});

```
## get()
发起一个`get`请求
方法原型:get(string $path): void  
参数说明:  
- $path get请求的路径,例如/index/a/index.html.  

```php
<?php
go(function () {
    $client = new \Swoole\Coroutine\Http\Client('www.baidu.com', 443, true);
    $client->setHeaders([
        'User-Agent' => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->get('/index.php');
    var_dump($client);
    $client->close();
});

```
## post()
发起 `POST` 请求.
方法原型:post(string $path, mixed $data): void    
参数说明:  
- $path  设置请求的路径.  
- $data  请求的包体数据

```php
<?php
go(function () {
    $client = new \Swoole\Coroutine\Http\Client('www.baidu.com', 443, true);
    $client->setHeaders([
        'User-Agent'      => 'Chrome/49.0.2587.3',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ]);
    $client->post('/post.php', ['a' => '123', 'b' => '456']);
    var_dump($client);
    $client->close();
});
```
::: warning
如果`$data`为数组底层自动会打包为 `x-www-form-urlencoded `格式的 `POST` 内容,并设置 `Content-Type` 为 `application/x-www-form-urlencoded`
使用`post`方法,会忽略`setMethod`方法设置的值,强制为`post`.      
:::
## upgrade()  
升级为 `WebSocket` 连接.  
方法原型:upgrade(string $path): bool  
参数说明:  
- $path 设置升级请求路径.  

```php
go(function () {
    $client = new \Swoole\Coroutine\Http\Client('127.0.0.1', 9701, false);
    $result = $client->upgrade('/');
    if ($result) {
        while (true) {
            $client->push("hello world");
            var_dump($client->recv());
            co::sleep(0.1);
        }
    }
});
```
::: warning
只有服务器返回101状态码,才代表`websocket`升级成功.    
upgrade 会产生一次协程调度.  
:::
## push() 
向已经`websocket`协议握手成功的服务器推送消息.  
方法原型:push(mixed $data, int $opCode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool    
参数说明:   
- $data 发送的数据.>= v4.2.0 $data 可以使用 Swoole\WebSocket\Frame 对象，支持发送各种帧类型.  
- $opCode 操作类型.  
- $finish 操作类型.  
::: warning
此方法会立即发送到缓冲区,并返回成功/失败,不存在协程调度.  

:::
## recv()  
接收已经`websocket`协议握手成功的服务器的消息.  
方法原型:recv(float $timeout = -1): void    
参数说明:  
- $timeout 接收的超时时间.  

## download()
通过`http协议`下载文件.  
方法原型:download(string $path, string $filename,  int $offset = 0): bool  
参数说明: 
- $path url路径
- $filename 下载的文件路径
- $offset 断点续传,当你文件下载完一半,继续请求时,可通过此参数直接偏移到一半位置,下载剩下的数据




## getCookies()
作用：获取http响应的cookie  
方法原型：getCookies(): array|false; 
::: warning
`cookie`信息将被`urldecode`解码   
获取原始`cookie`使用`var_dump($client->set_cookie_headers);`
:::


## getHeaders()
作用：获取http响应头信息  
方法原型：getHeaders(): array|false; 


## getStatusCode()
作用：获取http响应状态码  
方法原型：getStatusCode(): int|false;    

::: warning
状态码为负数，连接出现问题。  
-1 连接超时
-2 请求超时
-3 服务端强制切断连接
:::

## getBody()
作用：获取http响应内容   
方法原型：getBody(): string|false;


## close()
作用：关闭连接     
方法原型：close(): bool;


::: warning
close再次请求，Swoole会重新连接服务器。
:::

## execute()
作用：更底层的http请求方法，需调用`setMethod`和`setData`设置请求方法及数据。  
方法原型：execute(): bool;   
示例：
```php
<?php
Co\run(function(){
    $httpClient = new Swoole\Coroutine\Http\Client('www.easyswoole.com', 80);
    $httpClient->setMethod("GET");
    $status = $httpClient->execute("/");
    var_dump($status);
    var_dump($httpClient->getBody());
});
```