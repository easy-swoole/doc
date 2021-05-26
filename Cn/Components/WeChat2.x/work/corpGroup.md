---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 企业互联

## 获取应用共享信息

```php
<?php
$agentId = 100001;

$work->corpGroup->getAppShareInfo(int $agentId);
```

## 获取下级企业的 `access_token`

```php
<?php
$corpId = 'wwd216fa8c4c5c0e7x';
$agentId = 100001;

$work->corpGroup->getToken(string $corpId, int $agentId)
```

## 获取下级企业的小程序 `session`

```php
<?php
$userId = 'wmAoNVCwAAUrSqEqz7oQpEIEMVWDrPeg';
$sessionKey = 'n8cnNEoyW1pxSRz6/Lwjwg==';
        
$work->corpGroup->getMiniProgramTransferSession(string $userId, string $sessionKey);
```