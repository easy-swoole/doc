---
title: easyswoole基础使用-自定义进程
meta:
  - name: description
    content: easyswoole基础使用-自定义进程
  - name: keywords
    content: easyswoole基础使用-自定义进程
---

# 自定义进程

`PHP`自带的`pcntl`存在许多不足，不支持重定向标准输入和输出及进程间通信的功能，且容易使用错误。   
`EasySwoole`基于`Swoole`的`Process`模块进行了封装，来创建工作进程，用于处理耗时任务，消息队列，等其它的特殊任务。      
在`EasySwoole`启动时，会自动创建注册的进程，并执行进程指定的逻辑代码，进程意外退出时，会被重新拉起。

## 创建一个自定义进程

需要定义一个进程类继承`EasySwoole\Component\Process\AbstractProcess`。

### 定义进程内执行逻辑回调
```
protected function run($arg)
{
    // TODO: Implement run() method.
    $this->getProcessName(); // 获取注册进程名称

    $this->getProcess(); // 获取进程实例 \Swoole\Process

    $this->getPid(); // 获取当前进程Pid

    $this->getArg(); // 获取注册时传递的参数
}
```

### 进程间通信Pipe回调
```
protected function onPipeReadable(Process $process)
{
    // 该回调可选
    // 当主进程对子进程发送消息的时候 会触发
    $process->read(); // 读取消息
}
```

### 进程间异常回调
```
protected function onException(\Throwable $throwable, ...$args)
{
    // 该回调可选
    // 捕获run方法内抛出的异常
    // 这里可以通过记录异常信息来帮助更加方便的知道出现问题的代码
}
```

### 进程信号回调
```
protected function onSigTerm()
{
    // 当进程接收到 SIGTERM 信号触发该回调
}
```

### 进程意外退出回调
```
protected function onShutDown()
{
    // 该回调可选
    // 进程意外退出 触发此回调
    // 大部分用于清理工作
}
```

### 注册进程
在`EasySwoole`全局的`mainServerCreate`事件中进行进程注册
```php
$processConfig = new \EasySwoole\Component\Process\Config();
$processConfig->setProcessName('CustomProcess');//设置进程名称
$processConfig->setProcessGroup('Custom');//设置进程组名称
$processConfig->setArg(['user' => 'root']);//传参
$processConfig->setRedirectStdinStdout(false);//是否重定向标准io
$processConfig->setPipeType($processConfig::PIPE_TYPE_SOCK_DGRAM);//设置管道类型
$processConfig->setEnableCoroutine(true);//是否自动开启协程
$processConfig->setMaxExitWaitTime(3);//最大退出等待时间
Manager::getInstance()->addProcess(new CustomProcess($processConfig));
```

## 完整示例代码
```php
<?php
namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Process;

class CustomProcess extends AbstractProcess
{
    protected function run($arg)
    {
        // TODO: Implement run() method.
        $this->getProcessName(); // 获取注册进程名称

        $this->getProcess(); // 获取进程实例 \Swoole\Process

        $this->getPid(); // 获取当前进程Pid

        $this->getArg(); // 获取注册时传递的参数
    }

    protected function onPipeReadable(Process $process)
    {
        // 该回调可选
        // 当主进程对子进程发送消息的时候 会触发
        $process->read(); // 读取消息
    }


    protected function onException(\Throwable $throwable, ...$args)
    {
        // 该回调可选
        // 捕获run方法内抛出的异常
        // 这里可以通过记录异常信息来帮助更加方便的知道出现问题的代码
    }


    protected function onShutDown()
    {
        // 该回调可选
        // 进程意外退出 触发此回调
        // 大部分用于清理工作
    }


    protected function onSigTerm()
    {
        // 当进程接收到 SIGTERM 信号触发该回调
    }

}
```

## 进程管理

`EasySwoole`内置对于`Process`的命令行操作，方便开发者友好的去管理`Process`。

可执行`php easyswoole process -h`来查看具体操作。

**显示所有进程**

> php easyswoole process show

**如果想要以`MB`形式显示：**

> php easyswoole process show -d

**杀死指定进程(PID)**

> php easyswoole process kill --pid=PID

**杀死指定进程组(GROUP)**

> php easyswoole process kill --group=GROUP_NAME

**杀死所有进程**

> php easyswoole process killAll

**强制杀死进程**

需要带上`-f`参数，例如：
> php easyswoole process kill --pid=PID -f