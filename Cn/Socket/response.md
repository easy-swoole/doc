---
title: easyswoole socket响应对象
meta:
  - name: description
    content: easyswoole/socket
  - name: keywords
    content: easyswoole socket|swoole tcp udp websocket
---

# Socket-Response

## 响应状态分析

`EasySwoole\Socket\Bean\Response`

此响应类主要用于此调度结束或者调度出现异常，对连接后续的操作。

正常响应(保持连接，服务端不主动关闭)(Response默认响应状态)

> \EasySwoole\Socket\Bean\Response::STATUS_OK;

响应后服务端主动关闭连接

> \EasySwoole\Socket\Bean\Response::STATUS_RESPONSE_AND_CLOSE;

服务端直接关闭连接
> \EasySwoole\Socket\Bean\Response::STATUS_CLOSE;

## 设置响应信息

响应信息会经过解析器的`encode`。

```php
class Test extends \EasySwoole\Socket\AbstractInterface\Controller
{
    public function testMessage()
    {   
        $this->response()->setMessage('test message');
    }
}
```

## 设置响应状态

当响应信息为空的时候，并不会发送给客户端信息。

### 异常

自定义了异常处理器，可进行响应状态控制:

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
// 伪代码 需要开发者在mainServerCreate中进行注册，或者脱离主框架使用
$conf = new \EasySwoole\Socket\Config();
$conf->setType($conf::TCP);
$conf->setParser(new TestParser());
$conf->setMaxPoolNum(2);
$conf->setOnExceptionHandler(function (\swoole_server $server,\Throwable $throwable,string $raw,$client,\EasySwoole\Socket\Bean\Response $response){
    $response->setMessage('system error');
    $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
});
```

### 控制器内方法

```php
class Test extends \EasySwoole\Socket\AbstractInterface\Controller
{
    public function testStatus()
    {   
        $this->response()->setMessage('test status');
        $this->response()->setStatus($this->response()::STATUS_RESPONSE_OK);
    }
}
```