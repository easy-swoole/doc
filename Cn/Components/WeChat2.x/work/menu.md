---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 自定义菜单

自定义菜单是指为单个应用设置自定义菜单功能，所以在使用时请注意调用正确的应用实例。

```php
<?php
$config = [
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    
    // ...
];

// 企业微信
$work = \EasySwoole\WeChat\Factory::work($config);
```

## 创建菜单

```php
<?php
$menus = [
    'button' => [
        [
            'name' => '首页',
            'type' => 'view',
            'url' => 'https://www.easyswoole.com'
        ],
        [
            'name' => '关于我们',
            'type' => 'view',
            'url' => 'https://www.easyswoole.com/about'
        ],
        // ...
    ],
];
$work->menu->create($menus);
```

## 获取菜单

```php
$work->menu->get();
```

## 删除菜单

```php
$work->menu->delete();
```