---
title: easyswoole ORM 事务操作管理
meta:
  - name: description
    content: easyswoole ORM 事务操作管理
  - name: keywords
    content:  easyswoole ORM 事务操作管理
---

# 事务操作

- DbManager 链接管理器提供

## DbManager操作事务

传参说明（代码示例看下文）

| 参数类型        |  参数说明                                                     |
| --------------- | ------------------------------------------------------------ |
| string或array | 值为connectionName，代表当前协程下连接名相符的mysql链接执行事务 |
| ClientInterface | 在invoke闭包中直接传入client，代表直接操作指定客户端 |


返回说明：bool  开启成功则返回true，开启失败则返回false

- DbManager->startTransaction($connection  = 'default' ); // 参数名默认为default
- DbManager->commit($connection = 'default'); // 如果不传，则提交当前协程下连接名称为default的事务
- DbManager->rollback($connection = 'default'); // 如果不传，则回滚当前协程下连接名称为default的事务

## Client 直接管理

无需传参

返回说明：bool  开启成功则返回true，开启失败则返回false

- ClientInterface->startTransaction();
- ClientInterface->commit();
- ClientInterface->rollback();


## 代码示例1

DbManager 管理事务 ，可以开启多个连接名下的客户端事务，进行多连接事务管理

```php
try{
    //开启事务
    DbManager::getInstance()->startTransaction();
    $res = $model->update(['is_vip'=>1]);
} catch(\Throwable  $e){
    //回滚事务
    DbManager::getInstance()->rollback();
} finally {
    //提交事务
    DbManager::getInstance()->commit();
}
```

## 代码示例2

DbManager 管理事务，传递参数为ClientInterface类型，指定操作客户端

效果等同于示例3，直接操作客户端

```php
// 指定取出 write 连接名下的客户端，并且执行开启事务
\EasySwoole\ORM\DbManager::getInstance()->invoke(function (EasySwoole\ORM\Db\ClientInterface $client){
    // 开启事务
    \EasySwoole\ORM\DbManager::getInstance()->startTransaction($client);
    // ...
}, 'write');
```