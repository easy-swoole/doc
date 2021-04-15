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

另外框架还提供了在注解中对 `HTTP` 请求参数进行校验的组件，可以很方便地对 `HTTP` 请求参数的合法性进行校验。在注解中就可以设置请求参数的验证规则，使得代码更简洁，详细使用见 [参数注解校验](HttpServer/Annotation/param.md)。


## 组件要求

- php: >= 7.1.0
- easyswoole/spl: ^1.0
- psr/http-message: ^1.0

## 安装方法
框架 `3.4.x` 及以上版本自带 `validate` 组件，所以不需要单独安装。`3.4.x` 之前的版本请单独安装，安装方法如下：
> composer require easyswoole/validate

## 仓库地址
[easyswoole/validate](https://github.com/easy-swoole/validate)


## 默认提供的验证错误信息提示说明

`validate` 验证器组件提供了默认验证错误信息的规则，详细如下：
```
private $defaultErrorMsg = [
    'activeUrl'     => ':fieldName必须是可访问的网址',
    'alpha'         => ':fieldName只能是字母',
    'between'       => ':fieldName只能在 :arg0 - :arg1 之间',
    'bool'          => ':fieldName只能是布尔值',
    'dateBefore'    => ':fieldName必须在日期 :arg0 之前',
    'dateAfter'     => ':fieldName必须在日期 :arg0 之后',
    'equal'         => ':fieldName必须等于:arg0',
    'float'         => ':fieldName只能是浮点数',
    'func'          => ':fieldName自定义验证失败',
    'inArray'       => ':fieldName必须在 :arg0 范围内',
    'integer'       => ':fieldName只能是整数',
    'isIp'          => ':fieldName不是有效的IP地址',
    'notEmpty'      => ':fieldName不能为空',
    'numeric'       => ':fieldName只能是数字类型',
    'notInArray'    => ':fieldName不能在 :arg0 范围内',
    'length'        => ':fieldName的长度必须是:arg0',
    'lengthMax'     => ':fieldName长度不能超过:arg0',
    'lengthMin'     => ':fieldName长度不能小于:arg0',
    'max'           => ':fieldName的值不能大于:arg0',
    'min'           => ':fieldName的值不能小于:arg0',
    'regex'         => ':fieldName不符合指定规则',
    'required'      => ':fieldName必须填写',
    'timestamp'     => ':fieldName必须是一个有效的时间戳',
    'url'           => ':fieldName必须是合法的网址',
    'allowFile'     => ':fieldName文件扩展名必须在:arg0内',
    'allowFileType' => ':fieldName文件类型必须在:arg0内',
    'isArray'       => ':fieldName类型必须为数组'
];
```

## 基本使用

### 使用组件提供的默认的验证错误信息提示
使用示例如下：
```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$data = [
    'name' => 'blank',
    'age' => 25
]; // 要验证的数据
$validate = new \EasySwoole\Validate\Validate();
$validate->addColumn('name')->required(); // 给字段加上验证规则(验证数据中 name 字段不能没有)
$validate->addColumn('age')->required()->max(18); // 给字段加上验证规则(验证数据中 age 字段不能没有且值不能大于18)
$bool = $validate->validate($data); // 验证结果：验证通过返回true 反之返回false
if ($bool) {
    var_dump("验证通过");
} else {
    // 输出验证错误信息：
    var_dump($validate->getError()->__toString());
}
/*
 * 输出结果： string(23) "age的值不能大于18"
 */
```

### 使用自定义的验证错误信息提示
使用示例如下：
```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$data1 = [
    'name' => 'easyswoole',
    'age' => 25
]; // 要验证的数据
$validate1 = new \EasySwoole\Validate\Validate();
$validate1->addColumn('name', '名字')->required('参数不能缺少!'); // 给字段加上验证规则(验证数据中 name 字段不能没有)
$validate1->addColumn('age', '年龄')->required('参数不能缺少!')->max(18, '不能大于18周岁'); // 给字段加上验证规则(验证数据中 age 字段不能没有且值不能大于18)
$bool = $validate1->validate($data1); // 验证结果：验证通过返回true 反之返回false
if ($bool) {
    var_dump("验证通过");
} else {
    // 输出验证错误信息：
    $fieldName = $validate1->getError()->getFieldAlias(); // 获取验证规则中设置的字段别名 '年龄'
    $errorMsg = $validate1->getError()->__toString(); // 获取验证错误信息 '不能大于18周岁'
    var_dump($fieldName . $errorMsg);
}
/**
 * 输出结果：string(26) "年龄不能大于18周岁"
 */
```
> 注意：验证器组件的验证顺序是按照添加验证规则时的 `添加字段的先后顺序` 和 `验证规则的先后顺序` 逐个进行验证的，先添加的验证规则不通过则直接返回验证失败，然后就可以获取对应的验证错误信息。例如上述示例中，会优先验证 `name` 字段是否存在。

### 在控制器中封装使用
先定义一个带有 `validateRule` 方法的基础控制器。示例代码如下：
```php
<?php
namespace App\HttpController\Api;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

class BaseController extends Controller
{
    protected function validate(Validate $validate)
    {
        return $validate->validate($this->request()->getRequestParam());
    }

    protected function onRequest(?string $action): ?bool
    {
        $ret = parent::onRequest($action);
        if ($ret === false) {
            return false;
        }
        $v = $this->validateRule($action);
        if ($v) {
            $ret = $this->validate($v);
            if ($ret == false) {
                $this->writeJson(Status::CODE_BAD_REQUEST, null, "{$v->getError()->getField()}@{$v->getError()->getFieldAlias()}:{$v->getError()->getErrorRuleMsg()}");
                return false;
            }
        }
        return true;
    }

    protected function validateRule(?string $action): ?Validate
    {

    }
}
```

然后在需要验证的控制器方法中，我们给对应的 `action` 添加对应的校验规则，即可实现自动校验，这样控制器方法就只需要关注实现逻辑。示例代码如下：
```php
<?php
namespace App\HttpController;

use App\HttpController\Api\BaseController;
use EasySwoole\Validate\Validate;

class Common extends BaseController
{

    function sms()
    {
        $phone = $this->request()->getRequestParam('phone');
    }

    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action) {
            case 'sms':
                {
                    $v->addColumn('phone', '手机号')->required('不能为空')->length(11, '长度错误');
                    $v->addColumn('verifyCode', '验证码')->required('不能为空')->length(4, '长度错误');
                    break;
                }
        }
        return $v;
    }
}
```

然后访问 `http://ip:9501/common/sms`(示例请求地址) 就可以得到参数校验的结果：`{"code":400,"result":null,"msg":"phone@手机号:不能为空"}`

## 方法列表

### 获取验证错误相关信息(`getError()`)

用于获取验证错误(`Error`)的相关信息（验证字段名称、验证字段别名、验证错误信息）。

#### 函数原型
```php
function getError(): ?\EasySwoole\Validate\Error
```

#### 具体使用示例：
```php
<?php
require_once __DIR__ . "/vendor/autoload.php";
$data1 = [
    'age' => 18,
]; // 要验证的数据
$validate1 = new \EasySwoole\Validate\Validate();
$validate1->addColumn('name', '名字')->required('参数不能缺少!'); // 给字段加上验证规则(验证数据中 name 字段不能没有)
$bool = $validate1->validate($data1);
if ($bool) {
    var_dump("验证通过");
} else {
    // 获取验证错误字段的别名
    $fieldAliasName = $validate1->getError()->getFieldAlias(); // 获取验证规则中设置的字段别名 '名字'

    // 获取验证错误字段的名称
    $fieldName = $validate1->getError()->getField(); // 获取验证规则中设置的字段名称 'name'

    // 获取验证错误信息
    $errorMsg = $validate1->getError()->__toString(); // 获取验证错误信息 '参数不能缺少!'

    var_dump($fieldName . '@' . $fieldAliasName . $errorMsg);
}
/**
 * 输出结果：
 * string(30) "name@名字参数不能缺少!"
 */

```

### 给字段添加验证规则(`addColumn()`)

用于给字段添加验证规则。

#### 函数原型

> 组件 `1.1.9` 版本到目前：

```php
public function addColumn(string $name, ?string $alias = null, bool $reset = false): \EasySwoole\Validate\Rule
```
- string $name   字段 key
- string $alias  字段别名
- bool $reset    重置规则

针对 `1.1.8` 之前版本的函数参数说明如下：

> `1.1.0` 版本到 `1.1.8` 版本

```php
public function addColumn(string $name, ?string $alias = null): \EasySwoole\Validate\Rule
```
- string $name  字段 key
- string $alias 字段别名

> `1.0.1` 版本

```php
public function addColumn(string $name,?string $errorMsg = null,?string $alias = null): \EasySwoole\Validate\Rule
```
- string $name     字段 key
- string $errorMsg 验证错误提示信息
- string $alias    别名

> `1.0.0` 版本

```php
public function addColumn(string $name,?string $alias = null,?string $errorMsg = null):EasySwoole\Validate\Rule
```
- string $name     字段 key
- string $alias    别名
- string $errorMsg 错误信息

返回一个 `Rule` 对象可以添加自定义规则。

### 验证数据是否合法(`validate()`)

用于验证数据是否合法。

#### 函数原型：
```php
function validate(array $data)
```


## 验证规则用法说明
以下验证规则中，不设置验证错误时提示消息时，则默认使用组件提供的默认的错误提示信息。

### activeUrl
验证 `url` 是否可以通讯

#### 函数原型
```php
function activeUrl($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'url' => 'https://www.easyswoole.com/'
];
$validate->addColumn('url')->activeUrl();
$bool = $validate->validate($data);
```
    
### alpha
验证给定的参数值是否是字母 即 `[a-zA-Z]`

#### 函数原型
```php
function alpha($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 'easyswoole'
];
$validate->addColumn('param')->alpha();
$bool = $validate->validate($data);
```

### allDigital
验证给定的参数中字符串是否由数字构成

#### 函数原型
```php
function allDigital($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2022
];
$validate->addColumn('param')->allDigital();
$bool = $validate->validate($data);
```


### allowFile
验证给定参数中的文件的 `文件扩展名` 是否是在允许的文件扩展名范围数组内

#### 函数原型
```php
function allowFile(array $type, $isStrict = false, $msg = null)
```
- array $type    允许的文件扩展名范围数组
- bool $isStrict 是否使用严格等于，默认不使用
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'file' => $this->request()->getUploadedFile('file')
];
$validate->addColumn('file')->allowFile(['png','jpg']);
$bool = $validate->validate($data);
```


### allowFileType
验证给定的参数中的文件的 `文件类型` 是否是在允许的文件类型范围数组

#### 函数原型
```php
function allowFileType(array $type, $isStrict = false, $msg = null)
```
- array $type    允许的文件类型范围数组
- bool $isStrict 是否使用严格等于，默认不使用
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'file' => $this->request()->getUploadedFile('file')
];
$validate->addColumn('param')->allowFileType(['image/png','image/jpeg']);
$bool = $validate->validate($data);
```


