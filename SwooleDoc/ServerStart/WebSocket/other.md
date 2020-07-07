---
title: easyswoole swoole-websocket其他
meta:
  - name: description
    content: easyswoole swoole-websocket其他
  - name: keywords
    content: easyswoole swoole-websocket其他|easyswoole|swoole
---

# 其他
## Swoole\WebSocket\Frame对象
在v4.2.0后,`websocket`发送,接收都新增了`frame`对象支持.  
属性:  
- fd 客户端标识id
- data 发送的数据,文本或二进制
- opcode opcode类型
- finish  表示数据帧是否完整
::: warning
在`v4.4.12` 版本新增了`flags`属性支持 `WebSocket` 压缩帧.并且新增了`Swoole\WebSocket\CloseFrame` 对象
:::

##  Swoole\WebSocket\CloseFrame
需要在配置中开启`open_websocket_close_frame`参数.  

属性:  
- fd 客户端标识id
- data 发送的数据,文本或二进制
- opcode opcode类型
- finish  表示数据帧是否完整
- code  
- reason  

## websocket相关常量

### 数据帧类型
- 常量:WEBSOCKET_OPCODE_TEXT,值:0x1,说明:UTF-8 文本字符串数据
- 常量:WEBSOCKET_OPCODE_BINARY,值:0x2,说明:二进制数据
- 常量:WEBSOCKET_OPCODE_PING,值:0x3,说明:ping数据帧  

### 连接状态 
- 常量:WEBSOCKET_STATUS_CONNECTION,值:1,说明:连接进入等待握手.    
- 常量:WEBSOCKET_STATUS_HANDSHAKE,值:2,说明:正在握手.    
- 常量:WEBSOCKET_STATUS_FRAME,值:3,说明:握手成功.  

