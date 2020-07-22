---
title: easyswoole基本使用-phpunit(单元测试)
meta:
  - name: description
    content: easyswoole基本使用-phpunit(单元测试)
  - name: keywords
    content: easyswoole基本使用-phpunit(单元测试)
---

# Phpunit

[Easyswoole/Phpunit](https://github.com/easy-swoole/phpunit) 是对`Phpunit`的协程定制化封装，主要为解决自动协程化入口的问题。并屏蔽了`Swoole ExitException`。

## 安装

> composer require easyswoole/phpunit

## 使用

> ./vendor/bin/co-phpunit tests

或者使用以下方式：

> php easyswoole phpunit tests

默认采用协程容器去执行测试用例，使用非协程采用以下方式：

> php easyswoole phpunit --no-coroutine

注：`test`为测试目录。

## 预处理

`easyswoole/phpunit`支持在项目目录下定义一个`phpunit.php`，用户可以在该文件下进行统一的测试前预处理，其他测试与`phpunit`一致。

## 如何进行单元测试

这里以`ORM`组件为测试演示：

### 连接注册

请在`Easyswoole`全局的`initialize`事件中注册。

```php
public static function initialize()
{
    // TODO: Implement initialize() method.
    date_default_timezone_set('Asia/Shanghai');
    $config = new Config(GlobalConfig::getInstance()->getConf("MYSQL"));
    DbManager::getInstance()->addConnection(new Connection($config));
}
```

### 预处理

请在`EasySwoole`项目根目录下创建`phpunit.php`文件。

```php
<?php
use EasySwoole\EasySwoole\Core;
Core::getInstance()->initialize()->globalInitialize();
```

注：在`3.3.7+`，`initialize`事件调用改为：`EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();`。

### 编写测试用例

```php
namespace Test;
use EasySwoole\Mysqli\QueryBuilder;
use PHPUnit\Framework\TestCase;
use EasySwoole\ORM\DbManager;

class DbTest extends TestCase
{
    function testCon()
    {
        $builder = new QueryBuilder();
        $builder->raw('select version()');
        $ret = DbManager::getInstance()->query($builder,true)->getResult();
        $this->assertArrayHasKey('version()',$ret[0]);
    }
}
```

注：请注册`composer.json`下`Test`命名空间与`tests`目录的映射关系。


### 执行

> ./vendor/bin/co-phpunit tests