---
title: easyswoole swoole-response对象
meta:
  - name: description
    content: easyswoole swoole-response对象
  - name: keywords
    content: easyswoole swoole-response对象|easyswoole|swoole
---

## response对象
命名空间:`Swoole\Http\Response`.   

`http`响应的对象,通过调用此对象的方法,给客户端响应数据.  

::: warning
当`response`对象销毁时未调用`end()`结束响应时,底层将自动调用`end('')`;
:::

## 方法

### header()
用于设置http头的响应头,设置失败时返回false.  
方法原型:header(string $key, string $value, bool $ucWords = true);  
参数介绍:  
- $key 响应头的key
- $value 响应头key的值
- $ucWords 是否对每个key第一个字母大写  
::: warning
此方法必须在`end`之前调用  
重复调用时,将会覆盖上一次的调用  
:::

### trailer()
仅在`http2`时有效,将`header`信息附加到`http`响应的结尾.  
方法原型:trailer(string $key, string $value, bool $ucWords = true);
参数介绍:  
- $key 响应头的key
- $value 响应头key的值
- $ucWords 是否对每个key第一个字母大写  

::: warning
重复调用时,将会覆盖上一次的调用  
:::

### cookie()
设置响应的cookie数据,此方法等于php的`setcookie`函数.    
方法原型:cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httpOnly = false, string $sameSite = '');
参数介绍:
此方法等同于`setcookie`
::: warning
- 此方法必须在`end`之前调用  
- 底层将自动对`$value`进行`urlencode`编码,可使用`rawCookie`方法关闭编码处理
- 此方法可以设置多个相同`$key`的`cookie`值
:::

### rawCookie()
此方法与cookie方法一致,唯一区别为此方法不做`urlencode`编码处理

### status()
发送`http`状态码
方法原型:status(int $httpStatusCode, int $reason=0): bool;  
参数介绍:  
- $httpStatusCode  设置http code
- $reason  是否可以任意设置code
::: warning
- 此方法必须在`end`之前调用  
- 正常调用时,只允许设置合法的`httpCode`,例如`404/200/501/500`等.   
- `$reason`为1时,`$httpStatusCode`可以随便设置,例如499.  

:::

### redirect()
发送`http`跳转头,调用此方法,将自动调用`end`,并结束响应(但代码并不会直接中断)  
方法原型:redirect(string $url, int $httpCode = 302): void;  
参数介绍:  
- $url 跳转的地址,作为`location`头进行发送
- $httpCode 跳转的状态码.302临时跳转,301永久跳转  

### write()
启用`Http Chunk`分段向客户端响应数据.  
方法原型:write(string $data): bool;  
参数介绍:  
- $data  需要发送的数据
::: warning
- 启用之后,将使用`chunk`协议分段发送数据.
- 启用之后,再调用`end`时,不能附带任何数据.    
:::

### sendfile()  
直接发送一个文件数据到客户端.  
方法原型:sendfile(string $filename, int $offset = 0, int $length = 0): bool;  
参数介绍:  
- $filename 要发送的文件路径
- $offset 发送时候的偏移量,比如指定文件中间开始发送
- $length  需要发送的大小,字节单位
::: warning
    - 需要自行指定`Content-Type`
    - 使用`sendfile`时不能使用`write`  
    - 调用`sendfile`后将自动调用`end`  
    - 此方法不支持`gzip`压缩
:::

### end()
发送 `Http` 响应体,并结束此次请求响应.
方法原型:end(string $data): bool;  
参数介绍:  
    - $data 需要响应的数据
    ::: warning
    - `end`只能调用一次,如果需要多次发送数据,请使用`write`
    - 如果客户端开启了`KeepAlive`,此次响应结束后并不会切断连接,否则将切断  
:::

### detach()  
使用该方法后,`$response`对象销毁后不会自动end,通过此方法,可以在`http`之外的进程对客户端响应  
方法原型:detach(): bool;
::: warning
当需要在`task`进程做响应时,可通过该方法实现
```php
<?php
$httpServer = new Swoole\Http\Server("0.0.0.0", 9501);

$httpServer->set(['task_worker_num' => 1, 'worker_num' => 1]);

$httpServer->on('request', function (\Swoole\Http\Request $request, Swoole\Http\Response $response) use ($httpServer) {
    $response->detach();
    $httpServer->task(['fd' => $response->fd,'data'=>'啦啦啦']);
    //这个回调结束之后,$response将销毁,本来是会调用end的,由于$response->detach(),则不调用
});

$httpServer->on('finish', function () {
});

$httpServer->on('task', function ($httpServer, $taskId, $workerId, $data) {
    var_dump($data);
    //在这里,重新创建一个response对象,用于响应数据
    $response = Swoole\Http\Response::create($data['fd']);
    $response->end("我在task进程给你发送了{$data['data']}");
    echo "异步任务\n";
});

$httpServer->start();
```
:::

### create()
静态方法,用于重新构建一个`response`对象.  
方法原型:create(int $fd): Swoole\Http\Response;  
参数介绍:
    - $fd 客户端标识
    ::: warning
    - 使用时,必须先调用`detach`方法,否则一个请求,可能出现2次响应.  
:::
