---
title: easyswoole基本使用-日志
meta:
  - name: description
    content: easyswoole基本使用-日志
  - name: keywords
    content: easyswoole基本使用-日志
---

# 日志

日志可以快速帮助开发者快速定位问题的根源、追踪程序执行的过程、追踪数据变化、数据统计和性能分析等。

## 使用

### log

```php
\EasySwoole\EasySwoole\Logger::getInstance()->log('log level info',\EasySwoole\EasySwoole\Logger::LOG_LEVEL_INFO,'DEBUG');
```

### info

```php
\EasySwoole\EasySwoole\Logger::getInstance()->info('log level info');
```

### waring

```php
\EasySwoole\EasySwoole\Logger::getInstance()->waring('log level waring');
```

### console

```php
\EasySwoole\EasySwoole\Logger::getInstance()->console('console',\EasySwoole\EasySwoole\Logger::LOG_LEVEL_INFO,'DEBUG');
```

### notice

```php
\EasySwoole\EasySwoole\Logger::getInstance()->notice('log level notice');
```

### error

```php
\EasySwoole\EasySwoole\Logger::getInstance()->error('log level error');
```

### event

```php
\EasySwoole\EasySwoole\Logger::getInstance()->onLog()->set('myHook',function ($msg,$logLevel,$category){
    //增加日志写入之后的回调函数
});
```

:::tip
在非框架中使用，例如是单元测试脚本，请执行`EasySwoole\EasySwoole\Core::getInstance()->initialize();` 用于初始化日志。      
在`3.3.7+`，`initialize`事件调用改为：`EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();`。
:::

## 自定义处理器

需要实现`EasySwoole\Log\LoggerInterface`即可：
```php
<?php

namespace App\Log;

use EasySwoole\Log\LoggerInterface;

class LogHandel implements LoggerInterface
{

    private $logDir;

    function __construct(string $logDir = null)
    {
        if(empty($logDir)){
            $logDir = getcwd();
        }
        $this->logDir = $logDir;
    }

    function log(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'debug'):string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir."/log_{$category}.log";
        $str = "自定义日志:[{$date}][{$category}][{$levelStr}] : [{$msg}]\n";
        file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
        return $str;
    }

    function console(?string $msg,int $logLevel = self::LOG_LEVEL_INFO,string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "自定义日志:[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
        fwrite(STDOUT,$temp);
    }

    private function levelMap(int $level)
    {
        switch ($level)
        {
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}
```

在`bootstrap`事件中注入自定义`logger`处理器：
> \EasySwoole\EasySwoole\Logger::getInstance(new \App\Log\LogHandel());

## 日志中心

通常在一些情况下，会把数据往日志中心推送进行数据分析，在`onLog`回调，把日志信息，推送到日志中心即可。

