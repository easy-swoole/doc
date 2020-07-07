---
title: easyswoole swoole-协程tcp/upd客户端
meta:
  - name: description
    content: easyswoole swoole-协程tcp/upd客户端
  - name: keywords
    content: easyswoole swoole-协程tcp/upd客户端|easyswoole|swoole|coroutine
---

## tcp/udp协程客户端
命名空间:`\Swoole\Coroutine\Client`,`\Co\Client`.  
`\Swoole\Coroutine\Client` 提供了`tcp/udp/unixSocket`协议的客户端封装.    
```php
<?php
go(function(){
    $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('127.0.0.1', 9501, 0.5))
    {
        echo "连接失败: {$client->errCode}\n";
    }
    $client->send("easyswoole\n");
    while(1){
        echo $client->recv();
    }
    $client->close();
});
```
::: warning
`\Swoole\Coroutine\Client`兼容[同步阻塞客户端](/Cn/Swoole/Client/introduction.md) 所有的属性,方法,配置.(不同方法将在下面额外说明)  
其他使用方法可查看同步阻塞客户端.  
:::

## connect()
连接服务端.  
方法原型:connect(string $host, int $port, float $timeout = 0.5): bool    
参数说明:
- $host  服务端host
- $port  服务端端口
- $timeout   超时时间
::: warning
    - 连接失败返回false. 
    - 超时之后,$client->errCode 为110. 
:::

## send()
发送数据.  
方法原型:send(string $data): bool    
参数说明:   
- $data 需要发送的数据.字符串类型或者二进制类型.    
::: warning
发送成功将返回发送成功的字符串.  
可能出现发送成功一半,然后连接关闭的情况,所以需要自己判断是否全部发送完成.   
:::

## recv()  
接收服务端发送的数据.  
方法原型:recv(float $timeout = -1): string
参数说明:  
- $timeout 接收超时时间.单位秒,可以小数.     
::: warning
    - -1代表永远不超时.  
    - 如果返回空字符串,代表连接关闭.  
:::

## close()
立即关闭连接.  
方法原型:close(): bool   
::: warning
调用后,将直接关闭连接,没有协程切换操作.
:::

## peek()
窥视缓冲区数据.  
方法原型:peek(int $length = 65535): string    
参数说明:   
- $length 长度 
::: warning
用于直接查看`socket`内核缓冲区数据,并不对缓冲区做任何操作,可以通过`recv`重新获取到这部分数据.  
当缓存区存在数据时,立即返回数据,不存在则返回.  
如果返回空字符串,代表连接关闭.  
:::

## set()
设置客户端参数.  
方法原型:set(array $settings): string  
参数说明:  
- $settings 参数数组.  
::: warning
具体参数配置可查看[同步阻塞客户端配置](/Cn/Swoole/Client/setting.md).   

:::

额外配置项:  
- timeout  总超时,包括连接,发送,接收超时
- connect_timeout   连接超时
- read_timeout   接收超时
- write_timeout   发送超时