### alphaNum
验证给定的参数值是否是由字母或数字组成 即 `[a-zA-Z0-9]`

#### 函数原型
```php
function alphaNum($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 'easyswoole2020'
];
$validate->addColumn('param')->alphaNum();
$bool = $validate->validate($data);
```

### alphaDash
验证给定的参数值是否是由字母、数字、下划线或破折号组成 即[a-zA-Z0-9-_]

#### 函数原型
```php
function alphaDash($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 'easyswoole_2020'
];
$validate->addColumn('param')->alphaDash();
$bool = $validate->validate($data);
```

### between
验证给定的参数值是否在 `$min - $max` 之间

#### 函数原型
```php
function between($min, $max, $msg = null)
```
- integer $min 最小值 包含该值
- integer $max 最小值 包含该值
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => '2020'
];
$validate->addColumn('param')->between(2016, 2020);
$bool = $validate->validate($data);
```

### bool
验证给定的参数值是否为布尔值(1/0/true/false)

#### 函数原型
```php
function bool($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1
];
$validate->addColumn('param')->bool();
$bool = $validate->validate($data);
```

### callUserRule
调用自定义验证规则验证数据

#### 函数原型

```php
function callUserRule(\EasySwoole\Validate\Functions\AbstractValidateFunction $rule, $msg = null, ...$args)
```
- \EasySwoole\Validate\Functions\AbstractValidateFunction $rule 为继承了 `\EasySwoole\Validate\Functions\AbstractValidateFunction` 类的自定义验证规则类
- string $msg  验证错误时提示消息
- mixed $args  可选参数

#### 使用示例

先定义一个自定义验证规则类 `CustomValidator` 并且继承了 `\EasySwoole\Validate\ValidateInterface` 接口，具体实现代码如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: XueSi
 * Email: <1592328848@qq.com>
 * Date: 2021/4/15
 * Time: 22:43
 */

namespace App\Utility;


use EasySwoole\Validate\Functions\AbstractValidateFunction;
use EasySwoole\Validate\Validate;

class CustomValidator extends AbstractValidateFunction
{
    /**
     * 返回当前校验规则的名字
     */
    public function name(): string
    {
        return 'mobile';
    }

    /**
     * 校验失败返回false，或者抛出异常，否则返回true
     * @param $itemData
     * @param $arg
     * @param $column
     * @param Validate $validate
     * @return bool
     */
    public function validate($itemData, $arg, $column, Validate $validate): bool
    {
        $regular = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$/';
        if (!preg_match($regular, $itemData)) {
            return false;
        }

        return true;
    }
}
```

