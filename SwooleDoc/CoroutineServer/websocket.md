---
title: easyswoole swoole-协程websocket服务器
meta:
  - name: description
    content: easyswoole swoole-协程websocket服务器
  - name: keywords
    content: easyswoole swoole-协程websocket服务器|easyswoole|swoole|coroutine|
---

# Websocket 服务器

## 介绍

完全协程化的Websocket服务器，继承[Co\Http\Server](/Cn/Swoole/CoroutineServer/http.md)。

> 此章节在 v4.4.13 后可用。

## 方法

### upgrade

作用：发送 `WebSocket` 握手成功信息       
方法原型：upgrade(): bool;

### recv

作用：接收 `Websocket`消息     
方法原型：recv(double timeout = -1): Swoole\WebSocket\Frame | fasle | string;    
返回值：
- 成功收到消息，返回`Frame`对象。
- 失败返回 `false`
- 连接关闭返回字符串

### push

作用：发送 `Websocket` 数据    
方法原型：push(string|object $data, int $opcode = 1, bool $finish = true): bool;     
参数：
- $data 要发送的内容，若内容为`Swoole\WebSocket\Frame`对象，则其后续参数会被忽略。
- $opcode 指定发送数据的内容格式 WEBSOCKET_OPCODE_TEXT(默认值) | WEBSOCKET_OPCODE_BINARY
- $finish 是否发送完成

### close

作用：关闭`Websocket`连接  
方法原型：close(): bool;

## 示例代码

```php
<?php
\Swoole\Coroutine::create(function () {
    $server = new \Swoole\Coroutine\Http\Server('0.0.0.0', 9502, false);
    $server->handle('/ws', function ($request, Swoole\Http\Response $ws) {
        $ws->upgrade();
        while (true) {
            /** @var \Swoole\WebSocket\Frame $frame */
            $frame = $ws->recv();

            if (is_string($frame)) break;
            if ($frame === false) {
                echo swoole_last_error() . PHP_EOL;
                break;
            }

            $ws->push('Yes');
            $ws->push('EasySwoole非常棒');
        }
    });
    $server->handle('/', function ($request, Swoole\Http\Response $response) {
        $response->end(
            <<<EOF
                    <h1>EasySwoole</h1>
                    <head><meta charset="UTF-8"></head>
                    <script >
                    let websocket = new WebSocket('ws://127.0.0.1:9502/ws');
                    websocket.onopen = function (evt) {
                        console.log("成功连接");
                        websocket.send('你是EasySwoole吗？');
                    };
                    
                    websocket.onclose = function (evt) {
                        console.log("断开连接");
                    };
                    
                    websocket.onmessage = function (evt) {
                        console.log('收到信息: ' + evt.data);
                    };
                    
                    websocket.onerror = function (evt, e) {
                        console.log('连接出错：'+evt.data);
                    };
                    </script>
EOF

        );
    });
    $server->start();
});
```

## 代码流程

- `$ws->upgrade()` 向客户端发送握手
- `while(true)` 循环处理消息
- `$ws->recv()` 接收数据
- `$ws-push()` 向对端发送数据