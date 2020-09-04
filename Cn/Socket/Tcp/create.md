---
title: easyswoole tcp服务
meta:
  - name: description
    content: easySwoole tcp服务
  - name: keywords
    content: easySwoole tcp服务|swoole tcp服务|php tcp服务|swoole 硬件|swoole iot
---

# EasySwoole Tcp服务

利用EasySwoole实现几行代码编写一个简单的TCP服务以及TCP客户端

## 服务的创建

### 主服务模式

我们可以直接在配置文件中，将Swoole的启动类型声明为`EASYSWOOLE_SERVER`。

然后在`EasySwooleEvent.php`框架事件文件中`mainServerCreate`方法，进行TCP相关的回调注册

```php
public static function mainServerCreate(EventRegister $register)
{
    $register->add($register::onReceive, function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} 发送消息:{$data}\n";
    });
}
```
### 子服务模式
单独端口开启TCP服务器，需要添加子服务。

通过`EasySwooleEvent.php`文件的`mainServerCreate` 事件,进行子服务监听,例如:

````php
<?php
public static function mainServerCreate(EventRegister $register)
{
    $server = ServerManager::getInstance()->getSwooleServer();

    $subPort1 = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
    $subPort1->set(
        [
            'open_length_check' => false, //不验证数据包
        ]
    );
    $subPort1->on('connect', function (\swoole_server $server, int $fd, int $reactor_id) {
        echo "fd:{$fd} 已连接\n";
        $str = '恭喜你连接成功';
        $server->send($fd, $str);
    });
    $subPort1->on('close', function (\swoole_server $server, int $fd, int $reactor_id) {
        echo "fd:{$fd} 已关闭\n";
    });
    $subPort1->on('receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} 发送消息:{$data}\n";
    });
}
````