调用自定义验证规则类验证数据，具体实现如下：
```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$validate = new \EasySwoole\Validate\Validate();
$data = [
    'mobile' => '13312345678_',
];
$validate->addFunction(new \App\Utility\CustomValidator());
$validate->addColumn('mobile')->callUserRule(new \App\Utility\CustomValidator(), '手机号未通过验证');
$bool = $validate->validate($data);
if ($bool) {
    var_dump("验证通过");
} else {
    // 获取验证错误信息
    $errorMsg = $validate->getError()->__toString();
    var_dump($errorMsg);
}
/**
 * 输出结果：
 * string(24) "手机号未通过验证"
 */
```

> 该方法在 `Validate 2.0.0` 版本之前的组件函数原型为：

```php
function callUserRule(\EasySwoole\Validate\ValidateInterface $rule, $msg = null, ...$args)
```
- \EasySwoole\Validate\ValidateInterface $rule 为实现了 `\EasySwoole\Validate\ValidateInterface` 接口的自定义验证规则类
- string $msg  验证错误时提示消息
- mixed $args  可选参数


使用示例如下：

先定义一个自定义验证规则类 `CustomValidator` 并且实现 `\EasySwoole\Validate\ValidateInterface` 接口，具体实现代码如下：

```php
<?php
namespace App\Utility;
use EasySwoole\Spl\SplArray;
use EasySwoole\Validate\ValidateInterface;

class CustomValidator implements ValidateInterface
{
    /**
     * 返回当前校验规则的名字
     * @return string
     */
    public function name(): string
    {
        return 'mobile';
    }

    /**
     * 检验失败返回错误信息即可
     * @param SplArray $spl
     * @param $column
     * @param mixed ...$args
     * @return string|null
     */
    public function validate(SplArray $spl, $column, ...$args): ?string
    {
        $regular = '/^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$/';
        if (!preg_match($regular, $spl->get($column))) {
            return '手机号验证未通过';
        }
        return null;
    }
}
```

