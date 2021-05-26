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
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 agentid
    'agentId' => 100020, // 如果有 agentid 则填写
];

// 企业微信
$work = \EasySwoole\WeChat\Factory::work($config);
```
然后你就可以用 `$work` 来调用企业微信的服务了。
