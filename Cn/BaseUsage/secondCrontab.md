---
title: easyswoole基础使用-秒级定时任务
meta:
  - name: description
    content: easyswoole基础使用-秒级定时任务
  - name: keywords
    content: easyswoole基础使用-秒级定时任务
---

# 秒级定时任务

`EasySwoole` 没有直接提供最小粒度为秒级的定时任务，但是可以变相实现。使用 `EasySwoole` 的自定义进程组件 + 协程 Sleep 即可实现，使用方式如下：

## 创建一个秒级定时任务类（自定义进程类）

定义一个自定义进程类继承 `\EasySwoole\Component\Process\AbstractProcess` 父类，如下所示，新建一个文件 `\App\Crontab\SecondCrontab`：
```php
<?php

namespace App\Crontab;

use EasySwoole\Component\Process\AbstractProcess;

class SecondCrontab extends AbstractProcess
{
    protected function run($arg)
    {
        while(1) {
            
            // 这里写执行逻辑
            // to do something.
            
            // 这里表示每秒打印一个日期时间字符串，仅供参考
            var_dump(date('Y-m-d H:i:s'));
            
            // 休息1秒
            \Co::sleep(1);
        }
    }
}
```

## 注册秒级定时任务

在 `EasySwoole` 框架全局的 `mainServerCreate` 事件（即项目根目录的 `EasySwooleEvent.php` 文件的 `mainServerCreate` 方法中）中进行秒级定时任务注册。
```php
<?php

namespace EasySwoole\EasySwoole;

use App\Crontab\SecondCrontab;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        
        ###### 注册秒级定时任务 ######
        $process = new SecondCrontab(new \EasySwoole\Component\Process\Config([
            'enableCoroutine' => true
        ]));
        Manager::getInstance()->addProcess($process);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        
    }
}
```