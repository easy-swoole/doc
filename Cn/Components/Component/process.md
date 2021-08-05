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
在 `EasySwoole` 全局的 `mainServerCreate` 事件中进行进程注册
```php
$processConfig = new \EasySwoole\Component\Process\Config([
    'processName' => 'CustomProcess', // 设置 自定义进程名称
    'processGroup' => 'Custom', // 设置 自定义进程组名称
    'arg' => [
        'arg1' => 'this is arg1!'
    ], // 【可选参数】设置 注册进程时要传递给自定义进程的参数，可在自定义进程中通过 $this->getArg() 进行获取
    'enableCoroutine' => true, // 设置 自定义进程自动开启协程
]);


\EasySwoole\Component\Process\Manager::getInstance()->addProcess(new CustomProcess($processConfig));
```
::: tip
推荐使用 `\EasySwoole\Component\Process\Manager` 类进行注册自定义进程，注册方式示例代码如上所示。如果您的框架版本过低，不支持 `\EasySwoole\Component\Process\Manager` 类，可使用如下方式进行注册自定义进程: ```\EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer()->addProcess((new TickProcessnew CustomProcess($processConfig));```
:::

::: warning
  注意：用户在注册多个相同配置的自定义进程时，请一定不要复用实例化后的进程对象，而应该重新实例化一个新的进程对象。如果复用了将导致不可预知的结果。正确注册和错误注册的参考示例代码如下：
:::

> 错误的注册示例：

`EasySwooleEvent.php`：

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
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'TestProcess', // 设置 进程名称为 TickProcess
        ]);

        // 【推荐】使用 \EasySwoole\Component\Process\Manager 类注册自定义进程
        $testProcess = new \App\Process\TestProcess($processConfig);
        
        ### !!! 错误原因：把上述实例化得到的自定义进程对象 $testProcess 进行了复用，注册了 2 次，将导致未知错误。
        // 注册进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess);
    }
}
```

> 正确的注册示例：

`EasySwooleEvent.php`：

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
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'TestProcess', // 设置 进程名称为 TickProcess
        ]);

        // 【推荐】使用 \EasySwoole\Component\Process\Manager 类注册自定义进程
        $testProcess1 = new \App\Process\TestProcess($processConfig);
        $testProcess2 = new \App\Process\TestProcess($processConfig);
        
        ### 正确的注册进程的示例：重新使用 new 实例化另外 1 个新的自定义进程对象，然后进行注册
        // 注册进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess1);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess2);
    }
}
```

上文的 `\App\Process\TestProcess` 和下文的 `\App\Process\CustomProcess` 类的代码类似，这里不做重复说明。

## 完整示例代码
### 1. 定义自定义进程类示例
首先，我们定义一个自定义进程类继承 `\EasySwoole\Component\Process\AbstractProcess` 类，示例代码如下：
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
        $processName = $this->getProcessName(); // 获取 注册进程名称
        $swooleProcess = $this->getProcess(); // 获取 注册进程的实例 \Swoole\Process
        $processPid = $this->getPid(); // 获取 当前进程 Pid
        $args = $this->getArg(); // 获取 注册进程时传递的参数

        var_dump('### 开始运行自定义进程 start ###');
        var_dump($processName, $swooleProcess, $processPid, $args);
        var_dump('### 运行自定义进程结束 end ###');
    }

    protected function onPipeReadable(Process $process)
    {
        // 该回调可选
        // 当主进程对子进程发送消息的时候 会触发
        $recvMsgFromMain = $process->read(); // 用于获取主进程给当前进程发送的消息
        var_dump('收到主进程发送的消息: ');
        var_dump($recvMsgFromMain);
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        // 该回调可选
        // 捕获 run 方法内抛出的异常
        // 这里可以通过记录异常信息来帮助更加方便地知道出现问题的代码
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
### 2. 注册进程示例
然后在 `mainServerCreate` 事件中进行注册进程，示例代码如下：
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
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'CustomProcess', // 设置 进程名称为 TickProcess
            'processGroup' => 'Custom', // 设置 进程组名称为 Tick
            'arg' => [
                'arg1' => 'this is arg1!',
            ], // 传递参数到自定义进程中
            'enableCoroutine' => true, // 设置 自定义进程自动开启协程环境
        ]);

        // 【推荐】使用 \EasySwoole\Component\Process\Manager 类注册自定义进程
        $customProcess = (new \App\Process\CustomProcess($processConfig));
        // 【可选操作】把 tickProcess 的 Swoole\Process 注入到 Di 中，方便在后续控制器等业务中给自定义进程传输信息(即实现主进程与自定义进程间通信)
        \EasySwoole\Component\Di::getInstance()->set('customSwooleProcess', $customProcess->getProcess());
        // 注册进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($customProcess);


        /*
        #【针对于低版本不支持 \EasySwoole\Component\Process\Manager 类】可使用 \EasySwoole\EasySwoole\ServerManager 类注册自定义进程
        $customProcess = (new \App\Process\CustomProcess($processConfig))->getProcess();
        // 【可选操作】把 tickProcess 的 Swoole\Process 注入到 Di 中，方便在后续控制器等业务中给自定义进程传输信息(即实现主进程与自定义进程间通信)
        \EasySwoole\Component\Di::getInstance()->set('customSwooleProcess', $customProcess);
        // 注册进程
        \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer()->addProcess($customProcess);
        */
    }
}
```

### 3. 向自定义进程中传递消息
```php
<?php

namespace App\HttpController;

use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    public function index()
    {
        // 获取 Di 中注入的 自定义进程
        $customProcess = Di::getInstance()->get('customSwooleProcess');
        // 向自定义进程中传输信息，会触发自定义进程的 onPipeReadable 回调
        $customProcess->write('this is test!');
    }
}
```

## 进程管理命令说明

`EasySwoole` 内置了对于 `Process` 的命令行操作，方便开发者非常友好地去管理 `Process`。

可执行 `php easyswoole process -h` 来查看具体操作。

**显示所有进程**

> php easyswoole process show

**如果想要以 `MB` 形式显示：**

> php easyswoole process show -d

**杀死指定进程(PID)**

> php easyswoole process kill --pid=PID

**杀死指定进程组(GROUP)**

> php easyswoole process kill --group=GROUP_NAME

**杀死所有进程**

> php easyswoole process killAll

**强制杀死进程**

需要带上 `-f` 参数，例如：
> php easyswoole process kill --pid=PID -f
