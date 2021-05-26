---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# URL Scheme

微信文档：[https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/url-scheme/urlscheme.generate.html](https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/url-scheme/urlscheme.generate.html)

> 目前仅针对国内非个人主体的小程序开放。

## 获取小程序 `scheme` 码

```php
<?php
$miniProgram->urlScheme->generate();
```