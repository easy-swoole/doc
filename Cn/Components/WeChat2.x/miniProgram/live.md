---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 直播

微信文档：[https://developers.weixin.qq.com/miniprogram/dev/framework/liveplayer/live-player-plugin.html](https://developers.weixin.qq.com/miniprogram/dev/framework/liveplayer/live-player-plugin.html)

> 微信规定以下两个接口调用限制共享 `500` 次/天 建议开发者自己做缓存，合理分配调用频次。

## 获取直播房间列表

```php
<?php
$miniProgram->live->getRooms();
```

## 获取回放源视频

```php
<?php
$roomId = 1; // 直播房间id

$miniProgram->live->getPlaybacks($roomId);
```