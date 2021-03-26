---
title: easyswoole ORM 事务操作管理
meta:
  - name: description
    content: easyswoole ORM 事务操作管理
  - name: keywords
    content:  easyswoole ORM 事务操作管理
---

# 事务操作

- DbManager 连接管理器提供

## DbManager 操作事务

传参说明（代码示例看下文）

| 参数类型        |  参数说明                                                     |
| --------------- | ------------------------------------------------------------ |
| string 或 array | 值为 $connectionName (即连接名)，代表使用当前协程下连接名为 $connectionName 的 mysql 连接执行事务 |
| ClientInterface | 在 invoke 闭包中直接传入 client，代表直接操作指定 mysql 客户端 |


返回值说明：`bool`。开启成功则返回 `true`，开启失败则返回 `false`。


> 开启事务

```php
// $connection 参数默认为 'default'，表示使用当前协程下连接名为 $connectionName 的 mysql 连接开启事务
\EasySwoole\ORM\DbManager::getInstance()->startTransaction($connection = 'default');
```

> 提交事务

```php
// 如果不传 'default'，则提交当前协程下连接名称为 default 的事务
\EasySwoole\ORM\DbManager::getInstance()->commit($connection = 'default');
```

> 回滚事务

```php
// 如果不传 'default'，则回滚当前协程下连接名称为 default 的事务
\EasySwoole\ORM\DbManager::getInstance()->rollback($connection = 'default');
```

## Client 直接管理

无需传参

返回值说明：`bool`。开启成功则返回 `true`，开启失败则返回 `false`。

- ClientInterface->startTransaction();
- ClientInterface->commit();
- ClientInterface->rollback();


## 代码示例1

DbManager 管理事务 ，可以开启多个连接名下的客户端事务，进行多连接事务管理

```php
try {
    // 开启事务
    \EasySwoole\ORM\DbManager::getInstance()->startTransaction();
    
    // 执行更新 $model 的更新操作
    $model = new UserModel();
    $res = $model->update(['is_vip' => 1]);
    
    // 提交事务
    \EasySwoole\ORM\DbManager::getInstance()->commit();
} catch(\Throwable  $e){
    // 回滚事务
    \EasySwoole\ORM\DbManager::getInstance()->rollback();
}
```

## 代码示例2

`DbManager` 管理事务，传递参数为 `ClientInterface` 类型，指定操作客户端

效果等同于上文代码示例3，直接操作客户端

```php
// 指定取出 write 连接名下的客户端，并且执行开启事务
\EasySwoole\ORM\DbManager::getInstance()->invoke(function (EasySwoole\ORM\Db\ClientInterface $client){
    // 开启事务
    \EasySwoole\ORM\DbManager::getInstance()->startTransaction($client);
    
    // ...
}, 'write');
```