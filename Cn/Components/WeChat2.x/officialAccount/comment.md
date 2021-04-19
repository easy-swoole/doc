---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 评论数据管理

## 打开已群发文章评论

```php
$officialAccount->comment->open(string $msgId, int $index = null);
```

## 关闭已群发文章评论

```php
$officialAccount->comment->close(string $msgId, int $index = null);
```

## 查看指定文章的评论数据

```php
$officialAccount->comment->list(string $msgId, int $index, int $begin, int $count, int $type = 0);
```

## 将评论标记精选

```php
$officialAccount->comment->markElect(string $msgId, int $index, int $commentId);
```

## 将评论取消精选

```php
$officialAccount->comment->unmarkElect(string $msgId, int $index, int $commentId);
```

## 删除评论

```php
$officialAccount->comment->delete(string $msgId, int $index, int $commentId);
```

## 回复评论

```php
$officialAccount->comment->reply(string $msgId, int $index, int $commentId, string $content);
```

## 删除回复

```php
$officialAccount->comment->deleteReply(string $msgId, int $index, int $commentId);
```