---
title: easyswoole swoole-http回调事件
meta:
  - name: description
    content: easyswoole swoole-http回调事件
  - name: keywords
    content: easyswoole swoole-http回调事件|easyswoole|swoole
---

## http回调事件
`http server`继承于`tcp server`,大致回调事件和`server`一致,可查看[回调事件](/Cn/Swoole/ServerStart/Tcp/events.md)    
以下事件为`http server`专属,或者在`http server`环境下有不同解释   

### onRequest
事件原型:function(Swoole\Http\Request $request, Swoole\Http\Response $response);  
参数介绍:  
- $request [$request对象](/Cn/Swoole/ServerStart/Http/request.md),保存客户端发送的数据
- $response [$response对象](/Cn/Swoole/ServerStart/Http/response.md),用于响应客户端数据

::: warning
在收到一个http请求后,将回调此函数  
当`onRequest`调用完毕时,将销毁`$request/$response`对象  
:::