---
title: ORM结果转换数组
meta:
  - name: description
    content: Easyswoole ORM组件,
  - name: keywords
    content:  swoole|swoole 拓展|swoole 框架|EasySwoole mysql ORM|EasySwoole ORM|Swoole mysqli协程客户端|swoole ORM|查询|ORM结果转换数组
---

# 结果转换数组

查询后将对象转为数组

## 传参

toArray和toRawArray传参一致

| 参数名       |  参数说明                                                     |
| --------------- | ------------------------------------------------------------ |
| notNull | 是否过滤空，bool类型 默认`false`，当为true时，只返回非空字段 |
| strict | 严格模式，bool类型 默认`true`，当为true时，只返回当前模型对应数据表的字段，其他field别名等不返回。 |


## 示例

经过获取器
```php
$model = Model::create()->get(1);
$array = $model->toArray();

$model = Model::create()->all();
foreach($model as $one){
    var_dump($one->toArray());
}
```


不经过获取器
```php
$model = Model::create()->get(1);
$array = $model->toRawArray();

$model = Model::create()->all();
foreach($model as $one){
    var_dump($one->toRawArray());
}
```

## 筛选

`orm > 1.4.4`

在调用toArray或toRawArray之前。可以通过调用`field()`和`hidden()`方法进行返回数据的筛选

两个方法的传参为`array|string`,string代表只过滤一个字段

```php
$field = $model->field(['user_list'])->toArray(false, false); // 返回的数组里只有user_list一个元素
$hidden = $model->hidden('user_list')->toArray(false, false); // 返回的数组里过滤了user_list元素

```

### 追加

追加非模型字段的属性，必须设置获取器。

```php
\EasySwoole\ORM\Tests\models\TestUserListModel::create()->all()->append(['append_one'])->toArray();
```

### 显示

显示指定字段。

```php
\EasySwoole\ORM\Tests\models\TestUserModel::create()->all()->visible(['username','password'])->toArray();
``` 

### 隐藏

隐藏指定字段。

```php
\EasySwoole\ORM\Tests\models\TestUserModel::create()->all()->hidden(['password'])->toArray();
``` 


## 注意事项

模型层`all`方法,默认是不返回`Collection`的,需要通过`foreach`进行:

```php
$results = \EasySwoole\ORM\Tests\models\TestUserModel::create()->all();
/** @var \EasySwoole\ORM\AbstractModel $result */
foreach($results as $result) {
    $result->toArray();
}

```

可以通过配置项[returnCollection](/Components/Orm/core.html)进行配置,即可快速调用：

```php
\EasySwoole\ORM\Tests\models\TestUserModel::create()->all()->toArray();
```

> get方法不受此配置项影响。

如果不想修改此配置项，兼容以前代码可以通过以下代码快速实现`toArray`.

```php
$ret = \EasySwoole\ORM\Tests\models\TestUserModel::create()->all();
if (!$ret instanceof \EasySwoole\ORM\Collection\Collection) {
    $ret = new \EasySwoole\ORM\Collection\Collection($ret);
}
$ret->toArray();
```

> 以上代码自己去做封装.
