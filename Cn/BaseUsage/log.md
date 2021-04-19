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

## 配置说明

`EasySwoole` 在 `3.4.4` 版本中优化了框架默认的日志处理机制，允许用户更加方便地去自定义配置日志处理，可以直接在配置文件 (dev.php/produce.php) 中进行配置。(以后版本也将兼容以下配置特性)

配置包括以下几方面：
- 设置记录日志文件时日志文件存放目录 (dir)，用户可以自己设置日志存放目录(但是一定要保证日志有写入权限)，配置值为 `路径`，默认为 `框架根目录的 Log 目录`。
- 设置记录日志时的日志最低等级 (level)，等级配置值默认为 `\EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG`，等级值支持 `\EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG (0级)`、`\EasySwoole\Log\LoggerInterface::LOG_LEVEL_INFO (1级)`、`\EasySwoole\Log\LoggerInterface::LOG_LEVEL_NOTICE (2级)`、`\EasySwoole\Log\LoggerInterface::LOG_LEVEL_WARNING (3级)`、`\EasySwoole\Log\LoggerInterface::LOG_LEVEL_ERROR (4级)`。例如当我们在配置文件中把日志等级设置为 `\EasySwoole\Log\LoggerInterface::LOG_LEVEL_INFO (1级)` 时，就不会把我们在框架中调用打印小于这个等级的日志记录记录到日志文件中 (比如 `LOG_LEVEL_DEBUG (0级)` 就不会被记录到日志当中了，也不会显示在控制台了)。
- 设置日志处理器 `handler` (handler)，默认使用框架内置 `handler`，用户可以自定义日志类实现 `\EasySwoole\Log\LoggerInterface` 接口，来处理记录日志。配置值为 `自定义处理类名`，默认为 `\EasySwoole\Log\Logger`。具体自定义实现日志处理器可看下文。
- 设置记录日志到日志文件时是否在控制台打印日志 (logConsole)。配置值为 `boolean` 值，默认为 `true`，即开启。
- 设置是否开启在控制台打印日志 (displayConsole)。配置值为 `boolean` 值，默认为 `true`，即开启。
- 设置打印日志时忽略哪些分类的日志不进行记录 (ignoreCategory) 。配置值为 `array` 类型值，默认为 `null` (即不忽略任何分类的日志，任何分类的日志都进行在控制台显示打印并记录到文件)，配置忽略分类值支持 `debug`、`info`、`notice`、`warning`、`error` 作为配置值 `array` 中的可选值。例如：设置为 ```['debug', 'notice']``` 时，即当我们在框架中使用下面列举的使用日志的方法时，调用 `debug` 和 `notice` 方法记录日志时，不会把 `debug` 和 `notice` 分类的日志在控制台显示，也不会记录到日志文件中。

下面为配置文件中配置示例：

```php
<?php

use EasySwoole\Log\LoggerInterface;

return [
    // ... 这里省略
    'MAIN_SERVER' => [
        // ... 这里省略
    ],
    "LOG" => [
        // 设置记录日志文件时日志文件存放目录
        'dir' => null,
        // 设置记录日志时的日志最低等级，低于此等级的日志不进行记录和显示
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        // 设置日志处理器 `handler` (handler)
        'handler' => null,
        // 设置开启在记录日志到日志文件时在控制台打印日志
        'logConsole' => true,
        // 设置开启在控制台显示日志
        'displayConsole'=>true,
        // 设置打印日志时忽略哪些分类的日志不进行记录
        'ignoreCategory' => []
    ],
    // ... 这里省略
];
```

::: tip
  以上 `level` 和 `ignoreCategory` 的设置，更加方便用户在正式上线项目时，屏蔽那些在开发阶段的调试日志不进行记录和显示。当然对于 `PHP 异常错误等级` 的等级设置（即 `error_reporting()`)，用户也可以设置，详细请查看 [iniialize 事件中设置ERROR_LEVEL](/FrameDesign/event/initialize.md)。
:::

> 注意：在 `EasySwoole 3.4.3 版本中`，仅支持对上述 `dir`、`level`、`handler` 的配置。而在 `3.4.2` 之前版本中，仅支持对上述 `dir` 的配置。

## 日志使用

以下方法可以在框架的 `boostrap` 事件之后的任意位置进行调用。调用之前请先看下文注意事项。

:::tip
  在非框架中使用，例如是单元测试脚本，请执行 `\EasySwoole\EasySwoole\Core::getInstance()->initialize();` 用于初始化日志。
  在 `EasySwoole 3.3.7` 之前版本中，`initialize` 事件调用为：`EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();`。
:::

### log 记录显示日志

