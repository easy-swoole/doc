---
title: easyswoole Swoole Table  
meta:
  - name: description
    content: EasySwoole对Swoole table进行了基础的封装，用于共享内存。
  - name: keywords
    content: easyswoole Swoole Table
---

# Swoole Table

EasySwoole对Swoole table进行了基础的封装，用于共享内存。

## 基本使用

### 方法列表

获取TableManager管理器实例

```php
public function getInstance()
```

创建一个table

```php
public function add($name,array $columns,$size = 1024)
```

获取已经创建好的table

```php
public function get($name):? Table
```

### 简单示例

```php
\EasySwoole\Component\TableManager::getInstance()->add(
    self::TABLE_NAME,
    [
        'currentNum'=>['type'=>\Swoole\Table::TYPE_INT,'size'=>2],
    ],
    1024
);
```

::: warning
注意事项：请勿在 `onRequest` 、 `OnReceive` 等回调位置创建swoole table,swoole table应该在服务启动前创建，比如在 · 事件中创建。
:::
