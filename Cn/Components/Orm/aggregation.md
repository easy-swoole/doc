---
title: easyswoole orm 聚合操作 快捷查询
meta:
  - name: description
    content: easyswoole orm 聚合操作 快捷查询
  - name: keywords
    content:  easyswoole orm 聚合操作 快捷查询
---

# 聚合

ORM 还提供了各种聚合方法，比如 count, max，min， avg，还有 sum。你可以在构造查询后调用任何方法：

## max

```php
$max = TestUserListModel::create()->max('age');
```

## min

```php
$min = TestUserListModel::create()->min('age');
```

## count

```php
// count 不必传字段名
$count = TestUserListModel::create()->count();
```

## avg

```php
$avg = TestUserListModel::create()->avg('age');
```

## sum

```php
$sum = TestUserListModel::create()->sum('age');
```

