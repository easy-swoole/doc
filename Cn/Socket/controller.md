---
title: easyswoole socket控制器对象
meta:
  - name: description
    content: easyswoole/socket
  - name: keywords
    content: easyswoole socket|swoole tcp udp websocket
---

# Socket-Controller

## 创建

继承`EasySwoole\Socket\AbstractInterface\Controller`

```php
class Test extends \EasySwoole\Socket\AbstractInterface\Controller
{

}
```

## 自定义解析器

```php
class TestParser implements \EasySwoole\Socket\AbstractInterface\ParserInterface 
{
    public function decode($raw,$client) : ?\EasySwoole\Socket\Bean\Caller
    {
        
    }
    
    public function encode(\EasySwoole\Socket\Bean\Response $response,$client) : ?string
    {
        
    }
}
```

## 调度器注册

子服务举例

`EasySwooleEvent`中[mainServerCreate](/FrameDesign/event.html#mainServerCreate)事件进行回调注册：

```php
public static function mainServerCreate(\EasySwoole\EasySwoole\Swoole\EventRegister $register)
{
    $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();

    $subPort = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
    $subPort->set(
        // swoole 相关配置
    );

    $socketConfig = new \EasySwoole\Socket\Config();
    $socketConfig->setType($socketConfig::TCP);
    $socketConfig->setParser(new TestParser());
    //设置解析异常时的回调,默认将抛出异常到服务器
    $socketConfig->setOnExceptionHandler(function ($server, $throwable, $raw, $client, \EasySwoole\Socket\Bean\Response $response) {
        $response->setMessage("服务器异常（客户端fd:{$client->getFd()}）");
        $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE); // 发送完主动关闭该连接
    });
    $dispatch = new \EasySwoole\Socket\Dispatcher($socketConfig);

    $subPort->on($register::onConnect, function (\Swoole\Server $server, int $fd, int $reactor_id) {
            echo "fd {$fd} connected";
    });
    
    $subPort->on($register::onReceive, function (\Swoole\Server  $server, int $fd, int $reactor_id, string $data) use ($dispatch) {
        $dispatch->dispatch($server, $data, $fd, $reactor_id);
    });

    $subPort->on($register::onClose, function (\Swoole\Server  $server, int $fd, int $reactor_id) {
            echo "fd {$fd} closed";
    });
}
```

