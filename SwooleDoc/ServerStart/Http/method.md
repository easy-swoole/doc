---
title: easyswoole swoole-http服务端方法
meta:
  - name: description
    content: easyswoole swoole-http服务端方法
  - name: keywords
    content: easyswoole swoole-http服务端方法|easyswoole|swoole
---

## http Server方法
`http server`继承于`tcp Server`,大致方法和`server`一致,可查看[server方法](/Cn/Swoole/ServerStart/Tcp/method.md)    
以下方法为`http server`专属,或者在`http server`环境下有不同解释
### on
注册 `http server` 的回调函数
方法原型:on($eventName, callable $callback)  
   
#### 参数介绍
- $eventName 回调函数名称,忽略大小写
- $callback 回调函数,参数根据回调函数的不同而不同
::: warning
此方法和`server`方法一致,但是在`http server`中,不允许` onConnect/onReceive `事件注册.
`http server`需要新注册`onRequest`事件,用于处理http请求回调.    
```php
<?php
$server->on('Request', function(Swoole\Http\Request $request, Swoole\Http\Response $response) {
     $response->end("<h1>hello easyswoole</h1>");
});
```
`onRequest`需要提供 [$request](/Cn/Swoole/ServerStart/Http/request.md),[$response](/Cn/Swoole/ServerStart/Http/response.md) 对象进行回调,
::: 

### start
启动`http server`服务器
方法原型:start()  
