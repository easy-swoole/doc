---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 用户标签

## 获取所有标签

```php
$officialAccount->userTag->list();
```

使用示例：

```php
$tags = $officialAccount->userTag->list();
```

获取结果：

```json
{
    "tags": [
        {
            "id": 0,
            "name": "标签1",
            "count": 72596
        },
        {
            "id": 1,
            "name": "标签2",
            "count": 36
        },
        // ...
    ]
}
```

## 创建标签

```php
$officialAccount->userTag->create($name);
```

使用示例：

```php
$officialAccount->userTag->create('测试标签');
```

## 修改标签信息

```php
$officialAccount->userTag->update($tagId, $name);
```

使用示例：

```php
$officialAccount->userTag->update(12, "新的名称");
```

## 删除标签

```php
$officialAccount->userTag->delete($tagId);
```

## 获取指定 openid 用户所属的标签

```php
$userTags = $officialAccount->userTag->userTags($openId);
```

获取结果：

```json
{
    "tagid_list":["标签1","标签2"]
}
```

## 获取标签下用户列表

```php
<?php

// $nextOpenId：第一个拉取的 OPENID，不填默认从头开始拉取
$officialAccount->userTag->usersOfTag($tagId, $nextOpenId = '');
```

获取结果示例：

```json
{
    "count":2, // 这次获取的粉丝数量
    "data":{ // 粉丝列表
         "openid":[
             "ocYxcuAEy30bX0NXmGn4ypqx3tI0",
             "ocYxcuBt0mRugKZ7tGAHPnUaOW7Y"
         ]
    },
    "next_openid":"ocYxcuBt0mRugKZ7tGAHPnUaOW7Y" // 拉取列表最后一个用户的openid
}
```

## 批量为用户添加标签

```php
<?php

$openIds = [$openId1, $openId2, ...];
$officialAccount->userTag->tagUsers($openIds, $tagId);
```

## 批量为用户移除标签

```php
<?php

$openIds = [$openId1, $openId2, ...];
$officialAccount->userTag->untagUsers($openIds, $tagId);
```