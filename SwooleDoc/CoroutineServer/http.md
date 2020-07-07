---
title: easyswoole swoole-协程http服务器
meta:
  - name: description
    content: easyswoole swoole-协程http服务器
  - name: keywords
    content: easyswoole swoole-协程http服务器|easyswoole|swoole|coroutine|
---

# HTTP服务器

## 介绍

`Swoole\Coroutine\Http` 用于创建协程`HTTP`服务器，继承自[Co\Server](/Cn/Swoole/CoroutineServer/http.md)。

> 可以在运行时动态地创建、销毁

> 对连接的处理是在单独的子协程中完成，客户端连接的 Connect、Request、Response、Close 是完全串行的

> 在4.4 以上版本中使用

> 编译时开启HTTP2，默认会启用 HTTP2 协议支持 (v4.4.16 以下版本 HTTP2 支持存在已知 BUG, 请升级后使用)

可使用`Co\Http\Server`短名。


## 方法

### handle

作用：重写父类的handle方法，处理http请求，必须在[start()](/Cn/Swoole/CoroutineServer/tcp.html#start)方法前调用       
方法原型：handle(string $pattern, callable $fn);
参数说明：
- $pattern 设置`url`路径 不能传`http://domain`
- $fn 回调函数 用法参考[OnRequest](/Cn/Swoole/ServerStart/Http/events.html#onRequest)

> 服务器在`accept`成功建立连接后，会自动创建协程，接受`http`请求   
 
> `$fn` 在子协程空间运行 无需再次创建协程   

> 客户端支持 `KeepAlive`，子协程会循环继续接受新的请求，不退出 
 
> 客户端不支持 `KeepAlive`，子协程会停止接受请求，退出并关闭连接  
   
> `$pattern`设置相同的路径，会覆盖旧设置  

> 未匹配到`$pattern`，返回404错误    

## 简单示例代码

```php
<?php
Swoole\Coroutine::create(function () {
    $server = new Swoole\Coroutine\Http\Server("127.0.0.1", 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Hello world!</h1>");
    });
    $server->handle('/easyswoole', function ($request, $response) {
        $response->end("<h1>EasySwoole</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
```
