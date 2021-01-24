---
title: easyswoole file-watcher 组件
meta:
  - name: description
    content: easyswoole file-watcher 组件
  - name: keywords
    content: easyswoole file-watcher 组件|swoole file-watcher 组件
---

# File-Watcher 组件
用于在 `EasySwoole` 中实现热重启，让开发变得更简便。

## 组件要求
- easyswoole/spl: ^1.3
- easyswoole/component: ^2.2

## 安装方法
> composer require easyswoole/file-watcher

## 仓库地址
[easyswoole/file-watcher](https://github.com/easy-swoole/file-watcher)

## WatchRule
监控目录:
```
$watchRule = new \EasySwoole\FileWatcher\WatchRule(EASYSWOOLE_ROOT."/App");
```

指定忽略目录：
```
/**@var \EasySwoole\FileWatcher\WatchRule $watchRule **/
$watchRule->setIgnorePaths([EASYSWOOLE_ROOT."/App/Api", EASYSWOOLE_ROOT."/App/Admin"]);
```

指定忽略文件：
```
/**@var \EasySwoole\FileWatcher\WatchRule $watchRule **/
$watchRule->setIgnoreFiles([EASYSWOOLE_ROOT."/App/Api/Teacher.php", EASYSWOOLE_ROOT."/App/Admin/Teacher.php"]);
```

指定匹配后缀：
```
/**@var \EasySwoole\FileWatcher\WatchRule $watchRule **/
$watchRule->setType($watchRule::SCAN_TYPE_SUFFIX_MATCH);
//$watchRule->setType($watchRule::SCAN_TYPE_IGNORE_SUFFIX);
$watchRule->setSuffix(['php']);
type为SCAN_TYPE_SUFFIX_MATCH时，只匹配后缀必须在suffix规则数组的文件。 type为SCAN_TYPE_IGNORE_SUFFIX时，会忽略掉后缀在suffix规则数组的文件。
```

## FileWatcher
设置监控程序:
```
$fileWatcher = new \EasySwoole\FileWatcher\FileWatcher();
$fileWatcher->setScannerDriver(\EasySwoole\FileWatcher\Scanner\Inotify::class);
$fileWatcher->setScannerDriver(\EasySwoole\FileWatcher\Scanner\FileScanner::class);
二选一 不调用此方法 存在inotify扩展默认为Inotify::class反之FileScanner::class
```

增加监控规则：
```
/**@var \EasySwoole\FileWatcher\FileWatcher $fileWatcher **/
$fileWatcher->addRule(new \EasySwoole\FileWatcher\WatchRule(__DIR__));
$fileWatcher->addRule(new \EasySwoole\FileWatcher\WatchRule(EASYSWOOLE_ROOT. '/App'));
可进行多次调用 对不同目录设置不同的规则
```

设置异常回调：
```
/**@var \EasySwoole\FileWatcher\FileWatcher $fileWatcher **/
$fileWatcher->setOnException(function (\Throwable $throwable){

});
```

设置检测周期(默认1000ms)：
```
/**@var \EasySwoole\FileWatcher\FileWatcher $fileWatcher **/
$fileWatcher->setCheckInterval(1000);
```

设置触发回调(文件有变化)：
```
/**@var \EasySwoole\FileWatcher\FileWatcher $fileWatcher **/
$fileWatcher->setOnChange(function (array $list, \EasySwoole\FileWatcher\WatchRule $rule){
    // list为变化的文件列表
});
```

启动(swoole服务中使用)：
```
/**@var \EasySwoole\FileWatcher\FileWatcher $fileWatcher **/
/**@var \Swoole\Server $server **/
$fileWatcher->attachServer($server);
```

## EasySwoole 中用于热重启
例如在 `EasySwoole` 开发模式中，我们希望当有代码变动的时候，实现 `Server` 重启，只需要在 `EasySwoole` 的全局事件 `EasySwooleEvent` 中注册一下即可实现。 示例代码如下：
```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $watcher = new FileWatcher();
        $rule = new WatchRule(EASYSWOOLE_ROOT . "/App"); // 设置监控规则和监控目录
        $watcher->addRule($rule);
        $watcher->setOnChange(function () {
            Logger::getInstance()->info('file change ,reload!!!');
            ServerManager::getInstance()->getSwooleServer()->reload();
        });
        $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

注意，`reload` 仅仅针对 `Worker进程` 加载的代码有效。