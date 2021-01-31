---
title: easyswoole/tcp服务器
meta:
  - name: description
    content: easyswoole/tcp服务器
  - name: keywords
    content: easyswoole tcp服务器|swoole tcp
---

# TCP

`EasySwoole`创建`TCP`服务器，有两种以下方式：

## 主服务

修改配置文件`MAIN_SERVER.SERVER_TYPE`为`EASYSWOOLE_SERVER`。

`EasySwooleEvent`中[mainServerCreate](/FrameDesign/event.html#mainServerCreate)事件进行回调注册：

```php
public static function mainServerCreate(\EasySwoole\EasySwoole\Swoole\EventRegister $register)
{
    $register->add($register::onConnect, function (\Swoole\Server $server, int $fd, int $reactor_id) {
        echo "fd {$fd} connected";
    });

    $register->add($register::onReceive, function (\Swoole\Server  $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} send:{$data}\n";
    });

    $register->add($register::onClose, function (\Swoole\Server  $server, int $fd, int $reactor_id) {
            echo "fd {$fd} closed";
    });
}
```

## 子服务

顾名思义：另开一个端口进行`tcp`监听。

`EasySwooleEvent`中[mainServerCreate](/FrameDesign/event.html#mainServerCreate)事件，进行子服务监听：

```php
public static function mainServerCreate(\EasySwoole\EasySwoole\Swoole\EventRegister $register)
{
    $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();

    $subPort = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
    $subPort->set([
        // swoole 相关配置
        'open_length_check' => false,
    ]);
    $subPort->on($register::onConnect, function (\Swoole\Server $server, int $fd, int $reactor_id) {
            echo "fd {$fd} connected";
    });
    
    $subPort->on($register::onReceive, function (\Swoole\Server  $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} send:{$data}\n";
    });

    $subPort->on($register::onClose, function (\Swoole\Server  $server, int $fd, int $reactor_id) {
            echo "fd {$fd} closed";
    });
}
```