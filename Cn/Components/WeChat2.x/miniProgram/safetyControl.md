---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 安全风控

微信文档：[https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/safety-control-capability/riskControl.getUserRiskRank.html](https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/safety-control-capability/riskControl.getUserRiskRank.html)

> 根据提交的用户信息数据获取用户的安全等级 `risk_rank`，无需用户授权。

## 获取用户的安全等级

```php
<?php
$miniProgram->riskControl->getUserRiskRank([
    'appid' => 'wx311232323',
    'openid' => 'oahdg535ON6vtkUXLdaLVKvzJdmM',
    'scene' => 1,
    'client_ip' => '12.234.134.2',
]);
```