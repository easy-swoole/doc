---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 会话内容存档

企业需要使用会话内容存档应用 `secret` 所获取的 `accesstoken` 来调用。原文: [https://work.weixin.qq.com/api/doc/90000/90135/91614](https://work.weixin.qq.com/api/doc/90000/90135/91614)

## 获取会话内容存档开启成员列表

```php
<?php
$type = 1;

$work->msgAudit->getPermitUsers(string $type);
```

## 获取会话同意情况

### 单聊

```php
<?php
$info = [
    [
        "userid" => "XuJinSheng1",
        "exteranalopenid" => "wmeDKaCQAAGd9oGiQWxVsAKwV2HxNAAA1"
    ],
    [
        "userid" => "XuJinSheng2",
        "exteranalopenid" => "wmeDKaCQAAGd9oGiQWxVsAKwV2HxNAAA2"
    ],
    [
        "userid" => "XuJinSheng3",
        "exteranalopenid" => "wmeDKaCQAAGd9oGiQWxVsAKwV2HxNAAA3"
    ]
];

$work->msgAudit->getSingleAgreeStatus(array $info);
```

### 群聊

```php
<?php
$roomId = 'wrjc7bDwAASxc8tZvBErFE02BtPWyAAA';

$work->msgAudit->getRoomAgreeStatus(string $roomId);
```

## 获取会话内容存档内部群信息

```php
<?php
$roomId = 'wrjc7bDwAASxc8tZvBErFE02BtPWyAAA';

$work->msgAudit->getRoom(string $roomId);
```