---
title: easyswoole swoole-回调事件
meta:
  - name: description
    content: easyswoole swoole-回调事件
  - name: keywords
    content: easyswoole swoole-回调事件|easyswoole|swoole
---

## 回调事件   
回调事件是swoole开启异步服务后,通过注册回调事件的函数,来进行处理相应的逻辑.  
### onStart
调用原型:onStart(Swoole\Server $server)  
事件说明:服务开启后的回调,在 `Master` 进程的主线程中被调用.(SWOOLE_BASE模式不存在该回调)  
参数说明:  
- $server Swoole\Server 对象  

事件调用前执行的操作:    
- `manager` 进程创建完成
- `worker` 子进程创建完成
- 监听所有设置监听的`TCP/UDP/unixSocket` 端口,但还未开始 `Accept` 连接和请求
- 监听了定时器  

事件调用后执行的操作:    
- 这个时候客户端已经可以连接服务了,`Reactor`线程开始接收事件.

::: warning
在此回调中,不能调用 `server` 相关函数等操作,因为服务还未就绪.  
因为`onWorkerStart` 和 `onStart` 回调事件是在不同进程中并行执行的,所以不存在先后顺序.   
`worker进程`和`onStart`是同时调用的,所以`onStart`的创建的全局变量,不能在`worker进程`使用
:::
### onShutdown
调用原型:onShutdown(Swoole\Server $server)  
事件说明:`server`正常终止时将调用该回调  
参数说明:  
- $server Swoole\Server 对象  

事件调用前执行的操作:     
- 关闭了所有 `Reactor` 线程,`HeartbeatCheck(心跳检测)` 线程,`UdpRecv(udp接收)` 线程
- 关闭了所有 `Worker 进程`, `Task 进程`,`自定义进程`
- 关闭了所有 `TCP/UDP/UnixSocket`监听端口
- 关闭了主 `Reactor` 线程
事件调用后执行的操作:    
没了,程序已经结束了  
::: warning
`onShutdown`无法调用协程或者异步.    
:::
  
### onWorkerStart
调用原型:onWorkerStart(Swoole\Server  $server, int $workerId)
事件说明:当`worker进程/task worker进程`启动时,将调用该回调    
参数说明:  
- $server  Swoole\Server对象
- $workerId 进程启动的id标识(从0开始).该参数并非进程id,获取进程id,可通过php函数`getmypid()`获取进程id    

事件调用前执行的操作:    
无,start的时候,进程就创建好了
事件调用后执行的操作:     
等待`Reactor`线程接收数据,交给进程处理.
::: warning
可以通过 `$server->taskworker` 属性来判断此进程是 `Worker进程`还是 `Task 进程`   
该事件会根据`worker_num+task_worker_num` 触发多次,可根据`$workerId`识别.   

:::
### onWorkerStop
调用原型:onWorkerStop(Swoole\Server $server, int $workerId)  
事件说明:当`worker`进程将终止时,将调用该回调  
参数说明:
- $server  Swoole\Server对象
- $workerId 进程启动的id标识(从0开始).该参数并非进程id,获取进程id,可通过php函数`getmypid()`获取进程id    

事件调用前执行的操作:  
进程接收到了需要终止的进程信号,准备终止进程.    
事件调用后执行的操作:    
循环调用`onWorkerExit`回调,直到进程终止  
进程终止,重启一个新的进程或者关闭服务(重启的时候,和关闭服务的时候都会调用).   
::: warning
当执行`onWorkerStop`时,不代表进程会直接退出,需要等该`worker/task`进程将所有任务处理完,才会退出  
:::
### onWorkerExit  
调用原型:onWorkerExit(Swoole\Server $server, int $workerId)  
事件说明:当进程清理`event loop`事件时会调用该回调,用于通知`worker`进程尽快退出.    
参数说明:  
- $server  Swoole\Server对象
- $workerId 进程启动的id标识(从0开始).该参数并非进程id,获取进程id,可通过php函数`getmypid()`获取进程id    

事件调用前执行的操作:    
已经调用了`onWorkerStop`  
每次`event loop`都会调用一次`onWorkerExit`,意味着`onWorkerExit`可能执行多次
事件调用后执行的操作:    
每次调用都会尽可能的关闭/移除 `socket连接`,直到该进程没有事件需要处理后退出进程.  

### onConnect
调用原型:onConnect(Swoole\Server $server, int $fd, int $reactorId)  
事件说明:当客户端连接成功后,将会在`worker`进程调用该回调.    
参数说明:  
- $server  Swoole\Server对象
- $fd  客户端连接标识,从0开始
- $reactorId  连接所在的`reactor`线程id  

