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
