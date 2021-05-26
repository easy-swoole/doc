---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 微信小程序消息解密

## 比如获取电话等功能，信息是加密的，需要解密。

API：

```php
<?php
$encryptObj = new \EasySwoole\WeChat\MiniProgram\Encryptor();

$decryptedData = $encryptObj->decryptData($session, $iv, $encryptedData);
```