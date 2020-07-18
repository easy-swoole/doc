---
title: easyswoole基础使用-task异步任务
meta:
  - name: description
    content: easyswoole基础使用-task异步任务
  - name: keywords
    content: easyswoole基础使用-task异步任务
---

# Task
`EasySwoole3.3.0+`异步任务放弃了`Swoole`的原生`task`，采用独立组件实现实现。
相对于原生`swoole task`，[easyswoole/task](https://github.com/easy-swoole/task)组件实现了以下功能：

- 可以投递闭包任务
- 可以在`TaskWorker`等其他自定义进程继续投递任务
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
\EasySwoole\EasySwoole\Task\TaskManager::getInstance()->async(function (){
    echo 'async';
},function ($reply,$taskId,$workerIndex){
    // $reply 返回的执行结果
    // $taskId 任务id
    echo 'async success';
});
```

:::warning
由于`php`本身就不能序列化闭包，该闭包投递是通过反射该闭包函数，获取`php`代码直接序列化`php`代码，然后直接eval代码实现的。      
所以投递闭包无法使用外部的对象引用，以及资源句柄,复杂任务请使用任务模板方法。
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
$task->async(new CustomTask(['user'=>'custom']));
$data =  $task->sync(new CustomTask(['user'=>'custom']));
```

### 投递返回值

- `>0` 投递成功(异步任务专属,返回`taskId`,同步任务直接返回return值)
- `-1` `task`进程繁忙,投递失败(已经到达最大运行数量`maxRunningNum`)
- `-2` 投递数据解包失败,当投递数据传输时数据异常时会报错,此错误为组件底层错误,一般不会出现
- `-3` 任务出错(该任务执行时出现异常错误,被组件拦截并输出错误)

## 独立使用

该组件可独立使用，代码如下：
```php
use EasySwoole\Task\Config;
use EasySwoole\Task\Task;

/*
    配置项中可以修改工作进程数、临时目录，进程名，最大并发执行任务数，异常回调等
*/
$config = new Config();
$task = new Task($config);
//添加swoole 服务
$http = new \Swoole\Http\Server("0.0.0.0", 9501);
//注入swoole服务,进行创建task进程
$task->attachToServer($http);
//在onrequest事件中调用task(其他地方也可以,这只是示例)
$http->on("request", function (Swoole\Http\Request  $request, $response)use($task){
    if(isset($request->get['sync'])){
        //同步调用task
        $ret = $task->sync(function ($taskId,$workerIndex){
            return "{$taskId}.{$workerIndex}";
        });
        $response->end("sync result ".$ret);
    }else if(isset($request->get['status'])) {
        var_dump($task->status());
    }else{
        //异步调用task
        $id = $task->async(function ($taskId,$workerIndex){
            \co::sleep(1);
            var_dump("async id {$taskId} task run");
        });
        $response->end("async id {$id} ");
    }
});
//启动服务
$http->start();
```

## 版本强调

框架低版本升级为`EasySwoole3.3.0+`，需要手动进行配置修改。      
需要删除`MAIN_SERVER.SETTING.task_worker_num`，`MAIN_SERVER.SETTING.task_enable_coroutine`配置项。       
请在`MAIN_SERVER`配置项中，增加`TASK`子配置项：
```php
[
    'MAIN_SERVER' => [
        'TASK'=>[
            'workerNum'=>4,
            'maxRunningNum'=>128,
            'timeout'=>15
            ],
    ],
];
```

## Task管理

**查看所有Task进程的状态**

> php easyswoole task status