---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---


# 菜单

## 读取（查询）已设置菜单

```php
$list = $officialAccount->menu->query();
```

## 获取当前菜单

```php
$current = $officialAccount->menu->queryConfig();
```

## 添加菜单

### 添加普通菜单

```php
<?php

$buttons = [
    'button' => [
        [
            "type" => "click",
            "name" => "今日歌曲",
            "key" => "V1001_TODAY_MUSIC"
        ],
        [
            "name" => "菜单",
            "sub_button" => [
                [
                    "type" => "view",
                    "name" => "搜索",
                    "url" => "http://www.soso.com/"
                ],
                [
                    "type" => "view",
                    "name" => "视频",
                    "url" => "http://v.qq.com/"
                ],
                [
                    "type" => "click",
                    "name" => "赞一下我们",
                    "key" => "V1001_GOOD"
                ],
            ],
        ],
    ]
];

$officialAccount->menu->create($buttons);
```

以上将会创建一个普通菜单。

### 添加个性化菜单

与创建普通菜单不同的是，需要在 `create()` 方法中将个性化匹配规则作为第二个参数传进去：

```php
<?php

$matchRule = [
    "matchrule" => [
        "tag_id" => "2",
        "sex" => "1",
        "country" => "中国",
        "province" => "广东",
        "city" => "广州",
        "client_platform_type" => "2",
        "language" => "zh_CN"
    ],
];

$buttons = [
    'button' => [
        [
            "type" => "click",
            "name" => "今日歌曲",
            "key" => "V1001_TODAY_MUSIC"
        ],
        [
            "name" => "菜单",
            "sub_button" => [
                [
                    "type" => "view",
                    "name" => "搜索",
                    "url" => "http://www.soso.com/"
                ],
                [
                    "type" => "view",
                    "name" => "视频",
                    "url" => "http://v.qq.com/"
                ],
                [
                    "type" => "click",
                    "name" => "赞一下我们",
                    "key" => "V1001_GOOD"
                ],
            ],
        ],
    ],
    $matchRule
];

$officialAccount->menu->addconditional($buttons);
```

## 删除菜单

有两种删除方式，一种是全部删除，另外一种是根据菜单 `ID` 来删除(删除个性化菜单时用，`ID` 从查询接口获取)：

```php
// 删除全部
$officialAccount->menu->delete();
```

### 删除个性化菜单

```php
$officialAccount->menu->delconditional($menuId);
```

## 测试个性化菜单

```php
$officialAccount->menu->match($userId);
```

`$userId` 可以是粉丝的 `OpenID`，也可以是粉丝的微信号。

返回 `$menu` 与指定的 `$userId` 匹配的菜单项。