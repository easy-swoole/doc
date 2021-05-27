---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 网页授权

:::warning
  此文档为企业微信内部应用开发的网页授权
:::

[企业微信官方文档](https://work.weixin.qq.com/api/doc#90000/90135/91020)

创建实例：

```php
<?php
$config = [
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 agentid
    'agentId' => 100001,
];

// 企业微信
$work = \EasySwoole\WeChat\Factory::work($config);
```

## 跳转授权

```php
<?php
// $callbackUrl 为授权回调地址
$callbackUrl = 'https://xxx.xxx'; // 需设置可信域名

// 获取企业微信跳转目标地址
$redirectUrl = $work->oauth->redirect($callbackUrl);
```

## 获取授权用户信息

在回调页面中，你可以使用以下方式获取授权者信息：

```php
<?php
$code = "回调 URL 中的 code";

/** @var \EasySwoole\WeChat\Work\OAuth\User\User $user */
$user = $work->oauth->userFromCode($code);

// 获取用户信息
$user->getUserId(); // 对应企业微信英文名（userid）
$user->getRaw(); // 获取企业微信接口返回的原始信息
```