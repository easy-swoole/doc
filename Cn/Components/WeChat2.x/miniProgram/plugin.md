---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 插件管理

微信文档：[https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/plugin-management/pluginManager.applyPlugin.html](https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/plugin-management/pluginManager.applyPlugin.html)

## 申请使用插件

```php
<?php
$pluginAppId = 'xxxxxxxxx';

$miniProgram->plugin->apply($pluginAppId);
```

## 查询已添加的插件

```php
$miniProgram->plugin->list();
```

## 删除已添加的插件

```php
<?php
$pluginAppId = 'xxxxxxxxx';

$miniProgram->plugin->unbind($pluginAppId);
```

## 获取当前所有插件使用方

```php
<?php
$page = 1;
$size = 10;

$miniProgram->pluginDev->getUsers($page, $size);
```

## 同意插件使用申请

```php
<?php
$appId = 'wxxxxxxxxxxxxxx';

$miniProgram->pluginDev->agree($appId);
```

## 拒绝插件使用申请

```php
$miniProgram->pluginDev->refuse('拒绝理由');
```

## 删除已拒绝的申请者

```php
$miniProgram->pluginDev->delete();
```
