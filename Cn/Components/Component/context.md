---
title: easyswoole 基础使用-协程上下文管理器
meta:
  - name: description
    content: easyswoole 基础使用-协程上下文管理器
  - name: keywords
    content: easyswoole 基础使用-协程上下文管理器
---

# Context上下文管理器

在`Swoole`中，由于多个协程是并发执行的，因此不能使用类静态变量/全局变量保存协程上下文内容。使用局部变量是安全的，因为局部变量的值会自动保存在协程栈中，其他协程访问不到协程的局部变量。
  
因`Swoole`属于常驻内存，在特殊情况下声明变量，需要进行手动释放，释放不及时，会导致非常大的内存开销，使服务宕掉。

`ContextManager`上下文管理器存储变量会自动释放内存，避免开发者不小心而导致的内存增长。

## 原理

- 通过当前协程`id`以`key`来存储该变量。
- 注册`defer`函数。
- 协程退出时，底层自动触发`defer`进行回收。

## 安装

`EasySwoole`默认加载该组件，无须开发者引入。在非`EasySwoole`框架中使用，开发者可自行引入。

> composer require easyswoole/component

##  基础例子
```php
use EasySwoole\Component\Context\ContextManager;
go(function (){
    ContextManager::getInstance()->set('key','key in parent');
    go(function (){
        ContextManager::getInstance()->set('key','key in sub');
        var_dump(ContextManager::getInstance()->get('key')." in");
    });
    \co::sleep(1);
    var_dump(ContextManager::getInstance()->get('key')." out");
});
```
以上利用上下文管理器来实现协程上下文的隔离。

## 自定义处理项

例如，当有一个key，希望在协程环境中，get的时候执行一次创建，在协程退出的时候可以进行回收，就可以注册一个上下文处理项来实现。该场景可以用于协程内数据库短连接管理。

```php
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Component\Context\ContextItemHandlerInterface;

class Handler implements ContextItemHandlerInterface
{

    function onContextCreate()
    {
        $class = new \stdClass();
        $class->time = time();
        return $class;
    }

    function onDestroy($context)
    {
        var_dump($context);
    }
}

ContextManager::getInstance()->registerItemHandler('key',new Handler());

go(function (){
    go(function (){
        ContextManager::getInstance()->get('key');
    });
    \co::sleep(1);
    ContextManager::getInstance()->get('key');
});
```
