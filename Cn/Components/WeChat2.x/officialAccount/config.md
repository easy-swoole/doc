---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 配置

常用的配置参数会比较少，因为除非你有特别的定制，否则基本上默认值就可以了：

```php
<?php

use EasySwoole\WeChat\Factory;

$config = [
    // 微信公众平台后台的 appid
    'appId' => 'wxefe41fdeexxxxxx',

    // 微信公众平台后台配置的 Token
    'token' => 'dczmnau31ea9nzcnxxxxxxxxx',

    // 微信公众平台后台配置的 EncodingAESKey
    'aesKey' => 'easyswoole',
    
    // 微信公众平台后台配置的 AppSecret
    'secret' => 'AppSecret',

    //...
];

// 公众号
$officialAccount = Factory::officialAccount($config);
```

下面是一个完整的配置样例：

不建议你在配置的时候弄这么多，用到啥就配置啥才是最好的，因为大部分用默认值即可。

```php
<?php

return [
    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'appId'   => 'your-app-id',         // AppID
    'secret'  => 'your-app-secret',     // AppSecret
    'token'   => 'your-token',          // Token
    'aesKey'  => 'your-EncodingAESKey', // EncodingAESKey，兼容与安全模式下请一定要填写！！！
    
    
    /** 
     * 缓存配置
     *
     * tempDir：缓存文件存放位置 (绝对路径!!!)，要求可写权限 
     */
    'cache' => [
        'tempDir' => sys_get_temp_dir(), // 默认使用的 sys_get_temp_dir()
    ]
];
```

## 自定义日志驱动

暂时略。

## 自定义缓存驱动

暂时略。

## 自定义实现 httpClient 驱动

暂时略。

::: tip
  安全模式下请一定要填写 `aesKey`。
:::
