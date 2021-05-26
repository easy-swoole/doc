---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 小程序

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

// 小程序
$miniProgram = \EasySwoole\WeChat\Factory::miniProgram($config);
```

`$miniProgram` 在所有相关小程序的文档都是指 `Factory::miniProgram` 得到的实例，就不在每个页面单独写了。