调用自定义验证规则类验证数据，具体实现如下：
```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$validate = new \EasySwoole\Validate\Validate();
$data = [
    'mobile' => '13312345678_',
];
$validate->addColumn('mobile')->callUserRule(new \App\Utility\CustomValidator());
$bool = $validate->validate($data);
if ($bool) {
    var_dump("验证通过");
} else {
    // 获取验证错误信息
    $errorMsg = $validate->getError()->__toString();
    var_dump($errorMsg);
}
/**
 * 输出结果：
 * string(24) "手机号验证未通过"
 */
```

### decimal
验证给定的参数值是否合格的小数

#### 函数原型
```php
function decimal(?int $precision = null, $msg = null)
```
- integer $precision 规定小数点后位数。默认参数为`null`，表示不规定小数点后位数
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1.1
];
$validate->addColumn('param')->decimal();
$bool = $validate->validate($data);
```

### dateBefore
验证给定参数的日期是否在某日期之前

#### 函数原型
```php
function dateBefore(?string $date = null, $msg = null)
```
- string $date 需要对比的日期，默认验证日期是否在当天之前
- string $msg  验证错误时提示消息

#### 使用示例
```php
// 验证 param 参数日期是否在当天日期之前
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => '2020-06-28'
];
$validate->addColumn('param')->dateBefore('2021-01-14');
$bool = $validate->validate($data);


