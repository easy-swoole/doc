---
title: easyswoole swoole-websocket启动参数配置
meta:
  - name: description
    content: easyswoole swoole-websocket启动参数配置
  - name: keywords
    content: easyswoole swoole-websocket启动参数配置|easyswoole|swoole
---

## 启动参数配置

### websocket_subprotocol
说明:设置websocket的子协议
默认值:null
补充说明:  
设置后,握手响应头会增加额外的数据`Sec-WebSocket-Protocol: $websocket_subprotocol`
```php
$server->set([
    'websocket_subprotocol' => 'test',
]);
```
::: warning
具体使用方法可自行百度查看相关文档.  
:::

### open_websocket_close_frame
说明:启用后,`websocket`数据包的关闭帧(opcode=0x08)将会在`onMessage`中接收.
默认值:false
补充说明:开启后,在`onMessage`回调事件中,可以自行处理该关闭帧.  

### websocket_compression
说明:是否启用数据压缩
默认值:false