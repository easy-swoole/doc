---
title: easyswooe框架自定义命令
meta:
  - name: description
    content: easyswooe框架自定义命令
  - name: keywords
    content: easyswooe框架自定义命令
---

# 自定义命令
`EasySwoole` 默认自带有 5 个命令，如下所示: 
```
php easyswoole crontab  对定时任务进行管理
php easyswoole install  安装(需要在./vendor/easyswoole/easyswoole/bin/easyswoole 文件中调用)
php easyswoole phpunit  执行单元测试 
php easyswoole process  对自定义进程进行管理
php easyswoole server   启动、停止、重启服务等
php easyswoole task     查看 task 任务的运行状态
```

::: warning 
  默认命令详细内容可查看 [基础管理命令](/QuickStart/command.md)
:::

::: tip
  旧版本(3.4.x 之前版本)框架自定义命令的实现可查看 [自定义命令 3.3.x](/BaseUsage/customCommand_3.3.x.md)
:::

## 定义命令

通过实现 `\EasySwoole\EasySwoole\Command\CommandInterface` 接口，用户可自定义命令：

该接口定义的方法如下：
```php
<?php

namespace EasySwoole\Command\AbstractInterface;

interface CommandInterface
{
    public function commandName(): string;

    public function exec(): ?string;

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface;

    public function desc(): string;
}
```

## 自定义命令使用示例

### 实现自定义命令接口(AbstractInterface)

新建文件 `App/Command/Test.php`，内容如下：
```php
<?php

namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;

class Test implements CommandInterface
{
    public function commandName(): string
    {
        return 'test';
    }

    public function exec(): ?string
    {
        // 获取用户输入的命令参数
        $argv = CommandManager::getInstance()->getOriginArgv();

        if (count($argv) < 3) {
            echo "please input the action param!" . PHP_EOL;
            return null;
        }

        // remove test
        array_shift($argv);

        // 获取 action 参数
        $action = $argv[1];

        // 下面就是对 自定义命令 的一些处理逻辑
        if (!$action) {
            echo "please input the action param!" . PHP_EOL;
            return null;
        }

        // 获取 option 参数
        $optionArr = $argv[2] ?? [];

        switch ($action) {
            case 'echo_string':
                if ($optionArr) {
                    $strValue = explode('=', $optionArr);
                    echo $strValue[1] . PHP_EOL;
                } else {
                    echo 'this is test!' . PHP_EOL;
                }
                break;
            case 'echo_date':
                if ($optionArr) {
                    $strValue = explode('=', $optionArr);
                    echo "now is " . date('Y-m-d H:i:s') . ' ' . $strValue[1] . '!' . PHP_EOL;
                } else {
                    echo "now is " . date('Y-m-d H:i:s') . '!' . PHP_EOL;
                }
                break;
            case 'echo_logo':
                echo Utility::easySwooleLog();
                break;
            default:
                echo "the action {$action} is not existed!" . PHP_EOL;
        }
        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        // 添加 自定义action(action 名称及描述)
        $commandHelp->addAction('echo_string', 'print the string');
        $commandHelp->addAction('echo_date', 'print the date');
        $commandHelp->addAction('echo_logo', 'print the logo');
        // 添加 自定义action 可选参数
        $commandHelp->addActionOpt('--str=str_value', 'the string to be printed ');
        return $commandHelp;
    }

    // 设置自定义命令描述
    public function desc(): string
    {
        return 'this is test command!';
    }
}
```

### 注册自定义命令

在 [bootstrap 事件](/FrameDesign/event/bootstrap.md) 中注册自定义命令。

修改项目根目录的 `bootstrap.php` 文件，添加如下内容实现注册自定义命令:
```php
<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');

\EasySwoole\Command\CommandManager::getInstance()->addCommand(new \App\Command\Test());
```

::: warning 
  `bootstrap 事件` 是 `3.2.5` 新增的事件，它允许用户在框架初始化之前执行自定义事件。
:::

### 执行命令结果
```bash
$ php easyswoole test
please input the action param!

$ php easyswoole test -h
This is test command!
Usage:
  easyswoole test ACTION [--opts ...]
Actions:
  echo_string  print the string
  echo_date    print the date
  echo_logo    print the logo
Options:
  --str=str_value  the string to be printed 

$ php easyswoole test echo_string
this is test!

$ php easyswoole test echo_date
now is 2021-02-23 19:23:19!

$ php easyswoole test echo_logo
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

$ php easyswoole test echo_string --str="hello easyswoole"
hello easyswoole
```
