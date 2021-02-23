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

## toArray数据集

如果设置了[returnCollection](/Components/Orm/core.html)为`true`，无需进行`foreach`。可直接：

```php
\EasySwoole\ORM\Tests\models\TestUserModel::create()->all()->toArray();
```

### 追加

追加非模型字段的属性，必须设置获取器。

```php
\EasySwoole\ORM\Tests\models\TestUserModel::create()->all()->append(['a' => 1])->toArray();
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