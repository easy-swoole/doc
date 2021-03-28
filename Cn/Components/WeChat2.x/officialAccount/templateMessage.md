---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 模板消息

模板消息仅用于公众号向用户发送重要的服务通知，只能用于符合其要求的服务场景中，如信用卡刷卡通知、商品购买成功通知等。不支持广告等营销类消息以及其它所有可能对用户造成骚扰的消息。

## 修改账号所属行业

```php
$officialAccount->templateMessage->setIndustry($industryId1, $industryId2);
```

## 获取支持的行业列表

```php
$officialAccount->templateMessage->getIndustry();
```

## 添加模板

在公众号后台获取 `$shortId` 并添加到账户。

```php
$officialAccount->templateMessage->addTemplate($shortId);
```

## 获取所有模板列表

```php
$officialAccount->templateMessage->getPrivateTemplates();
```

## 删除模板

```php
$officialAccount->templateMessage->deletePrivateTemplate($templateId);
```

## 发送模板消息

```php
<?php

$officialAccount->templateMessage->send([
    'touser' => 'user-openid',
    'template_id' => 'template-id',
    'url' => 'https://www.easyswoole.com',
    'miniprogram' => [
        'appid' => 'xxxxxxx',
        'pagepath' => 'pages/xxx',
    ],
    'data' => [
        'key1' => 'VALUE',
        'key2' => 'VALUE2',
        // ...
    ],
]);
```

如果 `url` 和 `miniprogram` 字段都传，会优先跳转小程序。

## 发送一次性订阅消息

```php
<?php

$officialAccount->templateMessage->sendSubscription([
    'touser' => 'user-openid',
    'template_id' => 'template-id',
    'url' => 'https://www.easyswoole.com',
    'scene' => 1000,
    'data' => [
        'key1' => 'VALUE',
        'key2' => 'VALUE2',
        // ...
    ],
]);
```    
    
如果你想为发送的内容字段指定颜色，你可以将 `"data"` 部分写成下面 `4` 种不同的样式，不写 `color` 将会是默认黑色：

```php
'data' => [
    'foo' => '你好',  // 不需要指定颜色
    'bar' => ['你好', '#F00'], // 指定为红色
    'baz' => ['value' => '你好', 'color' => '#550038'], // 与第二种一样
    'zoo' => ['value' => '你好'], // 与第一种一样
]
```