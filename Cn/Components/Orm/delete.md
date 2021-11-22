---
title: easyswoole ORM 删除数据
meta:
  - name: description
    content: easyswoole ORM 删除数据
  - name: keywords
    content:  easyswoole ORM 删除数据
---


# 删除

删除记录使用 `destroy` 方法, 方法可以传入多种表达类型参数. 执行后返回影响的记录数

## 通过 已有Model

这种方式是我们最推荐的，也是ORM这种组件的核心思想，把数据的操作映射为对对象的操作。

```php
$user = UserModel::create()->get(1);
$user->destroy();
```

## 通过 主键 

```php
$res = UserModel::create()->destroy(1); //通过直接指定主键(如果存在)
$res = UserModel::create()->destroy([3, 7]);//数组指定多个主键

### orm 组件版本 >= 1.5.0 时不再支持以下写法来删除多条数据
$res = UserModel::create()->destroy('2,4,5');//指定多个参数每个参数为不同主键
/**
// orm 组件版本为 1.5.0 或者 1.5.1 时，下面等价于 DELETE FROM `test`
$res = UserModel::create()->destroy('2,4,5');
// orm 组件版本 >= 1.5.2 时，下面等价于 DELETE FROM `test` where id = '2,4,5'
$res = UserModel::create()->destroy('2,4,5');
*/
```

### 通过 where 条件

```php
$res = UserModel::create()->destroy(['age' => 21]);//数组指定 where 条件结果来删除
$res = UserModel::create()->destroy(function (QueryBuilder $builder) {
    $builder->where('id', 1);
});
```

## 删除全表数据

如果你需要清空表，你可以使用 destroy 方法传入 (null,true)，它将删除所有行

```php
$res = UserModel::create()->destroy(null,true);
```
