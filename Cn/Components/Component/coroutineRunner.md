---
title: easyswoole coroutineRunner 协程并发
meta:
  - name: description
    content: easyswoole协程 coroutineRunner 协程并发
  - name: keywords
    content: easyswoole协程 coroutineRunner 协程并发| swoole csp编程
---


# CoroutineRunner

协程执行器 CoroutineRunner类似于Csp组件，但更加灵活，可以投递协程任务并限制最大同时执行数、最长执行时间、成功或失败回调

## 简单示例

```php
use co;
use EasySwoole\Component\CoroutineRunner\Runner;
use EasySwoole\Component\CoroutineRunner\Task;

$runner = new Runner(10);
$runner->setOnException(function(\Throwable  $e, $task){
    echo $e->getMessage();
    echo PHP_EOL;
});

$max = 30;
$allTask = [];
while($max>0){
    $task = new Task(function() use ($max) {
        echo $max .PHP_EOL;
        co::sleep(1);
        // 将设这是一个curl爬取任务 return 爬取结果 可以在外部获取
        return 'ok';
    });
    $runner->addTask($task);
    $allTask[] = $task;
    $max--;
}

$runner->start(1);// 最长执行1秒  总共投递了30个 最大并发10个 需要3秒执行完，所以会有一部分将被丢弃  看下方参数说明列表

foreach($allTask as $key => $task){
    var_dump($task->getResult());
}

```

## 参数说明

### Runner构造函数

可接收两个参数 __construct($concurrency = 64,$taskChannelSize = 1024)

- concurrency 最大同时执行的协程数量
- taskChannelSize 可投递的task队列长度

### Runner->setOnException

设置异常回调 有两个参数 (\Throwable $e, Task $task) 

### Runner->start

开启已经投递的task协程的执行，有一个参数

- float $waitTime = 30 最长执行时间，如果超过这个时间，剩余的task协程将被丢弃，不再执行。

### Task构造函数

需要一个callable参数，用于调用执行，可以在`闭包内return数据`，外部使用 `$task->getResult()`获取

- return 不等于 false的数据将会触发onSuccess
- return false 将会触发 onFail

### Task->setOnSuccess

需要一个callable参数

【非必选】 task执行完成回调

### Task->setOnFail

需要一个callable参数

【非必选】 task执行失败回调

### Task->getResult

获取call函数执行后return的数据