// 验证 param 参数日期是否在 2021-01-14 之前
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => '2020-06-28'
];
$validate->addColumn('param')->dateBefore('2021-01-14');
$bool = $validate->validate($data);
```

### dateAfter
验证给定参数的日期是否在某日期之后

#### 函数原型
```php
function dateAfter(?string $date = null, $msg = null)
```
- string $date 需要对比的日期，默认验证日期是否在当天之后
- string $msg  验证错误时提示消息

#### 使用示例
```php
// 验证 param 参数日期是否在当天日期之后
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => '2020-06-28'
];
$validate->addColumn('param')->dateAfter('2021-01-14');
$bool = $validate->validate($data);


// 验证 param 参数日期是否在 2021-01-14 之后
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => '2021-06-28'
];
$validate->addColumn('param')->dateAfter('2021-01-14');
$bool = $validate->validate($data);
```

### equal
验证给定参数的值与某个值是否相等

#### 函数原型
```php
function equal($compare, bool $strict = false, $msg = null)
```
- mixed $compare 要判断的某个值
- bool $strict   是否使用严格等于，默认不使用
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020
];
$validate->addColumn('param')->equal(2020);
$bool = $validate->validate($data);
```

### different
验证给定参数的值与某个值是否不相等

#### 函数原型
```php
function different($compare, bool $strict = false, $msg = null)
```
- mixed $compare 要判断的某个值
- bool $strict   是否使用严格等于，默认不使用
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020
];
$validate->addColumn('param')->different(2021);
$bool = $validate->validate($data);
```


### equalWithColumn
验证给定参数中的值与给定参数中的某列值是否相等

#### 函数原型
```php
function equalWithColumn($fieldName, bool $strict = false, $msg = null)
```
- string $fieldName 给定参数中的某列的字段名
- bool $strict      是否使用严格等于，默认不使用
- string $msg       验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020,
    'test'  => 2020
];
$validate->addColumn('param')->equalWithColumn('test');
$bool = $validate->validate($data);
```


### differentWithColumn
验证给定参数中的值与给定参数中的某列值是否不相等

#### 函数原型
```php
function differentWithColumn($fieldName, bool $strict = false, $msg = null)
```
- string $fieldName 给定参数中的某列的字段名
- bool $strict      是否使用严格等于，默认不使用
- string $msg       验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020,
    'test'  => 2021
];
$validate->addColumn('param')->differentWithColumn('test');
$bool = $validate->validate($data);
```

### float
验证给定参数中的值是否是一个浮点数

#### 函数原型
```php
function float($msg = null)
```
- string $msg       验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020.1
];
$validate->addColumn('param')->float();
$bool = $validate->validate($data);
```

### func
调用自定义的闭包验证数据，闭包中返回 `false` 视为验证失败，返回 `true` 视为验证通过。

#### 函数原型
```php
function func(callable $func, $msg = null)
```
- callable $func 自定义闭包类型
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$validate->addColumn('username')->func(function ($data, $name) {
    var_dump($data); // 待验证的数据 ['username' => 'admin']
    var_dump($name); // 验证规则中设置的字段名称 'username'
    return false; // 视为验证失败
}, '用户不存在');

$bool = $validate->validate(['username' => 'admin']);
```


### isArray 
验证给定参数中的值是否是一个数组

#### 函数原型
```php
function isArray($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => ['hi', 'easyswoole']    
];
$validate->addColumn('param')->isArray();
$bool = $validate->validate($data);
```


### inArray
验证给定参数中的值是否在数组中

#### 函数原型
```php
function inArray(array $array, $isStrict = false, $msg = null)
```
- array $array 允许的范围数组
- bool $strict 值是否使用严格等于，默认不使用
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020
];
$validate->addColumn('param')->inArray([2020, 2021]);
$bool = $validate->validate($data);
```


### integer
验证给定参数中的值是否是一个整数值

#### 函数原型
```php
function integer($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020
];
$validate->addColumn('param')->integer();
$bool = $validate->validate($data);
```


### isIp
验证给定参数中的值是否一个有效的IP

