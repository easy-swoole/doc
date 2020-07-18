---
title: easyswoole基础使用-定时任务
meta:
  - name: description
    content: easyswoole基础使用-定时任务
  - name: keywords
    content: easyswoole基础使用-定时任务
---

# 定时任务

开发者执行定时任务会通过Linux的`Crontab`去实现，不方便去管理。`EasySwoole`提供了根据`Linux`下`Crontab`规则的定时任务，最小粒度为1分钟。

##  创建一个定时任务

需要定义一个定时任务类继承`EasySwoole\EasySwoole\Crontab\AbstractCronTask`。

### 定义执行规则
```
public static function getRule(): string
{
    // 定义执行规则 根据Crontab来定义
    return '*/1 * * * *';
}
```

### 定义Crontab名称
```
public static function getTaskName(): string
{
    // 定时任务的名称
    return 'custom crontab';
}
```

### 定义执行逻辑
```
public function run(int $taskId, int $workerIndex)
{
    // 定时任务的执行逻辑

    // 开发者可投递给task异步处理
    TaskManager::getInstance()->async(function (){
        // todo some thing
    });
}
```

### 定义异常捕获
```
public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
{
    // 捕获run方法内所抛出的异常
}
```

### 注册Crontab

在`EasySwoole`全局的`mainServerCreate`事件中进行进程注册

> Crontab::getInstance()->addTask(CustomCrontab::class);

## 完整示例代码
```php
<?php

namespace App\Crontab;


use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;

class CustomCrontab extends AbstractCronTask
{
    public static function getRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        // 定时任务的名称
        return 'CustomCrontab';
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 定时任务的执行逻辑

        // 开发者可投递给task异步处理
        TaskManager::getInstance()->async(function (){
            // todo some thing
        });
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // 捕获run方法内所抛出的异常
    }
}
```

## Crontab表达式

通用表达式：
```bash
    *    *    *    *    *
    -    -    -    -    -
    |    |    |    |    |
    |    |    |    |    |
    |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
    |    |    |    +---------- month (1 - 12)
    |    |    +--------------- day of month (1 - 31)
    |    +-------------------- hour (0 - 23)
    +------------------------- min (0 - 59)
```

特殊表达式：
```bash
@yearly                    每年一次 等同于(0 0 1 1 *) 
@annually                  每年一次 等同于(0 0 1 1 *)
@monthly                   每月一次 等同于(0 0 1 * *) 
@weekly                    每周一次 等同于(0 0 * * 0) 
@daily                     每日一次 等同于(0 0 * * *) 
@hourly                    每小时一次 等同于(0 * * * *)
```

## Crontab管理

`EasySwoole`内置对于`Crontab`的命令行操作，方便开发者友好的去管理`Crontab`。

可执行`php easyswoole crontab -h`来查看具体操作。

**查看所有注册的Crontab**

> php easyswoole crontab show

**停止指定的Crontab**

> php easyswoole crontab stop --name=TASK_NAME

**恢复指定的Crontab**

> php easyswoole crontab resume --name=TASK_NAME

**立即跑一次指定的Crontab**

> php easyswoole crontab run --name=TASK_NAME

## 版本强调

`EasySwoole3.3.0`如何定义：

```php
<?php
namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class TaskOne extends AbstractCronTask
{

    public static function getRule(): string
    {
        // TODO: Implement getRule() method.
        // 定时周期 （每小时）
        return '@hourly';
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        // 定时任务名称
        return 'taskOne';
    }

    static function run(\swoole_server $server, int $taskId, int $fromWorkerId,$flags=null)
    {
        // TODO: Implement run() method.
        // 定时任务处理逻辑
        var_dump('run once per hour');
    }
}
```