---
title: easyswoole coroutineRunner coroutine concurrent
meta:
  - name: description
    content: easyswoole coroutine coroutineRunner coroutine concurrent
  - name: keywords
    content: easyswoole coroutine coroutineRunner coroutine concurrent| swoole csp
---


# CoroutineRunner

Coroutine runner is similar to CSP component, but it is more flexible. It can post the tasks of coroutine and limit the maximum number of simultaneous execution, the maximum execution time, success or failure callback

## Simple example

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
        // It will be assumed that this is a curl crawling task. Return crawling results can be obtained externally
        return 'ok';
    });
    $runner->addTask($task);
    $allTask[] = $task;
    $max--;
}

$runner->start(1);// The longest execution time is 1 second, 30 posts are posted in total, and the maximum concurrent execution time is 10, which takes 3 seconds to complete, so some of them will be discarded. See the parameter description list below

foreach($allTask as $key => $task){
    var_dump($task->getResult());
}

```

## Parameter description

### Runner constructor

Two parameters can be received __construct($concurrency = 64,$taskChannelSize = 1024)

- concurrency Maximum number of concurrent processes
- taskChannelSize Deliverable task queue length

### Runner->setOnException

There are two parameters for setting exception callback (\Throwable $e, Task $task) 

### Runner->start

To start the execution of the delivered task process, there is a parameter

- float $waitTime = 30 The maximum execution time. If this time is exceeded, the remaining tasks will be discarded and will not be executed.

### Task constructor

You need a callable parameter to call execution. You can use '$task - > getresult()' to get the return data in the 'closure' and external use '$task - > getresult()'

- return Data not equal to false will trigger onsuccess
- return False will trigger onfail

### Task->setOnSuccess

A call parameter is required

[not required] callback after task execution

### Task->setOnFail

A call parameter is required

[not required] callback for task execution failure

### Task->getResult

Get the return data after the call function is executed