#### 函数原型
```php
function isIp($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'ip' => '127.0.0.1'
];
$validate->addColumn('ip')->isIp();
$bool = $validate->validate($data);
```


### notEmpty
验证给定参数中的值是否不为空(注意和 `require()` 规则区分开，`require()` 规则要求是必须存在于给定参数中，详细看下面 `require()` 规则的介绍)。除了 `0/'0'/empty($value)为假` 这些值被验证为不为空(验证通过)，其他都被验证为空(验证错误)。 

#### 函数原型
```php
function notEmpty($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => ''
];
$validate->addColumn('param')->notEmpty();
$bool = $validate->validate($data);
```

### numeric
验证给定参数中的值是否是一个数字值

#### 函数原型
```php
function numeric($msg = null)
```
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2020
];
$validate->addColumn('param')->numeric();
$bool = $validate->validate($data);
```


### notInArray
验证给定参数中的值是否不在数组中

#### 函数原型
```php
function notInArray(array $array, $isStrict = false, $msg = null)
```
- array $array 值不允许出现的范围数组
- bool $strict 值是否使用严格等于，默认不使用
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2022
];
$validate->addColumn('param')->notInArray([2020, 2021]);
$bool = $validate->validate($data);
```

### length
验证给定参数中的 `数组` 或 `字符串` 或者 `文件` 的大小是否与规定的大小值一致

#### 函数原型
```php
function length(int $len, $msg = null)
```
- int $len    规定的长度大小值
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'string' => 2022,
    'array'  => [0, 1, 2],
    'file'   => $this->request()->getUploadedFile('file')
];
$validate->addColumn('string')->length(4);
$validate->addColumn('array')->length(3);
$validate->addColumn('file')->length(4); // 此处 length 为文件的 size
$bool = $validate->validate($data);
```

### lengthMax
验证给定参数中的 `数组` 或 `字符串` 或者 `文件` 的大小是否 `超出` 规定的大小值，最大不能超过这个值。

#### 函数原型
```php
function lengthMax(int $lengthMax, $msg = null)
```
- int $lengthMax 规定的最大长度大小值
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'string' => 2022,
    'array'  => [0, 1, 2],
    'file'   => $this->request()->getUploadedFile('file')
];
$validate->addColumn('string')->lengthMax(4);
$validate->addColumn('array')->lengthMax(3);
$validate->addColumn('file')->lengthMax(4); // 此处 length 为文件的 size
$bool = $validate->validate($data);
```


### lengthMin
验证给定参数中的 `数组` 或 `字符串` 或者 `文件` 的大小是否 `达到` 规定的最小长度大小值，最小也不能低于这个值。

#### 函数原型
```php
function lengthMin(int $lengthMin, $msg = null)
```
- int $lengthMax 规定的最小长度大小值
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'string' => 2022,
    'array'  => [0, 1, 2],
    'file'   => $this->request()->getUploadedFile('file')
];
$validate->addColumn('string')->lengthMin(4);
$validate->addColumn('array')->lengthMin(3);
$validate->addColumn('file')->lengthMin(4); // 此处 length 为文件的 size
$bool = $validate->validate($data);
```


### betweenLen
验证给定参数中的 `数组` 或 `字符串` 或者 `文件` 的大小是否在一个区间内

#### 函数原型
```php
function betweenLen(int $min, int $max, $msg = null)
```
- int $min    最小值 包含该值
- int $max    最大值 包含该值
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'string' => 2022,
    'array'  => [0, 1, 2],
    'file'   => $this->request()->getUploadedFile('file')
];
$validate->addColumn('string')->betweenLen(1, 4);
$validate->addColumn('array')->betweenLen(1, 4);
$validate->addColumn('file')->betweenLen(1, 4); // 此处length为文件的size
$bool = $validate->validate($data);
```


### max
验证给定参数中的值 `不大于` 某个值(相等视为通过)

#### 函数原型
```php
max(int $max, ?string $msg = null): Rule
```
- int $max    需要对比的某个值
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2022
];
$validate->addColumn('param')->max(2022);
$bool = $validate->validate($data);
```


### min
验证给定参数中的值 `不小于` 某个值(相等视为通过)

#### 函数原型
```php
function min(int $min, ?string $msg = null): Rule
```
- int $min    需要对比的某个值
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2022
];
$validate->addColumn('param')->min(2022);
$bool = $validate->validate($data);
```