事件调用前执行的操作:  
客户端tcp握手成功    
事件调用后执行的操作:    
`worker`进程接收事件后.会进入等待状态,等待新的连接,或者客户端发送数据过来进行其他回调.  
::: warning
udp协议只有`onReceive`,没有`onConnect/onClose`事件  
`dispatch_mode = 1/3` 时,`onConnect/onReceive/onClose`可能会在不同的进程触发,因此在这个时候,不能在`onConnect`初始化数据(比如初始化一个mysql连接,到`onReceive`调用).
`dispatch_mode = 1/3` 时,`onConnect/onReceive/onClose`可能会同时触发.  
:::
### onReceive
调用原型:onReceive(Swoole\Server $server, int $fd, int $reactorId, string $data)    
事件说明:当客户端发送tcp数据时,将会在`worker`进程调用该回调.    
参数说明:  
- $server Swoole\Server对象
- $fd 客户端连接标识,从0开始
- $reactorId 连接所在的`reactor`线程id  
- $data 客户端发送的数据 文本/二进制

事件调用前执行的操作:    
客户端已经连接成功,并可能已经和服务端进行了数据交互  
事件调用后执行的操作:    
可能会 关闭连接/继续数据交互   
::: warning
由于`tcp流`的特性,客户端发送的数据可能会粘包,详细可查看[tcp粘包](/Cn/Socket/tcpSticky.md).  
默认情况下,同一个客户端fd将分配到同一个进程,这意味着可以自行拼接数据包并进行处理,但如果`dispatch_mode = 3`时,数据将会分发到不同进程,导致在进程中无法拼接数据包.   
:::  
### onPacket
调用原型:onPacket(Swoole\Server $server, string $data, array $clientInfo)  
事件说明:当`udp`客户端发送数据时,会在`worker`进程调用此回调  
参数说明:  
- $server Swoole\Server对象
- $data 客户端发送的数据 文本/二进制
- $clientInfo udp客户端的信息( address/port/server_socket)   

事件调用前执行的操作:    
没有啥操作,就是服务启动后,udp就可以发送数据
事件调用后执行的操作:    
也没有啥操作,可能还会重复发送数据  
::: warning
服务器同时监听 ` ` 端口时,收到 `TCP` 协议的数据会回调 `onReceive`,收到 `UDP` 数据包回调 `onPacket`.  
:::
### onClose
调用原型:onClose(Swoole\Server $server, int $fd, int $reactorId)  
事件说明:当tcp客户端与服务端之间的连接关闭时,会在会在`worker`进程调用此回调    
参数说明:  
- $server Swoole\Server对象
- $fd 客户端连接标识,从0开始 
- $reactorId 连接所在的`reactor`线程id,如果为-1代表是服务端主动关闭(主动调用`$server->close`方法).   

事件调用前执行的操作:    
客户端已经连接成功,可能还和服务端进行了数据交互.  
事件调用后执行的操作:    
关闭tcp连接.  
可能会有新的客户端连接,或者新的客户端进行数据交互
::: warning
`onClose` 回调函数如果发生了致命错误,会导致`连接泄漏`.连接将出现 `CLOSE_WAIT` 状态. 
当调用`onClose`时,tcp连接并没有关闭.还可以调用` $server->getClientInfo()`方法获取连接信息 

:::
### onTask
调用原型: onTask(Swoole\Server $server, int $taskId, int $srcWorkerId, mixed $data)    
事件说明:当`worker`进程调用`$server->task()`时,将在`task`进程调用该回调.    
参数说明:  
- $server Swoole\Server对象
- $taskId `worker`进程当前投递任务的id.   
- $srcWorkerId 投递task的`worker`进程id
- $data 投递的任务数据

事件调用前执行的操作:    
`worker`进程调用了`$server->task()`方法.  
事件调用后执行的操作:    
task进程切换为繁忙状态,不再接收新的任务,直到任务完成.  
任务完成后,task进程会将处理的结果返回给`worker`进程中的`onFinish`回调事件
#### 当开启`task_enable_coroutine`后
调用原型:onTask(Swoole\Server $server, Swoole\Server\Task $task)
参数说明:  
- $server Swoole\Server对象
- $task Swoole\Server\Task 对象   

例如:  
```php
<?php
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    $task->worker_id;              //`Worker`进程id
    $task->id;                     //`worker`进程当前投递任务的id.
    $task->flags;                  //投递任务的类型,taskwait, task, taskCo, taskWaitMulti
    $task->data;                   //投递的任务数据
    co::sleep(0.2);                //协程 睡眠0.2秒
    $task->finish([123, 'hello']); //完成任务,结束并返回数据(二者选1)
    return [123, 'hello']; //完成任务,结束并返回数据(二者选1)
});
```

