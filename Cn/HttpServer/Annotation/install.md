---
title: easyswoole注解控制器
meta:
  - name: description
    content: easyswoole注解控制器
  - name: keywords
    content:  easyswoole注解控制器
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