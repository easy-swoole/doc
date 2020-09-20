---
title: easyswoole udp服务
meta:
  - name: description
    content: easyswoole udp服务
  - name: keywords
    content: easyswoole udp服务|swoole 硬件|swoole iot
---

# UDP

`UDP`为应用程序提供了一种无需建立连接就可以发送封装的`IP`数据包的方法。

`EasySwooleEvent`中[mainServerCreate](/FrameDesign/event.html#mainServerCreate)事件，进行子服务监听：

```php
public static function mainServerCreate(\EasySwoole\EasySwoole\Swoole\EventRegister $register)
{
    $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();

    $subPort = $server->addlistener('0.0.0.0', 9503, SWOOLE_UDP);
    $subPort->on($register::onPacket, function (\Swoole\Server $server, string $data, array $clientInfo) {
           $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server：' . $data);
    });
}
```





