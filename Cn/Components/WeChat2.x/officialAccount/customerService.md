---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 客服

使用客服系统可以向用户发送消息以及群发消息，客服的管理等功能。

## 客服管理

### 获取所有客服

```php
$officialAccount->customerService->list();
```

### 获取所有在线的客服

```php
$officialAccount->customerService->online();
```

### 添加客服

```php
$officialAccount->customerService->create('foo@test', '客服1');
```

### 修改客服

```php
$officialAccount->customerService->update('foo@test', '客服1');
```

### 删除账号

```php
$officialAccount->customerService->delete('foo@test');
```

### 设置客服头像

```php
// $avatarPath 为本地图片路径，非 URL
$officialAccount->customerService->setAvatar('foo@test', $avatarPath);
```

### 获取客服与客户聊天记录

```php
$officialAccount->customerService->messages($startTime, $endTime, $msgId = 1, $number = 10000);
```

使用示例:

```php
<?php

$records = $officialAccount->customerService->messages('2015-06-07', '2015-06-21', 1, 20000);
```

### 主动发送消息给用户

```php
$officialAccount->customerService->send(array $message);
```

$message 为数组，请参考：[消息](/Components/WeChat2.x/officialAccount/messages.md)

使用示例：

暂略。

### 邀请微信用户加入客服

以账号 `foo@test` 邀请 微信号 为 `xxxx` 的微信用户加入客服。

```php
$officialAccount->customerService->invite('foo@test', 'xxxx');
```

## 客服会话控制

### 创建会话

```php
$officialAccount->customerServiceSession->create('test1@test', 'OPENID');
```

### 关闭会话

```php
$officialAccount->customerServiceSession->close('test1@test', 'OPENID');
```

### 获取客户会话状态

```php
$officialAccount->customerServiceSession->get('OPENID');
```

###获取客服会话列表

```php
$officialAccount->customerServiceSession->list('test1@test');
```

### 获取未接入会话列表

```php
$officialAccount->customerServiceSession->waiting();
```