---
title: easyswoole单例
meta:
  - name: description
    content: 单例模式确保类在全局只能有一个实例，因为它的实例是由自己保存，在类的外部也无法对该类进行实例化。PHP的单例模式是为了避免重复创建对象带来的资源消耗。
  - name: keywords
    content: easyswoole singleton

---

# 单例

单例模式确保类在全局只能有一个实例，因为它的实例是由自己保存，在类的外部也无法对该类进行实例化。
PHP的单例模式是为了避免重复创建对象带来的资源消耗。
实际项目中像数据库查询，日志输出，全局回调，统一校验等模块。这些模块功能单一，但需要多次访问，如果能够全局唯一，多次复用会大大提升性能。

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.4.2
- easyswoole/spl: ^1.1
- easyswoole/utility: ^1.0

## 安装方法

> composer require easyswoole/component

## 仓库地址

[easyswoole/component](https://github.com/easy-swoole/component)

## 基本使用

直接在内中 use \EasySwoole\Component\Singleton;

> 示例:
```php
namespace App\Data;

class MyClass
{
    use \EasySwoole\Component\Singleton;

    private function __construct($arg1, $arg2) {
        //do something
    }
}
$myClass = MyClass::getInstance('a','b');
```