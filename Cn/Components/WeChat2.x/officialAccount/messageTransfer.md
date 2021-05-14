---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 多客服消息转发

多客服的消息转发绝对是超级的简单，转发的消息类型为 `transfer`：

```php
<?php

$server = $officialAccount->server;

// 转发收到的消息给客服
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    return new \EasySwoole\WeChat\Kernel\Messages\Transfer();
});

/** @var \Psr\Http\Message\ServerRequestInterface $psr7Request */
// 在 EasySwoole 中
$psr7Request = $this->request();
# $psr7Request = new XxxReuest($request); // 伪代码 （在原生 Swoole 中）

$replyResponse = $server->forceValidate()->serve($psr7Request);
```

当然，你也可以指定转发给某一个客服：

```php
<?php

$server = $officialAccount->server;

// 转发收到的消息给客服
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    return new \EasySwoole\WeChat\Kernel\Messages\Transfer($account);
});

// ... 这里省略
```