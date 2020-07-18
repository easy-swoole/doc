---
title: easyswoole基础使用-command
meta:
  - name: description
    content: easyswoole基础使用-command
  - name: keywords
    content: easyswoole基础使用-command
---
# Command

`EasySwoole`提供了命令行操作的功能，`EasySwoole`的命令行由[easyswoole/command](https://github.com/easy-swoole/command)组件来提供。

## 安装

> composer require easyswoole/command


## 命令列表

在`EasySwoole`根目录下执行`php easyswoole`，即可看到所有注册的命令列表。

或者执行 `php easyswoole -h` `php easyswoole --help`

## 帮助

查看某个命令的帮助 `php easyswoole COMMAND -h`

示例：`php easyswoole server -h`

## 自定义命令

需要实现`EasySwoole\Command\AbstractInterface\CommandInterface`接口

### 定义Command名字

在自定义`Command`类中，实现`commandName`方法
```
public function commandName(): string
{
    return 'custom';
}
```

### 定义Command介绍

在自定义`Command`类中，实现`desc`方法
```
public function desc(): string
{
    return 'user custom';
}
```

### 定义方法及参数

在自定义`Command`类中，实现`help`方法
```
public function help(\EasySwoole\Command\AbstractInterface\CommandHelpInterface $commandHelp): \EasySwoole\Command\AbstractInterface\CommandHelpInterface
{
    $commandHelp->addAction('create','create new file');
    $commandHelp->addActionOpt('-y','Is it covered');
    return $commandHelp;
}
```

### 定义执行逻辑

在自定义`Command`类中，实现`exec`方法
```
public function exec(): string
{
    // todo something
    return 'success'
}
```
:::tip
需要返回执行完成后的提示信息
:::

### 注册Command

在全局`bootstrap`事件进行注册
> EasySwoole\Command\CommandManager::getInstance()->addCommand(new Custom());


## 完整示例代码
```php
class Custom implements \EasySwoole\Command\AbstractInterface\CommandInterface
{
    public function commandName(): string
    {
        return 'custom';
    }

    public function desc(): string
    {
        return 'user custom';
    }

    public function exec(): string
    {
        /** 获取原始未变化的argv */
        \EasySwoole\Command\CommandManager::getInstance()->getOriginArgv();

        /**
         * 经过处理的数据
         * 例如：`php easyswoole custom create 1 2 name=test`
         * 获取到的数据为：`['create', 1, 2, 'name'=>'test']`
         */
        $args = \EasySwoole\Command\CommandManager::getInstance()->getArgs();
        var_dump($args);

        /**
         * 获取选项
         * 例如：`php easyswoole custom create -mode=dev -d`
         * 获取到的数据为：`['mode'=>'dev', '-d'=>NULL]`
         */
        $opts = \EasySwoole\Command\CommandManager::getInstance()->getOpts();
        var_dump($opts);

        /**
         * 根据下标或者键来获取值
         * php easyswoole custom create
         */
        $action = \EasySwoole\Command\CommandManager::getInstance()->getArg(0);
        var_dump($action); // create

        /**
         * 根据键来获取选项
         * php easyswoole custom create -mode=dev -d
         */
        $d = \EasySwoole\Command\CommandManager::getInstance()->getOpt('d');
        var_dump($d); // NULL

        /**
         * 检测在args中是否存在该下标或者键
         * php easyswoole custom create -mode=dev -d
         */
        $issetArg = \EasySwoole\Command\CommandManager::getInstance()->issetArg(1);
        var_dump($issetArg); // false

        /**
         * 检测在opts中是否存在该键
         * php easyswoole custom create -mode=dev -d
         */
        $issetOpt = \EasySwoole\Command\CommandManager::getInstance()->issetOpt('d');
        var_dump($issetOpt); // true

        return 'success';
    }

    public function help(\EasySwoole\Command\AbstractInterface\CommandHelpInterface $commandHelp): \EasySwoole\Command\AbstractInterface\CommandHelpInterface
    {
        $commandHelp->addAction('create', 'create new file');
        $commandHelp->addActionOpt('-y', 'Is it covered');
        return $commandHelp;
    }
}
```

## 版本强调

`EasySwoole3.4.x`之前，需要依赖`command`组件`1.0.x`版本。

代码如下：
```php
<?php
namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Test implements CommandInterface
{
    public function commandName(): string
    {
        return 'test';
    }

    public function exec(array $args): ?string
    {
        //打印参数,打印测试值
        var_dump($args);
        echo 'test'.PHP_EOL;
        return null;
    }

    public function help(array $args): ?string
    {
        //输出logo
        $logo = Utility::easySwooleLog();
        return $logo."this is test";
    }
}
```
