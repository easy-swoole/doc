---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 小程序

## 登录获取用户信息

> 注意：需要关联小程序，并且使用关联后的小程序 `AgentId` 与 `Secret`。

```php
<?php
$config = [
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 agentid
    'agentId' => 100020, // 如果有 agentid 则填写
];

$miniProgram = new \EasySwoole\WeChat\Work\MiniProgram\Application($config);

$res = $miniProgram->auth->session("js-code");
```