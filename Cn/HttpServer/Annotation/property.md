---
title: easyswoole注解控制器 - 成员属性注解
meta:
  - name: description
    content: easyswoole注解控制器 - 成员属性注解
  - name: keywords
    content:  easyswoole注解控制器 - 成员属性注解
---

# 成员属性注解
我们直接查看以下例子：
```
namespace App\HttpController;


use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\HttpAnnotation\AnnotationTag\Inject;


class Test
{
    public function __construct($a, $b)
    {
    }
}

class Index extends AnnotationController
{
    /**
     * @Di(key="di")
     */
    public $di;
    /**
     * @Context(key="context")
     */
    public $context;

    /**
     * @var \EasySwoole\HttpAnnotation\Tests\TestController\Test $inject
     * @Inject(className="\EasySwoole\HttpAnnotation\Tests\TestController\Test", args={1,{1,2}})
     */
    public $inject;

}
```

### @Di
```@Di```标签，完整命名空间是```EasySwoole\HttpAnnotation\AnnotationTag\Di```，用于在每次请求进来的时候，从IOC中取数据，并赋值到对应的属性中，以上等价于:
```
$this->di = EasySwoole\Component\Di::getInstance()->get(di)
```

### @Context
```@Context```标签，完整命名空间是```EasySwoole\HttpAnnotation\AnnotationTag\Context```，用于在每次请求进来的时候，从上下文管理器中取数据，并赋值到对应的属性中，以上等价于:
```
$this->context = EasySwoole\Component\ContextManager::getInstance()->get(context)
```

### @Inject
```@Inject```标签，完整命令空间是```EasySwoole\HttpAnnotation\AnnotationTag\Inject```，可注入类并且传入构造函数参数，以上等价于: 
```
new className(...$args)
```