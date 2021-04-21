---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 企业微信

企业微信的使用与公众号以及其它几个应用的使用方式都是一致的，使用 `\EasySwoole\WeChat\Factory::work($config)` 来初始化：

```php
<?php

$config = [
    // 企业微信平台后台的 appid
    'appId' => 'wxefe41fdeexxxxxx', 

    // 企业微信平台后台配置的 Token
    'token' => 'dczmnau31ea9nzcnxxxxxxxxx',

    // 企业微信平台后台配置的 EncodingAESKey
    'aesKey' => 'easyswoole',

    // 企业微信平台后台配置的 AppSecret
    'appSecret' => 'your-AppSecret'
];

// 企业微信
$work = \EasySwoole\WeChat\Factory::work($config);
```
然后你就可以用 `$work` 来调用企业微信的服务了。

其他文档暂时没写。详细使用可参考 `easywechat` 对应 `API` 的用法。
