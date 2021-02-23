---
title: easyswoole swoole-udp服务简单运行
meta:
  - name: description
    content: easyswoole swoole-udp服务简单运行
  - name: keywords
    content: easyswoole swoole-udp服务简单运行|easyswoole|swoole
---

## udp服务简单运行
新增udp.php文件,通过以下代码即可创建一个简单的udp服务器:

```php
<?php
$server = new Swoole\Server("0.0.0.0", 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

//监听数据接收事件
$server->on('Packet', function ($server, $data, $clientInfo) {
    echo "udp客户端发送了数据:{$data}\n";
    $server->sendto($clientInfo['address'], $clientInfo['port'], "服务器回复:{$data}");
    var_dump($clientInfo);
});
echo "服务器启动成功\n";
//启动服务器
$server->start();
```

执行命令:  
```
php udp.php
```

这个时候已经正常启动了一个udp服务,在下个章节我们会讲到如何测试
