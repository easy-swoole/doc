---
title: easyswoole基本使用-trigger(追踪器)
meta:
  - name: description
    content: easyswoole基本使用-trigger(追踪器)
  - name: keywords
    content: easyswoole基本使用-trigger(追踪器)
---

# Trigger

`EasySwoole\EasySwoole\Trigger`触发器，用于主动触发错误或者异常而不中断程序继续执行。

## 使用

### 拦截异常并记录

比如：在控制器的`OnException`中：
```php
protected function onException(\Throwable $throwable): void
{
    //拦截错误进日志,使控制器继续运行
    \EasySwoole\EasySwoole\Trigger::getInstance()->throwable($throwable);
    $this->writeJson(\EasySwoole\Http\Message\Status::CODE_INTERNAL_SERVER_ERROR, null, $throwable->getMessage());
}
```

### 直接记录

```php
\EasySwoole\EasySwoole\Trigger::getInstance()->error('test error');
```

### 回调接管注册

通常出现重大异常（支付失败等）需要进行报警处理，在全局的`mainServerCreate`事件中进行注册：

```php
\EasySwoole\EasySwoole\Trigger::getInstance()->onException()->set('notify',function (\Throwable $throwable){
    // 自行实现通知代码
});

\EasySwoole\EasySwoole\Trigger::getInstance()->onError()->set('notify',function ($msg){
    // 自行实现通知代码
});
```

## 自定义处理类

需要开发者实现`EasySwoole\Trigger\TriggerInterface`：
```php
<?php

namespace App\Exception;

use EasySwoole\EasySwoole\Logger;
use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

class TriggerHandel implements TriggerInterface
{
    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        Logger::getInstance()->console('这是自定义输出的错误:'.$msg);
        // TODO: Implement error() method.
    }

    public function throwable(\Throwable $throwable)
    {
        Logger::getInstance()->console('这是自定义输出的异常:'.$throwable->getMessage());
        // TODO: Implement throwable() method.
    }
}
```

在`bootstrap`事件中注入自定义`trigger`处理器：
> \EasySwoole\EasySwoole\Trigger::getInstance(new \App\Exception\TriggerHandel());