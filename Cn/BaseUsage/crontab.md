---
title: easyswoole基础使用-定时任务
meta:
  - name: description
    content: easyswoole基础使用-定时任务
  - name: keywords
    content: easyswoole基础使用-定时任务
---

# 定时任务

开发者执行定时任务会通过 Linux 的 `Crontab` 去实现，不方便去管理。`EasySwoole` 提供了根据 `Linux` 下 `Crontab` 规则的定时任务，最小粒度为1分钟。

::: tip
注意：旧版本（3.5.x 之前版本）的定时任务的使用请查看 [旧版本(3.5.x之前)定时任务](/BaseUsage/crontab3.4.x.md)
:::

## 创建一个定时任务

需要定义一个定时任务类实现 `\EasySwoole\Crontab\JobInterface` 接口。

### 定义执行规则
```php
public function crontabRule(): string
{
    // 定义执行规则 根据Crontab来定义
    return '*/1 * * * *';
}
```

### 定义 Crontab 名称
```php
public function jobName(): string
{
    // 定时任务的名称
    return 'CustomCrontab';
}
```

### 定义执行逻辑
```php
public function run()
{
    // 定时任务的执行逻辑
    
    // 开发者可投递给task异步处理
    TaskManager::getInstance()->async(function (){
        // todo some thing
    });
}
```

### 定义异常捕获
```php
public function onException(\Throwable $throwable)
{
    // 捕获run方法内所抛出的异常
}
```

### 注册 Crontab

在 `EasySwoole` 框架全局的 `mainServerCreate` 事件（即项目根目录的 `EasySwooleEvent.php` 文件的 `mainServerCreate` 方法中）中进行定时任务注册。
```php
public static function mainServerCreate(EventRegister $register)
{
    // 配置定时任务
    $crontabConfig = new \EasySwoole\Crontab\Config();
    
    // 1.设置执行定时任务的 socket 服务的 socket 文件存放的位置，默认值为 当前文件所在目录
    // 这里设置为框架的 Temp 目录
    $crontabConfig->setTempDir(EASYSWOOLE_TEMP_DIR);
    
    // 2.设置执行定时任务的 socket 服务的名称，默认值为 'EasySwoole'
    $crontabConfig->setServerName('EasySwoole');
    
    // 3.设置用来执行定时任务的 worker 进程数，默认值为 3
    $crontabConfig->setWorkerNum(3);
    
    // 4.设置定时任务执行出现异常的异常捕获回调
    $crontabConfig->setOnException(function (\Throwable $throwable) {
        // 定时任务执行发生异常时触发（如果未在定时任务类的 onException 中进行捕获异常则会触发此异常回调）
    });
    
    // 创建定时任务实例
    $crontab = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance($crontabConfig);
    
    // 注册定时任务
    $crontab->register(new \App\Crontab\CustomCrontab());
}
```


## 完整使用示例代码

### 在 EasySwoole 中使用

1.定义定时任务类，新增 `\App\Crontab\CustomCrontab` 文件，文件内容如下：

```php
<?php

namespace App\Crontab;

use EasySwoole\Crontab\JobInterface;

class CustomCrontab implements JobInterface
{
    public function jobName(): string
    {
        // 定时任务的名称
        return 'CustomCrontab';
    }

    public function crontabRule(): string
    {
        // 定义执行规则 根据 Crontab 来定义
        // 这里是每分钟执行 1 次
        return '*/1 * * * *';
    }

    public function run()
    {
        // 定时任务的执行逻辑
        
        // 相当于每分钟打印1次时间戳，这里只是参考示例。
        echo time();
    }

    public function onException(\Throwable $throwable)
    {
        // 捕获 run 方法内所抛出的异常
    }
}
```

2.注册定时任务，在 `EasySwoole` 框架全局的 `mainServerCreate` 事件（即项目根目录的 `EasySwooleEvent.php` 文件的 `mainServerCreate` 方法中）中进行定时任务注册，如下所示：

```php
<?php

namespace EasySwoole\EasySwoole;

use App\Crontab\CustomCrontab;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\Crontab\Crontab;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        ###### 注册一个定时任务 ######
        // 配置定时任务
        $crontabConfig = new \EasySwoole\Crontab\Config();

        // 1.设置执行定时任务的 socket 服务的 socket 文件存放的位置，默认值为 当前文件所在目录
        // 这里设置为框架的 Temp 目录
        $crontabConfig->setTempDir(EASYSWOOLE_TEMP_DIR);

        // 2.设置执行定时任务的 socket 服务的名称，默认值为 'EasySwoole'
        $crontabConfig->setServerName('EasySwoole');

        // 3.设置用来执行定时任务的 worker 进程数，默认值为 3
        $crontabConfig->setWorkerNum(3);

        // 4.设置定时任务执行出现异常的异常捕获回调
        $crontabConfig->setOnException(function (\Throwable $throwable) {
            // 定时任务执行发生异常时触发（如果未在定时任务类的 onException 中进行捕获异常则会触发此异常回调）
        });

        // 创建定时任务实例
        $crontab = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance($crontabConfig);

        // 注册定时任务
        $crontab->register(new CustomCrontab());
    }
}
```

### 在 Swoole 中使用

```php
<?php
use EasySwoole\Crontab\JobInterface;

require_once __DIR__ . '/vendor/autoload.php';

$http = new Swoole\Http\Server('0.0.0.0', 9501);

class JobPerMin implements JobInterface
{
    public function jobName(): string
    {
        return 'JobPerMin';
    }

    public function crontabRule(): string
    {
        return '*/1 * * * *';
    }

    public function run()
    {
        var_dump(time());
        return time();
    }

    public function onException(\Throwable $throwable)
    {
        throw $throwable;
    }
}

// 配置及注册定时任务
$crontab = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance();
$crontab->register(new JobPerMin());
$crontab->attachToServer($http);

$http->on('request', function ($request, $response) use ($crontab) {

    // 在 http 服务中直接触发执行定时任务
    $ret = $crontab->rightNow('JobPerMin');

    $response->header('Content-Type', 'text/plain');
    $response->end('Hello World ' . $ret);
});

$http->start();
```

## Crontab 表达式

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