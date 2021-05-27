---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 生物认证

## 生物认证秘钥签名验证

```php
$miniProgram->soter->verifySignature($openid, $json, $signature);
```

返回值示例：

```json
{
    "is_ok": true
}
```

参数说明：
:::tip
- string $openid - 用户 openid
- string $json - 通过 wx.startSoterAuthentication 成功回调获得的 resultJSON 字段
- string $signature - 通过 wx.startSoterAuthentication 成功回调获得的 resultJSONSign
:::