---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 基础接口

## 清理接口调用次数

此接口官方有每月调用限制，不可随意调用

```php
$officialAccount->base->clearQuota();
```

## 获取微信服务器 IP (或 IP 段)
```php
$officialAccount->base->getValidIps();
```