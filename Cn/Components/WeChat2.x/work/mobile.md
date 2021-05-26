---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 移动端

## 通过 `code` 获取用户信息

通过 `iOS` 或 `Android` 应用授权登录，获取一次性 `code`，通过后端服务器换取用户的信息。

```php
<?php
$code = 'CODE';

$work->mobile->getUser(string $code);
```