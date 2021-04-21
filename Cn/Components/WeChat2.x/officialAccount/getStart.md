---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 入门

`EasySwoole WeChat` 组件公众号的各模块相对比较统一，用法如下：

在服务端中，我们通过如下方式获取到公众号操作相关的整个实例，用法如下：

```php
<?php

$config = [
    // 微信公众平台后台的 appid
    'appId' => 'wxefe41fdeexxxxxx', 
    
    // 微信公众平台后台配置的 Token
    'token' => 'dczmnau31ea9nzcnxxxxxxxxx',
    
    // 微信公众平台后台配置的 EncodingAESKey
    'aesKey' => 'easyswoole',
   
    // 微信公众平台后台配置的 AppSecret
    'appSecret' => 'your-AppSecret'
];

// 公众号
$officialAccount = \EasySwoole\WeChat\Factory::officialAccount($config);
```

`$officialAccount` 在后文所有相关公众号的文档都是指 `\EasySwoole\WeChat\Factory::officialAccount` 得到的实例，下面就不在每个页面单独写了。

## 重点总结

所有的应用服务都通过主入口 `EasySwoole\WeChat\Factory` 类来创建：

```php
<?php

use EasySwoole\WeChat\Factory;

// 公众号
$officialAccount = Factory::officialAccount($config);

// 小程序
$miniProgram = Factory::miniProgram($config);

// 开放平台
$openPlatform = Factory::openPlatform($config);

// 企业微信
$work = Factory::work($config);
```