```php
// 打印和记录 `DEBUG` 等级、`debug` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->log('record level:DEBUG-category:debug log info',\EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG,'debug');
### [旧版本说明] 注意：当找不到 `\EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG` 常量，请查看是否为 `\EasySwoole\EasySwoole\Logger::LOG_LEVEL_INFO` 


// 打印和记录 `INFO` 等级、`info` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->log('record level:INFO-category:info log info',\EasySwoole\Log\LoggerInterface::LOG_LEVEL_INFO,'info');

// 打印和记录 `NOTICE` 等级、`notice` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->log('record level:NOTICE-category:notice log info',\EasySwoole\Log\LoggerInterface::LOG_LEVEL_NOTICE,'notice');

// 打印和记录 `WARNING` 等级、`warning` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->log('record level:WARNING-category:warning log info',\EasySwoole\Log\LoggerInterface::LOG_LEVEL_WARNING,'warning');

// 打印和记录 `ERROR` 等级、`error` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->log('record level:ERROR-category:error log info',\EasySwoole\Log\LoggerInterface::LOG_LEVEL_ERROR,'error');
```

运行结果：在控制台和日志文件 `Log\log_XxxxXx.log` 中均可看到如下结果：

```bash
// 格式说明: [记录日志时间][分类][等级]:[日志内容]
[2021-03-18 22:52:09][debug][debug]:[record level:DEBUG-category:debug log info]
[2021-03-18 22:52:09][info][info]:[record level:INFO-category:info log info]
[2021-03-18 22:52:09][notice][notice]:[record level:NOTICE-category:notice log info]
[2021-03-18 22:52:09][warning][warning]:[record level:WARNING-category:warning log info]
[2021-03-18 22:52:09][error][error]:[record level:ERROR-category:error log info]
```

### info 日志

```php
// 打印和记录 `INFO` 等级、`info` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->info('record level:INFO-category:info log info');
```

### waring 日志

```php
// 打印和记录 `WANING` 等级、`waring` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->waring('record level:WANING-category:waring log info');
```

### console 日志

```php
// 只在控制台打印 `INFO` 等级、`debug` 分类的日志 (不记录日志文件)
\EasySwoole\EasySwoole\Logger::getInstance()->console('console', \EasySwoole\Log\LoggerInterface::LOG_LEVEL_INFO, 'debug');
```

### notice 日志

```php
// 打印和记录 `NOTICE` 等级、`notice` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->notice('record level:NOTICE-category:notice log info');
```

### error 日志

```php
// 打印和记录 `ERROR` 等级、`error` 分类的日志
\EasySwoole\EasySwoole\Logger::getInstance()->error('record level:ERROR-category:error log info');
```

### event 日志写入后执行回调

使用 `event` 时，请先注册 `Event`。

```php
// 日志写入之后执行
\EasySwoole\EasySwoole\Logger::getInstance()->onLog()->set('myHook', function ($msg, $logLevel, $category) {
    // 增加日志写入之后的回调函数
});
```

## 自定义日志处理器

需要实现 `EasySwoole\Log\LoggerInterface` 即可：

自定义示例如下，新建 `App\Log\LogHandler.php` 文件，编辑内容如下：

```php
<?php

namespace App\Log;

use EasySwoole\Log\LoggerInterface;

class LogHandler implements LoggerInterface
{

    private $logDir;

    function __construct(string $logDir = null)
    {
        if (empty($logDir)) {
            $logDir = getcwd();
        }
        $this->logDir = $logDir;
    }

    function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'): string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir . "/log_{$category}.log";
        $str = "自定义日志:[{$date}][{$category}][{$levelStr}] : [{$msg}]\n";
        file_put_contents($filePath, "{$str}", FILE_APPEND | LOCK_EX);
        return $str;
    }

    function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "自定义日志:[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
        fwrite(STDOUT, $temp);
    }

    private function levelMap(int $level)
    {
        switch ($level) {
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

### 注册自定义日志处理器

> (`EasySwoole 3.4.4` 及以上版本可使用) 方法1. 在配置文件 (`dev.php` / `produce.php`)中注册自定义日志处理器 

```php
<?php

use EasySwoole\Log\LoggerInterface;

return [
    // ... 这里省略
    'MAIN_SERVER' => [
        // ... 这里省略
    ],
    "LOG" => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        // 注册日志处理器 `handler` (handler)
        'handler' =>  new \App\Log\LogHandler(),
        'logConsole' => true,
        'displayConsole'=>true,
        'ignoreCategory' => []
    ],
    // ... 这里省略
];
```

> (`EasySwoole 3.4.x+` 版本可使用) 方法2. 在 [`initialize` 事件](/FrameDesign/event/initialize.md) 中注册自定义 `logger` 处理器 

注册示例代码如下：

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        // 注册自定义 `logger` 处理器
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::LOGGER_HANDLER, new \App\Log\LogHandler());
        
        // 或使用如下方式进行注册自定义 `logger` 处理器
        // \EasySwoole\EasySwoole\Logger::getInstance(new \App\Log\LogHandler());
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

::: tip
  注意：针对 `EasySwoole 3.4.x` 之前版本，请在 `initialize` 事件中，使用 `\EasySwoole\EasySwoole\Logger::getInstance(new \App\Log\LogHandler());` 方式注册自定义日志处理器。
:::

## 日志中心

通常在一些情况下，会把数据往日志中心推送进行数据分析，在 `onLog` 回调，把日志信息，推送到日志中心即可。

