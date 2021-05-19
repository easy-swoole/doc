---
title: easyswoole参数验证器
meta:
  - name: description
    content: easyswoole参数验证器
  - name: keywords
    content: easyswoole参数验证器|swoole参数验证器
---

# EasySwoole 验证器组件

`EasySwoole` 提供了独立的 `验证器组件`，几行代码即可实现对请求参数进行验证。常用于对 `HTTP` 等请求中的请求参数的验证。

::: tip
  验证器 `Validate` 组件当前最新版本为 `2.0.0`，相比旧版本 `1.3.0` 及之前版本支持了更强的验证规则，也允许用户使用更多的自定义操作，更加方便用户对请求参数进行验证。关于组件旧版本 `1.3.0` 及更早版本的使用文档请查看 [Validate 1.3.x 文档](/Components/Validate/validate_1.3.x.md)
:::

另外框架还提供了在注解中对 `HTTP` 请求参数进行校验的组件，可以很方便地对 `HTTP` 请求参数的合法性进行校验。在注解中就可以设置请求参数的验证规则，使得代码更简洁，详细使用见 [参数注解校验](HttpServer/Annotation/param.md)。

## 组件要求

- php: >= 7.1.0
- easyswoole/spl: ^1.0
- psr/http-message: ^1.0
- ext-json: *
- ext-mbstring: *

## 安装方法

框架 `3.4.x` 及以上版本自带 `validate` 组件，所以不需要单独安装。`3.4.x` 之前的版本请单独安装，安装方法如下：

> composer require easyswoole/validate

## 仓库地址

