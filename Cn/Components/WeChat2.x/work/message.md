---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 消息发送

## 主动发送消息

```php
<?php
use EasySwoole\WeChat\Kernel\Messages\TextCard;

// 获取 Messenger 实例
$messenger = $work->messenger;

// 准备消息
$message = new TextCard([
    'title' => '你的请假单审批通过',
    'description' => '单号：1928373, ....',
    'url' => 'http://easywechat.com/oa/....'
]);

// 发送
$messenger->message($message)->toUser('easyswoole')->send();
```

你也可以很方便地发送普通文本消息：

```php
<?php
$messenger->message('你的请假单（单号：1928373）已经审批通过！')->toUser('easyswoole')->send();

// 或者写成
$messenger->toUser('easyswoole')->send('你的请假单（单号：1928373）已经审批通过！');
```

## 接收消息

被动接收消息，与回复消息，请参考：[服务端](/Components/WeChat2.x/work/server.md)