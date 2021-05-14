---
title: easyswoole基础使用-task异步任务
meta:
  - name: description
    content: easyswoole基础使用-task异步任务
  - name: keywords
    content: easyswoole基础使用-task异步任务
---

# Task

`EasySwoole 3.3.0+` 异步任务放弃了 `Swoole` 的原生 `task`，采用独立组件实现。

相对于原生 `Swoole Task`，[easyswoole/task](https://github.com/easy-swoole/task) 组件实现了以下功能：

- 可以投递闭包任务
- 可以在 `TaskWorker` 等其他自定义进程继续投递任务
- 实现任务限流与状态监控  

## 安装 

> composer require easyswoole/task

## 框架中使用

同步调用：

```php
\EasySwoole\EasySwoole\Task\TaskManager::getInstance()->sync(function (){
    echo 'sync';
});
```

异步调用：
```php
\EasySwoole\EasySwoole\Task\TaskManager::getInstance()->async(function () {
    echo 'async';
}, function ($reply, $taskId, $workerIndex) {
    // $reply 返回的执行结果
    // $taskId 任务id
    echo 'async success';
});
```

:::warning
  由于 `php` 本身就不能序列化闭包，该闭包投递是通过反射该闭包函数，获取 `php` 代码直接序列化 `php` 代码，然后直接 `eval` 代码实现的。      
所以投递闭包无法使用外部的对象引用，以及资源句柄，复杂任务请使用任务模板方法。
:::

## 任务模版

### 自定义一个任务模版

```php
<?php

namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;

class CustomTask implements TaskInterface
{
    protected $data;

    public function __construct($data)
    {
        // 保存投递过来的数据
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 执行逻辑
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // 异常处理
    }
}
```

### 如何使用

```php
$task = \EasySwoole\EasySwoole\Task\TaskManager::getInstance();

// 投递异步任务
$task->async(new CustomTask(['user' => 'custom']));

// 投递同步任务
$data = $task->sync(new CustomTask(['user' => 'custom']));
```

### 投递返回值

`easyswoole/task` 组件在 `1.0.8` 及以前版本支持，如下 `4` 个投递返回值：

- `> 0` 投递成功（异步任务专属，返回 `taskId`，同步任务直接返回 `run()` 方法运行之后返回的值)
- `-1` `task` 进程繁忙，投递失败 (已经到达最大运行数量 `maxRunningNum` )
- `-2` 投递数据解包失败，当投递数据传输时数据异常时会报错，此错误为组件底层错误，一般不会出现
- `-3` 任务出错 (该任务执行时出现异常错误，被组件拦截并输出错误)

在 `1.0.9` ~ `1.1.1` 版本，除了支持上述 `4` 个投递返回值，还新增支持了以下 `2` 个投递返回值：

- `-4` 投递的任务数据不合法，一般是投递了不能序列化的数据才会出现。
- `-5` 投递的任务在运行时出错

在最新的版本及以后版本中，又新增支持了以下 `2` 个投递返回值：

- `-6` 投递的任务数据包已过期，一般是 `Task` 进程比较繁忙时才会出现。
- `-7` 投递任务时，任务运行完成后没有任何数据返回。一般是因为执行任务时间过长导致 `UnixSocket` 超时，才会出现。


## 独立使用

该组件可独立使用，代码如下：

```php
<?php

use EasySwoole\Task\Config;
use EasySwoole\Task\Task;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * 配置项中可以修改工作进程数、临时目录，进程名，最大并发执行任务数，异常回调等
 */
$config = new Config();
$task = new Task($config);

// 添加 swoole 服务
$http = new \Swoole\Http\Server("0.0.0.0", 9501);

// 注入 swoole 服务，进行创建 task 进程
$task->attachToServer($http);

// 在 onrequest 事件中调用 task (其他地方也可以，这只是示例)
$http->on("request", function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($task) {
    if (isset($request->get['sync'])) {
        // 同步调用 task
        $ret = $task->sync(function ($taskId, $workerIndex) {
            return "{$taskId}.{$workerIndex}";
        });
        $response->end("sync result " . $ret);
    } else if (isset($request->get['status'])) {
        var_dump($task->status());
    } else {
        // 异步调用 task
        $id = $task->async(function ($taskId, $workerIndex) {
            \co::sleep(1);
            var_dump("async id {$taskId} task run");
        });
        $response->end("async id {$id} ");
    }
});
// 启动服务
$http->start();
```

## 版本强调

框架低版本升级为 `EasySwoole 3.3.0+`，需要手动进行配置修改。  
    
需要删除 `MAIN_SERVER.SETTING.task_worker_num`，`MAIN_SERVER.SETTING.task_enable_coroutine` 配置项。       

请在项目根目录的 `dev.php/produce.php` 的 `MAIN_SERVER` 配置项中，增加 `TASK` 子配置项：

```php
<?php

// 这里省略

return [
    // 这里省略 ...
    
    'MAIN_SERVER' => [
        
        // 这里省略 ...
        
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 15
        ]
    ],
    
    // 这里省略 ...
];
```

## Task管理

**查看所有Task进程的状态**

> php easyswoole task status
