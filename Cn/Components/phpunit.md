---
title: easyswoole基本使用-phpunit(单元测试)
meta:
  - name: description
    content: easyswoole基本使用-phpunit(单元测试)
  - name: keywords
    content: easyswoole基本使用-phpunit(单元测试)
---

# Phpunit 组件

EasySwoole/Phpunit 是对 `Phpunit` 的协程定制化封装，主要为解决自动协程化入口的问题。并屏蔽了 `Swoole ExitException`。

## 组件要求

- php: >= 7.3
- ext-swoole: ^4.4.0
- phpunit/phpunit: ^9.3

## 安装方法

> composer require easyswoole/phpunit

## 仓库地址

[easy-swoole/phpunit](https://github.com/easy-swoole/phpunit)

## 基本使用

> ./vendor/bin/co-phpunit tests

或者使用以下方式：

> php easyswoole phpunit tests

默认采用协程容器去执行测试用例，使用非协程采用以下方式：

> php easyswoole phpunit --no-coroutine

注：`tests` 为要的测试目录，用于放需要进行单元测试的文件。

## 预处理

`easyswoole/phpunit` 支持在项目目录下定义一个 `phpunit.php`，用户可以在该文件中，进行统一的测试前预处理，其他测试与 `phpunit` 一致。

## 如何进行单元测试

这里以 `ORM` 组件为测试演示：

### 连接注册

请在 `EasySwoole` 全局的 `initialize` 事件中注册。

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf("MYSQL"));
        DbManager::getInstance()->addConnection(new Connection($config));
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

### 预处理

请在 `EasySwoole` 项目根目录下创建 `phpunit.php` 文件。

```php
<?php

use EasySwoole\EasySwoole\Core;

require_once __DIR__ . '/vendor/autoload.php';

Core::getInstance()->initialize();
```

注：在 `3.4.x 之前版本`，`initialize` 事件调用为：`EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();`。

### 编写测试用例

新建 `tests\DbTest.php`，编辑内容如下：

```php
<?php

namespace Tests;
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

注：请注册 `composer.json` 下 `Test` 命名空间与 `tests` 目录的映射关系。

映射关系大致如下所示：

```json
{
    "require": {
        "easyswoole/easyswoole": "3.4.4",
        "easyswoole/phpunit": "^1.0",
        "easyswoole/orm": "^1.4"
    },
    "autoload": {
        "psr-4": {
            "App\\": "App/",
            "Tests\\": "tests/"
        }
    }
}
```

### 执行

> ./vendor/bin/co-phpunit tests

或者执行

> php easyswoole phpunit tests/DbTest.php
