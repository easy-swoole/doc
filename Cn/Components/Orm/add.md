---
title: easyswoole orm 插入数据
meta:
  - name: description
    content: easyswoole orm 插入数据
  - name: keywords
    content:  easyswoole orm 插入数据
---

# 新增

要往数据库新增一条记录，先创建新模型实例，给实例设置属性，然后调用 save 方法：

```php
$model = new UserModel();
// 不同设置值的方式
$model->setAttr('id', 7);
$model->name = 'name';
$model['name'] = 'name';

$res = $model->save();
var_dump($res); // 返回自增id 或者主键的值  失败则返回null
```
在这个示例中，我们将 `id` 和 `name` 赋值给了 UserModel 模型实例的 `id` 和 `name` 属性。当调用 `save` 方法时，将会插入一条新记录


### 数组赋值

可以传入数组`[字段名=>字段值]` 再调用 `save` 方法保存

```php
$model = UserModel::create([
    'name' => 'siam',
    'age'  => 21,
]);

$res = $model->save();
```

```php
// data($data, $setter = true)  
// 第二个参数 可以决定是否要调用修改器（如果要设置的话   下面的文档有说明）
$user = UserModel::create()->data([
    'name' => 'siam',
    'age'  => 21,
], false)->save();
```


### 批量插入

saveAll可以传递二维数组，批量插入数据，但由于ORM的工作职责，他需要将数据映射为对象，所以在内部处理中还是通过遍历处理，而非一条sql插入

（如果有此需求的用户请自行自定义执行sql语句）（mysqli组件中提供了 insertMulti 方法，ORM可以使用func方式调用）

```php
function saveAll($data, $replace = true, $transaction = true)
```

参数说明

- 数据，二维数组
- 是否覆盖，意思为：如果在数组中包含了pk主键的值，那么则操作为更新 ` if ( $replace && isset($row[$pk]) )`
- 是否开启事务，默认为true，如果是已经手动开启过事务，并在中间调用saveAll，则需要关闭这里的事务，否则因为内部代码的开启事务，导致你的程序执行逻辑异常。