### onFinish
调用原型: onFinish(Swoole\Server $server, int $taskId, string $data)  
事件说明:当`worker`进程通过`$server->task()`调用`task`进程处理任务完成后,`worker`进程将调用该回调.    
参数说明:  
- $server Swoole\Server对象
- $taskId `worker`进程投递任务的id.   
- $data `task`进程完成任务后,返回的数据.  

事件调用前执行的操作:    
`task`进程完成了任务,并通知`worker`完成.  
事件调用后执行的操作:    
`task`进程状态改为空闲,等待下一个任务.  
::: warning
如果`task`进程没有`return`或者没有调用`finish`,将不会触发该回调.  
执行`onFinish`回调的进程和调用`$server->task()`的进程一致  
:::
### onPipeMessage
调用原型:onPipeMessage(Swoole\Server $server, int $srcWorkerId, mixed $message)  
事件说明:当有`worker/task`进程调用`$server->sendMessage()`,目标`worker/task`进程会调用该回调.    
参数说明:  
- $server Swoole\Server对象
- $srcWorkerId  调用`$server->sendMessage()`的`worker/task`进程id
- $message  发送的消息内容,可以为php任意类型

事件调用前执行的操作:    
有其他进程调用了`$server->sendMessage()`方法.
事件调用后执行的操作:    
没了.   

### onWorkerError
调用原型: onWorkerError(Swoole\Server $server, int $workerId, int $workerPid, int $exitCode, int $signal)  
事件说明:当`worker/task`进程发生异常退出后,将在`Manager`进程调用此回调  
参数说明:  
- $server Swoole\Server对象
- $workerId 发生异常的进程id
- $workerPid 发生异常的进程的进程id(pid)
- $exitCode 退出的状态码(0-255)
- $signal 进程退出的信号

事件调用前执行的操作:    
进程可能发生了异常/错误,导致了这个进程退出,退出的时候给`Manager`进程发送了这个消息. 
事件调用后执行的操作:    
`Manager`进程处理异常信息,进行相应的重新创建一个进程,并且将错误进入到日志.  

::: warning
常见错误:    
- signal = 11,说明 `Worker` 进程发生了 `segment fault 段错误`,可能触发了底层的 BUG,请收集 `core dump` 信息和 `valgrind` 内存检测日志,向`swoole官方`反馈.
- exit_code = 255,说明 `Worker` 进程发生了 `Fatal Error 致命错误`,请检查php代码,自行解决. 
- signal = 9,说明 `Worker` 被系统强行 Kill -9杀死,如果不是人为请检查代码是否内存泄漏,是否创建了非常大的 `Swoole\Table` 内存模块.
:::  

### onManagerStart
调用原型:onManagerStart(Swoole\Server $server)  
事件说明:当管理进程启动时,将在`manager`进程调用此回调.    
参数说明:  
- $server Swoole\Server对象

事件调用前执行的操作:    
`Worker/Task`进程已创建  
`Master`进程和`Manager`可能为同时创建,`Master`状态不明.  
事件调用后执行的操作:    
`manage`进程将监听所有`task/worker`进程状态,保证一直在运行.如果有退出,则重新创建.  
::: warning
`manager` 进程中可以调用 `$server->sendMessage()`方法向其他`task/worker`进程发送消息.  
在 `SWOOLE_BASE` 模式下,如果配置了 `worker_num/max_request/task_worker_num` 参数,底层将创建 `manager` 进程来管理`task/worker`进程,将会触发 `onManagerStart` 和 `onManagerStop` 事件回调.
:::
### onManagerStop
调用原型:onManagerStop(Swoole\Server $server)   
事件说明:当`task/worker`进程已被 `Manager` 进程回收,`manager` 进程将退出时,将调用此回调.    
参数说明:  
- $server Swoole\Server对象

事件调用前执行的操作:     
`task/worker`进程已经退出完毕.  

事件调用后执行的操作:     
没了

### 事件执行顺序  
- 所有事件回调都在 `$server->start()` 后发生
- 服务器启动成功后,`onStart/onManagerStart/onWorkerStart`事件将会在不同的进程内并发执行
- `onReceive/onConnect/onClose` 在 `Worker` 进程中触发
- `Worker/Task` 进程在 `启动/结束` 时会分别调用一次 `onWorkerStart/onWorkerStop`事件
- `onTask` 事件只会在 `task` 进程中发生
- `onFinish` 事件只会在 `worker` 进程中发生
- 服务器关闭后,最后的事件为`onShutdown`