[easyswoole/validate](https://github.com/easy-swoole/validate)

## 基本使用

### 普通验证

#### 支持的验证方法

普通验证支持的验证方法有如下：`activeUrl`、`allDigital`、`allowFile`、`allowFileType`、`alpha`、`alphaDash`、`alphaNum`、`between`、`betweenLen`、`bool`、`dateAfter`、`dateBefore`、`decimal`、`different`、`differentWithColumn`、`equal`、`equalWithColumn`、`float`、`func`、`greaterThanWithColumn`、`inArray`、`integer`、`isArray`、`isIp`、`length`、`lengthMax`、`lengthMin`、`lessThanWithColumn`、`max`、`min`、`url`、`money`、`notEmpty`、`notInArray`、`numeric`、`optional`、`regex`、`required`、`timestampAfter`、`timestampAfterDate`、`timestampBefore`、`timestampBeforeDate`、`url`。

验证方法的具体使用可查看 [方法列表](/Components/Validate/validate_1.3.x.html#验证规则用法说明)

#### 使用组件提供的默认的验证错误信息提示

`validate` 验证器提供了默认验证错误信息的规则，点击查看 [默认验证错误信息的规则](https://github.com/easy-swoole/validate/blob/2.x/src/Error.php)。

使用示例如下：

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

// 要验证的数据
$data = [
    'name' => 'easyswoole',
    'age' => 19
];

// 初始化验证器对象
$validate = new \EasySwoole\Validate\Validate();

// 给字段加上验证规则 (验证数据中 name 字段不能没有)
$validate->addColumn('name')->required();

// 给字段加上验证规则 (验证数据中 age 字段不能没有且值不能大于18)
$validate->addColumn('age')->required()->max(18);

// 验证结果：验证通过返回 true 反之返回 false
$bool = $validate->validate($data);
if ($bool) {
    var_dump("验证通过");
} else {
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果：string(23) "age的值不能大于18"
 */
```

> 注意：验证器组件的验证顺序是按照添加验证规则时的 `添加字段的先后顺序` 和 `验证规则的先后顺序` 逐个进行验证的，先添加的验证规则不通过则直接返回验证失败，然后就可以获取对应的验证错误信息。例如上述示例中，会优先验证 `name` 字段是否存在。下面示例也是一样的原理。

#### 使用自定义的验证错误信息提示

使用示例如下：

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

// 要验证的数据
$data = [
    'name' => 'easyswoole',
    'age' => 16
];

// 初始化验证器对象
$validate = new \EasySwoole\Validate\Validate();

// 给字段加上验证规则 (验证数据中 name 字段不能没有)
$validate->addColumn('name')->required('名字不为空');

// 给字段加上验证规则
$validate->addColumn('age')->required('年龄不为空')->func(function ($itemData, $column, \EasySwoole\Validate\Validate $validate) {

    // 获取要验证的数据，为 1 个 \EasySwoole\Spl\SplArray 对象
    var_dump($validate->getVerifyData());

    // 判断要验证的数据是否属于 \EasySwoole\Spl\SplArray
    var_dump($validate->getVerifyData() instanceof \EasySwoole\Spl\SplArray);

    // 获取验证的字段名，为 'age'，即 addColumn() 中设置的字段名
    var_dump($column);

    // 获取验证的字段名的值，为 18
    var_dump($itemData);

    return ($validate->getVerifyData() instanceof \EasySwoole\Spl\SplArray) && $column === 'age' && $itemData === 0.001;
}, '只允许18岁的进入');

// 验证结果：验证通过返回 true 反之返回 false
$bool = $validate->validate($data);
if ($bool) {
    var_dump("验证通过");
} else {
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果：string(23) "只允许18岁的进入"
 */
```

### 自定义验证

#### 使用自定义验证器类的自定义验证规则

使用示例如下：

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

class CustomValidator extends \EasySwoole\Validate\Functions\AbstractValidateFunction
{
    /**
     * 返回当前校验规则的名字
     */
    public function name(): string
    {
        return 'mobile';
    }

    /**
     * 验证失败返回 false，或者用户可以抛出异常，验证成功返回 true
     * @param $itemData
     * @param $arg
     * @param $column
     * @return bool
     */
    public function validate($itemData, $arg, $column, \EasySwoole\Validate\Validate $validate): bool
    {
        $regular = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$/';
        if (!preg_match($regular, $itemData)) {
            return false;
        }

        return true;
    }
}

// 待验证数据
$data = [
    'mobile' => '12312345678'
];

$validate = new \EasySwoole\Validate\Validate();

// 先添加 function 第一个参数为类，第二个参数设置是否覆盖 (当存在相同名字的验证规则，传参数 true 会替换掉前面设置的同名的验证规则)
$validate->addFunction(new CustomValidator(), false);

// 自定义错误消息示例
$validate->addColumn('mobile')->required('手机号不能为空')->callUserRule(new CustomValidator(), '手机号格式不正确');

// 验证结果
$bool = $validate->validate($data);
if ($bool) {
    var_dump("验证通过");
} else {
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果：string(24) "手机号格式不正确"
 */
```

### 特殊验证

#### 使用带 * 号的匹配规则进行验证

使用示例如下：

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$validate = new \EasySwoole\Validate\Validate();

// * 可以放在任意位置 且有多个
$validate->addColumn('*.a')->required()->notEmpty()->between(1, 10);

// 验证结果
$bool = $validate->validate([
    'a' => ['a' => 1],
    'b' => ['a' => 11]
]);
if ($bool) {
    var_dump("验证通过");
} else {
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果：*.a只能在 1 - 10 之间
 */
```

### 快速验证

我们还提供了数组快速验证方式。

函数原型：`EasySwoole\Validate\Validate::make()`:

参数：

- `$rules` 验证规则.
- `$message` 自定义错误信息.
- `$alias` 字段别名.

返回值：

- `\EasySwoole\Validate\Validate::class`实例.

使用示例如下：

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

// 验证规则
$rules = [
    'name' => 'required|notEmpty',
    'age' => 'required|integer|between:20,30',
    'weight' => 'required|max:50'
];

// 验证错误消息提示
$messages = [
    'name.required' => '名字不能为空！',
    'age' => '年龄输入有误！',
    'weight.max' => '体重最大不能超过50！'
];

// 验证字段的别名
$alias = [
    'name' => '名字',
    'age' => '年龄',
    'weight' => '体重'
];

// 组装快速验证
$validate = \EasySwoole\Validate\Validate::make($rules, $messages, $alias);

// 验证结果
$bool = $validate->validate([
    'name' => '史迪仔',
    'age' => 20,
    'weight' => 70
]);
if ($bool) {
    var_dump("验证通过");
} else {
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果：weight的值不能大于'50'
 */
```

> 暂不支持 `inArray`、`notInArray`、`func`、`callUserRule`、`allowFile`、`allowFileType` 等规则。

其他的具体的验证规则，可查看 [验证规则列表](/Components/Validate/validate_1.3.x.html#方法列表)
