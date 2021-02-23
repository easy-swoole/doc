---
title: easyswoole swoole-同步阻塞服务端
meta:
  - name: description
    content: easyswoole swoole-同步阻塞服务端
  - name: keywords
    content: easyswoole swoole-同步阻塞服务端|easyswoole|swoole
---


## 非协程客户端
非协程客户端封装了`tcp/udp/unixSokcet`代码,可以在`php-fpm`环境下使用.   
::: warning
由于`协程客户端`大部分用法复制于非协程客户端,所以将在这里说明非协程客户端.   
:::


```php
<?php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("连接失败: {$client->errCode}\n");
}
while (1){
    $client->send("easyswoole\n");
    echo $client->recv();
}
$client->close();

```  
