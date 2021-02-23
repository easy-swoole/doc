---
title: swoole ini配置
meta:
  - name: description
    content: swoole ini配置
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|EasySwoole|swoole|swoole ini配置
---

### ini配置

|参数|默认值|说明|
|----|---- |----|
|swoole.enable_coroutine|On|开关内置协程|
|swoole.display_errors|On|开启或者关闭swoole的错误信息|
|swoole.use_shortname|On|是否启用短命名|
|swoole.enable_preemptive_scheduler|On|防止协程死循环占用CPU|
|swoole.enable_library|On|开启或者关闭扩展内置的library|
|swoole.socket_buffer_size|8M|设置进程间通信的Socket缓存区尺寸|