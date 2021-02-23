---
title: easyswoole swoole-异步服务器
meta:
  - name: description
    content: easyswoole swoole-异步服务器
  - name: keywords
    content: easyswoole swoole-异步服务器|easyswoole|swoole
---

# 异步服务器(Server)
通过`Swoole\Server`对象,可快速,方便的创建一个网络服务器,支持 `TCP/UDP/unixSocket` 3 种 `socket` 类型,支持 `IPv4/IPv6`,`SSL/TLS 单向双向证书的隧道加密`,由于是异步服务器,创建好之后,需要配置异步回调事件.  

::: warning
只有`server`风格是异步的,在回调函数里,默认开启了`enable_coroutine`,在回调中,为同步写法. 
:::

## Server进程组解析
### swoole Server运行流程图
![Server运行流程图](/Images/Swoole/Server/serverFlow.jpg)

### swoole Server 进程/线程关系图
![Server 进程/线程关系图](/Images/Swoole/Server/serverProcess.jpg)


## master进程
### master主线程
`Master`主线程将在服务开始时监听`Tcp/Udp/unixSocket`端口,管理定时器,以及部分业务逻辑`onStart事件`,  
同时接收客户端连接请求,分发到`Reactor`线程进行处理

### Reactor 线程(多个)
`Reactor`线程将维护客户端连接,对客户端连接进行`接收/发送/关闭`等操作.  
不执行任何php业务逻辑.  
同时`Reactor`线程会将客户端发来的数据缓冲,拼接,拆分成一个完整的请求数据包,交给`worker`进程处理  

::: warning
`master进程`只适用于`SWOOLE_PROCESS`模式
:::

## Worker 进程(多个)
worker进程的作用为:  
- 接收`Reactor`线程调度的客户端数据包,并执行相应的回调函数处理(`onConnect,onReceive,onPacket,onClose`等事件)  
- 给`Reactor`线程调度需要给客户端发送的数据包(`$server->send()`).  
- 给`Task`进程投递需要处理的异步任务数据(`$server->task()`),并接收`Task`进程完成任务后的`onFinish`事件.  
- 默认情况下`Worker`进程有协程环境,可串行处理多个客户端请求数据.  

## Task(Worker)进程
taskWorker进程的作用为:  
- 接收由`worker`进程投递(`$server->task()/$server->taskwait()/$server->taskCo()/$server->taskWaitMulti()`)的任务数据包,并进行处理.  
- 处理完任务数据后,返回给`worker`进程(`onFinish`事件).  
- `taskWorker`进程为同步阻塞模式,但可以通过`task_enable_coroutine`改为异步协程环境,可串行执行多个任务.  

## Manager进程  
`Manager`进程负责创建/回收`Worker/Task`进程  
当`Worker/task`异常退出时,`Manager`进程将记录日志,并且重新创建一个进程.  
当`Worker/task`到达最后处理数时,`Manager`进程将重启这个进程,并重新创建.  


## SWOOLE_PROCESS/SWOOLE_BASE模式
在`swoole server`中,提供了`SWOOLE_PROCESS(默认)/SWOOLE_BASE`运行模式.那么我们该如何选择呢?首先我们需要理解这2种模式的区别.  

### SWOOLE_PROCESS模式
在此模式下,所有tcp客户端都是在主进程进行连接,然后交给`Reactor`线程维护(收发数据),再通过进程通信,调度到不同的`worker`进程,由`worker`进程处理.   
#### 优点
- 连接与数据请求发送分开,使得worker进程均衡处理业务.  
- `Worker`进程发生异常退出时,并不会影响客户端连接(连接由`Reactor`线程维护).  

#### 缺点
`Reactor`线程调度数据到`worker`进程,`worker`进程再响应到`Reactor`线程,存在两次进程通信.可能会影响一点点性能


### SWOOLE_BASE模式
SWOOLE_BASE模式为传统的异步服务器模式,全部的`worker`进程进行端口监听,当有客户端连接进入时,会有一个进程成功建立,其他进程将进入等待建立状态.  
当客户端连接成功后,之后的收发数据,关闭连接事件,都将绑定在该`worker`进程,不会变动.   

::: warning 
此模式下没有`master`进程,每个`worker`进程都需要承担`Reactor`线程+`worker`进程的任务  
此模式下,如果`worker_num=1&关闭了task进程&max_request=0`时,并不会创建`Manager`进程.
:::

#### 优点
- 客户端都由`worker`进程接管,没有进程通信的开销

#### 缺点
- 由于客户端连接都有`worker`进程接管,当一个`worker`进程异常退出后,这个进程处理的所有客户端连接都将断开.  
- `worker`进程没有调度程序,可能会造成1个进程非常繁忙,另一个却是没事做的情况.  
- 当有一个进程出现阻塞函数调用时,将阻塞该进程的所有客户端连接.  