---
title: easyswoole IOC依赖注入
meta:
  - name: description
    content: easyswoole IOC依赖注入
  - name: keywords
    content: easyswoole IOC依赖注入
---


# 依赖注入

Dependency Injection  依赖注入

`EasySwoole` 实现了简单版的 `IOC 容器`，使用 `IOC 容器` 可以很方便地存储/获取资源，实现解耦。

使用依赖注入，最重要的一点好处就是有效地分离了对象和它所需要的外部资源，使得它们松散耦合，有利于功能复用，更重要的是使得程序的整个体系结构变得非常灵活。

在我们的日常开发中，创建对象的操作随处可见，以至于对其十分熟悉的同时又感觉十分繁琐，每次需要对象都需要亲手将其 `new` 出来，甚至某些情况下由于坏编程习惯还会造成对象无法被回收，这是相当糟糕的。但更为严重的是，我们一直倡导的松耦合、少入侵原则，这种情况下变得一无是处。于是前辈们开始谋求改变这种编程陋习，考虑如何使用编码更加解耦合，由此而来的解决方案是面向接口的编程。

::: tip
注意：在服务启动后，对 `IOC容器` 的获取/注入仅限当前进程有效。不对其他 `worker` 进程产生影响。
:::

## 方法列表

### getInstance
用于获取依赖注入组件 `IoC 容器` 对象。

使用示例：
```php
$di = \EasySwoole\Component\Di::getInstance();
```

### set
用于向 `IoC 容器` 中注入内容。

函数原型：set($key, $obj, ...$arg): void

- $key: 键名。

- $obj: 要注入的内容。支持注入对象名、对象实例、闭包、资源、字符串等各种常见变量。

- $arg: 若注入的内容为 `is_callable` 类型，则可以设置该参数以供 `callable` 执行时传入。

使用示例：
```php
$di->set('test', new TestClass());
$di->set('test', TestClass::class);

// set 的时候储存的是[类名, 方法名]的数组，需要自己手动调用 call_user_func() 执行（不要被错误与异常章节的 demo 误解为会自动执行)
$di->set('test', [TestClass::class,'testFunction']);

// set 的时候传递了类名，get 的时候才去 new 对象，并且将可变变量传递进构造函数，返回实例化后的对象
$di->set('test', TestClass::class, $arg_one, $arg_tow);
```


::: warning 
 `Di` 的 `set` 方法为懒惰加载模式，若 `set` 一个对象名或者闭包，则该对象不会马上被创建。
:::

### get
用户获取 `IoC 容器` 中某个注入的内容。

函数原型：get($key)

- $key: 调用 `set` 方法时设置的键名。

使用示例：
```php
$val = $di->get('test');
```

### delete
用户删除 `IoC 容器` 中某个注入的内容。

函数原型：delete($key): void

- $key: 调用 `set` 方法时设置的键名。

使用示例：
```php
$di->delete('test');
```

### clear
用于清空 `IoC 容器` 的所有内容。

函数原型：clear($key): void

- $key: 调用 `set` 方法时设置的键名。