### money
验证给定参数中的值是否一个合法的金额

#### 函数原型
```php
function money(?int $precision = null, string $msg = null): Rule
```
- int $precision 规定小数点后的位数，默认不规定
- string $msg    验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 2022.22
];
$validate->addColumn('param')->money();
$bool = $validate->validate($data);
```


### regex
验证给定参数中的值是否匹配某个正则表达式

#### 函数原型
```php
function regex($reg, $msg = null)
```
- string $reg 需要匹配的正则表达式
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 'easyswoole'
];
$validate->addColumn('param')->regex('/^[a-zA-Z]+$/');
$bool = $validate->validate($data);
```

### required
验证给定参数中的某字段必须存在，不存在则视为不通过。可用于检测请求参数中是否存在某个字段。与 PHP 中的 `isset` 判断规则一致。(注意和上面的 `notEmpty()` 规则区分开)

#### 函数原型
```php
function required($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
];
$validate->addColumn('param')->required();
$bool = $validate->validate($data);
```

### optional
验证给定参数中的某字段是可选字段，不用必须存在，和上面的 `required()` 验证规则刚好相反。

#### 函数原型
```php
function optional()
```

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
];
$validate->addColumn('param')->optional();
$bool = $validate->validate($data);
```


### timestamp
验证给定参数中的时间戳是否是一个合法的时间戳

#### 函数原型
```php
function timestamp($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1593315393
];
$validate->addColumn('param')->timestamp();
$bool = $validate->validate($data);
```


### timestampBeforeDate
验证给定参数中的时间戳是否是在某个指定日期之前

#### 函数原型
```php
function timestampBeforeDate($date, $msg = null)
```
- string $date 需要对比的日期
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1593315393
];
$validate->addColumn('param')->timestampBeforeDate('2020-06-29');
$bool = $validate->validate($data);
```

### timestampBeforeDate
验证给定参数中的时间戳是否是在某个指定日期之后

#### 函数原型
```php
function timestampAfterDate($date, $msg = null)
```
- string $date 需要对比的日期
- string $msg  验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1593315393
];
$validate->addColumn('param')->timestampAfterDate('2020-06-27');
$bool = $validate->validate($data);
```

### timestampBefore
验证给定参数中的时间戳是否是在某个时间戳之前

#### 函数原型
```php
function timestampBefore($beforeTimestamp, $msg = null)
```
- string|integer $beforeTimestamp 需要对比的时间戳
- string $msg                     验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1593315393
];
$validate->addColumn('param')->timestampBefore(1593315394);
$bool = $validate->validate($data);
```

### timestampAfter
验证给定参数中的时间戳是否是在某个时间戳之后

#### 函数原型
```php
function timestampAfter($afterTimestamp, $msg = null)
```
- string|integer $afterTimestamp 需要对比的时间戳
- string $msg                     验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'param' => 1593315393
];
$validate->addColumn('param')->timestampAfter(1593315392);
$bool = $validate->validate($data);
```


### url
验证给定参数中的值是一个合法的链接

#### 函数原型
```php
function url($msg = null)
```
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'url' => 'https://www.easyswoole.com/'
];
$validate->addColumn('param')->url();
$bool = $validate->validate($data);
```

### lessThanWithColumn
验证字段的值必须小于`with`的字段(仅限`int`)

#### 函数原型
```php
function lessThanWithColumn($fieldName, $msg = null)
```
- string $filedName 需要比较的字段
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'foo' => 10,
    'bar' => 9
];
$validate->addColumn('bar')->lessThanWithColumn('foo');
$bool = $validate->validate($data);
```

### greaterThanWithColumn
验证字段的值必须大于`with`的字段(仅限`int`)

#### 函数原型
```php
function greaterThanWithColumn($fieldName, $msg = null)
```
- string $filedName 需要比较的字段
- string $msg 验证错误时提示消息

#### 使用示例
```php
$validate = new \EasySwoole\Validate\Validate();
$data = [
    'foo' => 10,
    'bar' => 9
];
$validate->addColumn('foo')->greaterThanWithColumn('bar');
$bool = $validate->validate($data);
```
