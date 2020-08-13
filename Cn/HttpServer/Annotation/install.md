---
title: easyswoole注解控制器 - 安装
meta:
  - name: description
    content: easyswoole注解控制器 - 安装
  - name: keywords
    content:  easyswoole注解控制器 - 安装
---
# 安装
```
composer require easyswoole/http-annotation
```

# 实现原理
注解控制器，完整命名空间为```EasySwoole\HttpAnnotation\AnnotationController```，是继承自
```use EasySwoole\Http\AbstractInterface\Controller```的子类。它重写了父类的```__construct```和```__exec```方法，从而实现的注解支持。
#### __construct
在构造方法中。默认实例化了自带的注解解析器```EasySwoole\HttpAnnotation\Annotation\Parser```，并解析了当前class的注解标签。

#### __exec
该方法是承接```Dispatcher```与控制器实体逻辑的桥梁。在该方法中，注解控制器做了以下事情
- 检查并执行成员变量注解逻辑
- 检查```onRequest```函数注解参数并执行注解参数逻辑校验
- 检查目的action所注解标签并进行参数校验与逻辑校验

# 基础例子
```php
namespace App\HttpController;


use EasySwoole\EasySwoole\Trigger;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

class Index extends AnnotationController
{
    /**
     * @Param(name="name",required="")
     * @Param(name="age",integer="")
     */
    function index()
    {
        $data = $this->request()->getRequestParam();
        $this->response()->write("your name is {$data['name']} and age {$data['age']}");
    }

    function onException(\Throwable $throwable): void
    {
        if($throwable instanceof ParamValidateError){
            $this->response()->withHeader('Content-type','text/html;charset=utf-8');
            $this->response()->write("字段【{$throwable->getValidate()->getError()->getField()}】校验错误");
        }else{
            Trigger::getInstance()->throwable($throwable);
        }
    }
}
```

在以上代码中，会自动对```name```和```age```字段进行校验，当校验失败时，抛出一个异常，校验成功则进入action逻辑。具体请看参数注解章节。