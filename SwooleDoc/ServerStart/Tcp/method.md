---
title: easyswoole swoole-服务方法列表
meta:
  - name: description
    content: easyswoole swoole-服务方法列表
  - name: keywords
    content: easyswoole swoole-服务方法列表|easyswoole|swoole
---

## server方法列表
对象命名空间: `Swoole\Server`

   
### __construct
方法原型:__construct($host, $port = null, $mode = null, $sockType = null)  
   
#### 参数介绍
- $host 指定监听的ip地址  
::: warning
ipv4中,`127.0.0.1`表示监听本机地址,`0.0.0.0`表示监听所有地址.  
ipv6中,`::1`表示监听本机地址,`::` 表示监听所有地址
:::
- $port 指定监听的端口
::: warning
如果$sockType 值为 UnixSocket Stream/Dgram,可忽略该参数  
端口小于1024需要root权限才可创建
$port 为0将随机分配一个端口,在new server的时候并不建议使用,你将会不知道它监听的是哪个端口
:::
- $mode 指定运行模式,默认为`SWOOLE_PROCESS`多进程模式
::: warning
建议使用SWOOLE_PROCESS模式(多进程分配模式)
还可以选择SWOOLE_BASE模式(多进程抢占模式)
:::

- $sockType 指定socket类型,例如:SWOOLE_SOCK_TCP
::: warning
可选参数:  
    - SWOOLE_TCP/SWOOLE_SOCK_TCP tcp ipv4 socket  
    - SWOOLE_TCP6/SWOOLE_SOCK_TCP6 tcp ipv6 socket  
    - SWOOLE_UDP/SWOOLE_SOCK_UDP udp ipv4 socket  
    - SWOOLE_UDP6/SWOOLE_SOCK_UDP6 udp ipv6 socket  
    - SWOOLE_UNIX_DGRAM unix socket dgram  
    - SWOOLE_UNIX_STREAM unix socket stream  
:::  

::: warning
配置 $sockType|SWOOLE_SSL 可开启ssl加密,实现https访问,但是需要配置`ssl_key_file`和`ssl_cert_file`
:::


   
#### 示例
```php
<?php
//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 0,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
echo "服务器启动成功\n";
//启动服务器
$server->start(); 
```

   
### listen
新增一个监听端口,swoole服务允许监听多个端口用于不同的服务,例如,你可以监听9501成为http服务,可以新增9502作为websocket服务,再新增一个9503作为tcp服务.      
方法原型:listen($host, $port, $sockType)  
   
#### 参数介绍
- $host,同上
- $port,同上
- $sockType,同上

   
#### 示例
```php
<?php

//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 9501,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

/**
 * @var $port1  \Swoole\Server\Port
 * @var $port2  \Swoole\Server\Port
 * @var $port3  \Swoole\Server\Port
 */
$port1 = $server->listen("0.0.0.0", 9502, SWOOLE_SOCK_TCP); // 添加 TCP
$port2 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP); // 添加 Web Socket
$port3 = $server->listen("0.0.0.0", 9504, SWOOLE_SOCK_UDP); // UDP
//给port1监听的端口单独配置参数
$port1->set([
    'open_length_check' => true,
    'package_length_type' => 'N',
]);
//给port2监听的端口单独配置回调参数
$port2->on('Connect',function ($server,$fd){
    echo "9503 客户端 {$fd} 连接成功\n";
});

//给port3 监听的端口单独配置Packet回调函数
$port3->on('Packet',function ($server,$data,$address){
    echo "udp接收响应数据,地址:{$address},数据:{$data}";
});

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
echo "服务器启动成功\n";
//启动服务器
$server->start(); 
```

   
### addlistener
listen 的别名方法

   
### on
注册 server的回调函数
方法原型:on($eventName, callable $callback)  
   
#### 参数介绍
- $eventName 回调函数名称,忽略大小写
- $callback 回调函数,参数根据回调函数的不同而不同
::: warning
具体的回调函数名称和传参,可查看[事件](/Cn/Swoole/ServerStart/Tcp/events.md) 
::: 

   
#### 示例
```php
<?php

//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 9501);

//监听连接进入事件,当客户端连接成功时,会分配一个fd(自增id),然后会调用这个回调函数
$server->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件,当客户端发送数据到服务器时,会调用这个回调函数
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件,当客户端关闭连接时,会调用这个回调函数
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});

echo "服务器启动成功\n";

//启动服务器
$server->start(); 
```

   
### getCallback
获取当前注册的回调函数闭包对象
方法原型:getCallback($eventName)  
   
#### 参数介绍
- $eventName 回调函数名
   
#### 示例
```php
<?php
//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 9501,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
var_dump($server->getCallback('Close'));

//输出
//object(Closure)#5 (1) {
//  ["parameter"]=>
//  array(2) {
//    ["$server"]=>
//    string(10) "<required>"
//    ["$fd"]=>
//    string(10) "<required>"
//  }
//}
```

   
### set 
设置server启动的不同参数
方法原型:set(array $settings)  
   
#### 参数介绍
- $setting 配置的数组
   
#### 示例
```php
//创建Server对象,监听 127.0.0.1:9501端口
/**
 * @var $server Swoole\Server
 */
$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$server->set([
    'worker_num'    => 4,
    'backlog'       => 128,
    'max_request'   => 50,
]);

```

::: warning 
详细的配置信息可查看[swoole配置](/Cn/Swoole/ServerStart/Tcp/serverSetting.md)
:::

   
### start
启动服务,启动swoole的服务
方法原型:start()  
   
#### 示例
```php
<?php
//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("127.0.0.1", 0,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
echo "服务器启动成功\n";
//启动服务器
$server->start(); 

```

::: warning
调用start方法成功之后,后面的代码将不会再执行,如果启动失败则会抛出相关异常.
:::

   
### send
向客户端发送数据

方法原型:send($fd, $sendData, $serverSocket = null)  
   
#### 参数介绍
- $fd 客户端的fd
- $sendData 发送的内容
- $serverSocket UnixSocket DGRAM 对端发送数据时专用,默认值-1
   
#### 示例
```php
<?php

//tcp监听数据接收事件 send例子
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

// UnixSocket DGRAM 监听事件 send 例子
$server->on("packet", function (Swoole\Server $server, $data, $address){
    $server->send($address['address'], "服务器响应: ".$data, $address['server_socket']);
});

```

::: warning
send发送时是异步的,调用send之后,将不会立即发送数据,而是先存入缓存区,监听可写,然后逐步发送到客户端.  
send方法具有原子性,不同进程同时send,数据不会错乱.  
在发送超过`8k`的数据时,底层会开启 `Worker` 进程的共享内存，将进行一次 Mutex->lock 的操作  
默认最大只可发送`2M`的数据,可通过修改`buffer_output_size`配置项进行修改    
当 `Worker` 进程的 `unixSocket` 缓存区已满时,再发送 `8K` 数据将使用临时文件进行存储
如果向同一个客户端连续发送大量数据,客户端来不及接收会导致 `Socket` 内存缓存区塞满，`Swoole` 底层会立即返回 `false`.用户可以手动保存数据,等待客户端接收完成再发送.(在默认开启`send_yield`的情况下,不会出现这个问题,所以可以忽略.)
:::

   
### sendto
向指定的客户端发送`udp`数据包.  
方法原型:sendto($ip, $port, $sendData, $serverSocket = null)  
   
#### 参数介绍
- $ip  客户端的ip `ipv4/ipv6字符串`
- $port 客户端端口 `1-65535`
- $sendData 需要发送的数据 `字符串/二进制`
- $serverSocket 指定服务器的端口发送`根据swoole监听的udp端口进行选择`
   
#### 示例
```php
<?php
//向192.168.1.200 9502端口的客户端发送一个easyswoole牛逼的字符串。
$server->sendto('192.168.1.200', 9502, "easyswoole牛逼");
//向IPv6客户端发送UDP数据包
$server->sendto('2610:1cff::f14e:92ea:f274:e58f', 9501, "easyswoole牛逼");
```

   
### sendwait
使用同步方法向客户端发送数据
方法原型:sendwait($fd, $sendData)  
   
#### 参数介绍
- $fd
- $sendData
   
#### 示例
```php
<?php
$server->sendwait(1,'easyswoole牛逼');
```
::: warning
当需要连续向客户端发送数据,由于send方法是存异步的,会先存入缓存发送队列,通过此方法,可以不需要经过缓存,直接点对点发送,直到发送成功才会返回. 
`enable_coroutine`开启时(默认开启)不能使用该方法,否则会全进程阻塞
`sendwait` 只能用在 `SWOOLE_BASE` 模式
`sendwait` 只用于本机或内网通信,外网连接请勿使用 `sendwait`
:::

   
### exists
判断客户端是否存在
方法原型:exists($fd)  
   
#### 参数介绍
- $fd 客户端fd
   
#### 示例
```php
<?php
var_dump($server->exist(1));
```

   
### exist
exists 别名

   
### protect
设置客户端的保护状态,为true时不会被心跳线程主动断开
方法原型:protect($fd, $isProtected = null)  
   
#### 参数介绍
- $fd 客户端fd
- $isProtected 是否设置为保护状态

::: warning
当设置了swoole心跳时,当一个连接超过n秒没有和服务端交互数据时,会被心跳线程主动断开连接   
通过此方法设置,将不会主动断开
:::

   
### sendfile
向客户端直接发送一个文件数据
方法原型:sendfile($conn_fd, $filename, $offset = null, $length = null)  
   
#### 参数介绍
参数介绍
   
#### 示例
```php

```

   
### close
主动关闭一个客户端连接
方法原型:close($fd, $reset = false)  
   
#### 参数介绍
- $fd 客户端fd
- $reset  是否强制关闭连接(可能会丢弃还未发送的数据),默认为false
::: warning 
服务器主动调用close方法,也会触发onClose事件.  
close和send一样是异步的,调用close不代表马上关闭,如果需要做关闭之后的操作,请到onClose事件去做.  
:::

   
### confirm
`enable_delay_receive=true`时配合使用,用于监听可读事件(`onReceive`等事件)  
方法原型:confirm($fd)   
   
#### 参数介绍  
- $fd 客户端fd   
   
#### 示例
```php
<?php
//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("0.0.0.0", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$server->set([
    'enable_delay_receive' => true
]);
//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    /**
     * @var $server \Swoole\Server
     */
    echo "客户端 {$fd} 连接成功\n";
    if ($fd % 2 == 1) {
        //只有confirm的fd  服务器才会接受消息
       $result =  $server->confirm($fd);
       var_dump($result);
    }
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: " . $data);
});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
echo "服务器启动成功\n";
//启动服务器
$server->start(); 

```

::: warning
小编没有测试出这个方法的作用,`enable_delay_receive=true`时并不能成功打印连接成数据
:::

   
### pause
停止接受客户端的数据,调用此方法后,将停止接收客户端的数据,但不会影响之前发送的数据,所以将还可能触发onReceive事件.
方法原型:pause($fd)  
   
#### 参数介绍
- $fd 客户端fd
::: warning
只能在 `SWOOLE_PROCESS` 模式下使用.    
:::

   
### resume
恢复数据接收,与 pause 方法成对使用.
方法原型:resume($fd)  
   
#### 参数介绍
- $fd 客户端id


   
### task
投递一个异步任务到`task_worker` 进程池中,调用成功返回taskId(id可能是0),失败返回false  
方法原型:task($data, $workerId = -1, ?callable $finishCallback = null)  
   
#### 参数介绍
- $data 要投递的任务数据,必须是可序列化的php变量
- $workerId 指定要投递的worker进程号(0-task_worker_num-1),为-1则自动投递.(`task_ipc_mode`=3时参数无效)
- $finishCallback 任务完成时候执行的回调函数,如果不设置,则会调用 $sever->on()设置的 `onFinish`事件
   
#### 示例
```php
$server->task($data, -1, function (Swoole\Server $server, $taskId, $data) {
    echo "task完成";
    var_dump($taskId, $data);
});
```

::: warning
task底层使用unixSocket通信,没有io消耗,当workerId为-1时.底层自动根据当前task进程的繁忙状态分配任务,如果全部繁忙,则会轮询投递到各个进程.  
使用`$server->stats()`可获取当前排队的任务状态  
$taskId 范围为`0-42亿`,在当前进程中唯一   
通过配置`task_worker_num`配置项,才会启动task功能  
::: 

   
### taskwait
同步投递一个任务,直到运行结束后返回数据,如果返回false代表投递失败    
方法原型:taskwait($data, $timeout = null, $workerId = null)  
   
#### 参数介绍
- $data  投递的任务数据
- $timeout 超时时间,单位秒,最小粒度为毫秒,当超时,则会直接返回false
- $workerId 指定 task进程id
::: warning
Swoole\Server::finish(),不能使用 taskwait  
task进程不能使用taskwait
taskwait使用unixsocket和共享内存进行通信,在非协程模式下将阻塞.  
在协程模式中,将自动进行协程调度,并不会阻塞其他协程  
通过taskWait,在协程中可实现[csp并发调用](/Cn/Swoole/Coroutine/csp.md)   
:::

   
### taskWaitMulti
并发执行多个task异步任务,该方法将会阻塞当前进程,如果是协程环境,请使用taskCo   
方法原型:taskWaitMulti(array $tasks, $timeout = null)  
   
#### 参数介绍
- $tasks 数字索引数组,遍历数组并逐个投递到task进程
- $timeout 超时时间
   
#### 示例
```php
<?php
//创建Server对象,监听 127.0.0.1:9501端口
$server = new Swoole\Server("0.0.0.0", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$server->set([
    'task_worker_num' => 3
]);
$server->on('Task', function (Swoole\Server $server, int $taskId, int $srcWorkerId, $data) {
    //task任务处理
    var_dump($data);
    return $data;
});

$server->on('Finish', function (Swoole\Server $server, int $taskId, string $data) {
    //task完成事件
});


//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    /**
     * @var $server \Swoole\Server
     */
    echo "客户端 {$fd} 连接成功\n";
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: " . $data);

    $taskArr[] = mt_rand(10000, 99999); //任务1
    $taskArr[] = mt_rand(10000, 99999); //任务2
    $taskArr[] = mt_rand(10000, 99999); //任务3
    var_dump($taskArr);

//等待所有Task结果返回，超时为10s
    $results = $server->taskWaitMulti($taskArr, 3);
    var_dump($results);
    if (!isset($results[0])) {
        echo "任务1执行超时了\n";
    }
    if (isset($results[1])) {
        echo "任务2的执行结果为{$results[1]}\n";
    }
    if (isset($results[2])) {
        echo "任务3的执行结果为{$results[2]}\n";
    }

});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});
echo "服务器启动成功\n";
//启动服务器
$server->start(); 
```
::: warning
最大并发任务不能超过`1024`  
返回一个结果数组,顺序一一对应  
onTask事件必须有return 值,否则整个结果数组将会成false.  
如果task进程数<投递任务数,则实际并发为task进程数,其他多出来的task任务需要等上一批执行完才能够执行. 
:::

   
### taskCo
并发执行多个task,并进行协程调度,功能和`taskWaitMulti`一致.
方法原型:taskCo(array $tasks, $timeout = null)  
   
#### 参数介绍
- $tasks   数字索引数组,遍历数组并逐个投递到task进程
- $timeout 超时时间
   
#### 示例
```php
<?php

$server = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_PROCESS);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $server, $taskId, $workerId, $data) {
    echo "#{$server->worker_id}\tonTask: workerId={$workerId}, taskId=$taskId\n";
    if ($server->worker_id == 1) {
        sleep(1);
    }
    return $data;
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "easyswoole牛逼";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result   = $server->taskCo($tasks, 0.5);
    $response->end('http请求,异步任务完成: ' . var_export($result, true));
});

$server->start();
```

   
### finish
在 task进程中使用,通知worker进程,该任务已经完成.
方法原型:finish($data)  
   
#### 参数介绍
- $data 返回的数据
   
#### 示例
```php
<?php

$server = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_PROCESS);

$server->set([
    'worker_num'      => 1,
    'task_worker_num' => 2,
]);

$server->on('Task', function (Swoole\Server $server, $taskId, $workerId, $data) {
    $server->finish('仙士可牛逼');
    echo "完成\n";
});

$server->on('Finish', function (Swoole\Server $server, int $task_id, string $data) {
    var_dump($data);
});

$server->on('Request', function ($request, $response) use ($server) {
    $tasks[0] = "easyswoole牛逼";
    $tasks[1] = ['data' => 1234, 'code' => 200];
    $result = $server->taskCo($tasks, 0.5);
    $response->end('http请求,异步任务完成: ' . var_export($result, true));
});

$server->start();
```

::: warning
finish 方法可以不调用,等同于 return $data;  
此方法必须在`task`进程的`onTask`事件中才可以使用.
:::

   
### reload
安全的重启所有进程
方法原型:reload(bool $onlyReloadTaskWorker = false)  
   
#### 参数介绍
- $onlyReloadTaskWorker 是否只重启task进程
::: warning
该方法会先给所有进程发送重启信号,进程收到信号之后,会处理完当前的任务,然后进行重启
reload在同一时间只会执行一次,其他reload调用将忽略  
reload不能重启自定义进程  
reload只能重启所有worker进程,在worker进程启动前的文件/数据将不能被重启  
通过向主进程发送 `SIGUSR1` 信号,等同于$server->reload();  
通过向主进程发送 `SIGUSR2` 信号,等同于$server->reload(true);   

:::


   
### shutdown
关闭服务  
方法原型:shutdown()  
::: warning
向主进程发送 `SIGTERM` 信号也可以实现关闭服务  
:::


   
### stop
方法原型:stop($workerId = null, bool $waitEvent = false)  
   
#### 参数介绍
- $workerId 指定退出的workerId,默认为-1(当前worker)
- $waitEvent 是否等待事件处理完再退出,true是,false直接退出

::: warning
如果强制退出,可能会造成一些执行中断影响业务数据.  
退出后将重启一个新的worker进程  
:::
   
### getLastError
获取最近一次操作错误的错误码.  
方法原型:getLastError()  
::: warning
详细错误码解释可查看[swoole错误码](/Cn/Swoole/Other/swooleErrno.md);
:::


   
### heartbeat
主动检测所有连接,并找出已经超过心跳时间的连接.
方法原型:heartbeat(bool $ifCloseConnection = true)  
   
#### 参数介绍
- $ifCloseConnection 是否关闭已经超时的连接
::: warning
 将返回一个包含关闭连接fd的连续数组,调用失败返回false
:::
   
### getClientInfo
获取客户端连接的详细
方法原型:getClientInfo(int $fd, int $extraData, bool $ignoreError = false)  
   
#### 参数介绍
- $fd 客户端fd
- $extraData 扩展信息,保留参数,暂时没卵用
- $ignoreError 是否忽略错误,设置为true,则连接关闭也将返回数据
   
#### 示例
```php
<?php

$server = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_PROCESS);

$server->on('Request', function ($request, $response) use ($server) {
    /**
     * @var $response \Swoole\Http\Response
     */
    var_dump($server->getClientInfo($response->fd));
    $response->end('http请求响应');
});

$server->start();

//array(10) {
//  ["server_port"]=>
//  int(9501)  //来自哪个监听的端口
//  ["server_fd"]=>
//  int(4)   //来自哪个监听的socket
//  ["socket_fd"]=>
//  int(13) //
//  ["socket_type"]=>
//  int(1) 
//  ["remote_port"]=>
//  int(52298) //来自客户端的哪个端口
//  ["remote_ip"]=>
//  string(13) "192.168.159.1"//来自客户端的哪个ip
//  ["reactor_id"]=>
//  int(1)   来自哪个 Reactor 线程
//  ["connect_time"]=>
//  int(1582534381) //客户端连接的时间
//  ["last_time"]=> 
//  int(1582534381) //最后一次收到数据的时间
//  ["close_errno"]=>
//  int(0) 连接关闭的错误码 0为正常
//}
//websocket_status 当连接为websocket时会增加此信息
//uid 使用 bind 绑定了用户 ID 时会增加此信息
//ssl_client_cert  使用 SSL 隧道加密,并且客户端设置了证书时会增加此项信息

```
::: warning
有些参数swoole文档没有,小编也不是很清楚  
当使用 `dispatch_mode` = 1或者3时,考虑到这种数据包分发策略用于无状态服务,当连接断开后相关信息会直接删除,所以 $server->getClientInfo 是获取不到相关的连接信息的。
:::

   
### getClientList 
遍历当前服务所有的客户端连接.   
调用成功将返回一个不超过$findCount条的数组,从小到大排序.    
方法原型:getClientList($startFd, $findCount = null)   
   
#### 参数介绍  
- $startFd 是否指定起始id  
- $findCount 每次取多少条,最大不超过`100`  
   
#### 示例
```php
<?php
//获取所有的连接,并发送 hello   

$startFd = 0;//开始的fd
while (true) {
    $list = $server->getClientList($startFd, 10);//每次只获取10条
    if ($list === false||count($list) === 0) {
        echo "完成\n";//获取完毕则退出
        break;
    }
    $startFd = end($list);//获取最大的fd
    var_dump($list);
    foreach ($list as $fd) {
        $server->send($fd, "hello");
    }
}

```

   
### connection_info
同 `getClientInfo` 方法  

   
### connection_list
同`getClientList`方法  

  
   
### sendMessage
向任意 `Worker` 进程或 `Task` 进程发送消息,在非主进程和管理进程中可调用.  
收到消息的进程会触发 `onPipeMessage` 事件。

方法原型:sendMessage($message, $workerId)  
   
#### 参数介绍
- $message 发送的内容,超过8k将启动内存临时文件
- $workerId  需要发送的进程id(0-worker_num + task_worker_num - 1)
   
#### 示例
```php
<?php
$server = new Swoole\Server("0.0.0.0", 9501);

$server->set([
    'worker_num'      => 4,
    'task_worker_num' => 4,
]);
$server->on('PipeMessage', function ($server, $srcWorkerId, $data) {
    echo "#{$server->worker_id} 进程消息 来自进程号 #$srcWorkerId: $data\n";
});
$server->on('Task', function ($server, $taskId, $reactorId, $data) {
    var_dump($taskId, $reactorId, $data);
});
$server->on('Finish', function ($server, $fd, $reactorId) {

});
$server->on('Receive', function (Swoole\Server $server, $fd, $reactorId, $data) {
    if (trim($data) == 'task') {
        $server->task("异步task启动");
    } else {
        var_dump($server->worker_id);
        //task进程范围为 worker_num-1至worker_num+task_worker_num-1
        $server->sendMessage("发送给task进程", 1);
    }
});

$server->start();
```
::: warning
task进程范围为 worker_num-1至worker_num+task_worker_num-1  
使用 `sendMessage`方法必须注册 `onPipeMessage` 事件回调函数.
`task_ipc_mode = 3` 将无法使用`sendMessage`向特定的`task`进程发送消息.    
在 `MacOS/FreeBSD`系统下数据超过 `2K` 就会使用临时文件存储.

在 `Worker` 进程内调用 `sendMessage` 是异步 `IO` 的,消息会先存到缓冲区,可写时才通过`unixSocket`发送.  
在`Task`进程和`用户自定义进程` 内调用`sendMessage`默认是同步 `IO`.可通过一键协程化转为异步.

:::
   
### addProcess
添加一个自定义进程.  
方法原型:addProcess(\swoole_process $process)  
   
#### 参数介绍
- $process `Swoole\Process`对象
   
#### 示例
```php
<?php
$server = new Swoole\Server('0.0.0.0', 9501);

/**
 * 自定义进程,new Swoole\Process 时,传入一个闭包,进程启动后将执行这个闭包
 */
$process = new Swoole\Process(function ($process) use ($server) {

    $socket = $process->exportSocket();
    while (true) {
        $msg = $socket->recv();
        //不断的向其他客户端发送消息
        foreach ($server->connections as $connection) {
            $server->send($connection, $msg);
        }
        //每秒发送一次
        sleep(1);
    }
}, false, 2, 1);

$server->addProcess($process);

$server->on('receive', function ($server, $fd, $reactorId, $data) use ($process) {
    //用户连接成功后,将这条消息发送到所有socket
    $socket = $process->exportSocket();
    //向自定义进程发送接收到的消息
    $socket->send($data);
});

$server->start();
```
::: warning
自定义进程在swoole启动后直接创建,意味着也可以使用在swoole启动前所有声明的全局变量   
自定义进程跟swoole主进程生命周期一致,`reload`方法不能重启自定义进程
在其他进程,可通过`$process`对象变量,在`worker`进程与自定义进程通信  
在自定义进程,可通过`$server->sendMessage` 和`worker`进程通信  
自定义进程不能调用task,不能使用关于`$server->task`,`$server->taskwait`接口
自定义进程退出后,系统将会自动重启,为了避免不断退出,重启,在自定义进程中最好是使用while(1)或[timer定时器](/Cn/Swoole/Timer/timer.md) 包裹   
在`shutdown`关闭服务时,自定义进程将收到`SIGTERM`信号,关闭进程,但如果进程当前繁忙,则无法退出(例如一直while(1)并且没有sleep)   
:::

   
### stats
获取当前`server`的客户端连接信息,系统启动信息,客户端连接/关闭次数信息等.

方法原型:stats()  
   
#### 示例
```php
<?php
var_dump($server->stats());
//array(11) {
//  ["start_time"]=>
//  int(1582541268)  //服务启动事件
//  ["connection_num"]=>
//  int(1) //当前客户端连接数
//  ["accept_count"]=>
//  int(1) //总连接次数
//  ["close_count"]=>
//  int(0) //总关闭次数
//  ["worker_num"]=>
//  int(2) //worker进程数
//  ["idle_worker_num"]=>
//  int(1) //空闲的worker进程数
//  ["tasking_num"]=> 
//  int(0) //task worker进程数
//  ["request_count"]=>
//  int(0) //server收到的数据次数,只有onReceive,onMessage,onRequest,onPacket 才会计算
//  ["worker_request_count"]=>
//  int(0) //当前`Worker`进程收到的请求次数(worker_request_count)超过 `max_request`时`worker`进程将退出】
//  ["worker_dispatch_count"]=>
//  int(1) //`master`进程向当前`Worker`进程投递任务的次数
//  ["coroutine_num"]=>
//  int(1) //当前的协程数
//}

//额外的参数

// task_queue_num	消息队列中的投递`task`的数量(用于task投递)
// task_queue_bytes	消息队列的内存占用大小(字节单位)(用于task投递)
// task_idle_worker_num	空闲的`task`进程数

```

   
### getSocket
获取底层的socket句柄
方法原型:getSocket($port = null)  

::: warning
通过获取底层的socket句柄,调用`socket_set_option`设置更加底层的socket参数  
此方法需要 `sockets` 扩展,编译 `Swoole` 时必须开启 --enable-sockets 选项

:::
   
### bind
将连接绑定一个自定义的uid,
将连接绑定一个用户定义的 UID，可以设置 dispatch_mode=5 设置以此值进行 hash 固定分配。可以保证某一个 UID 的连接全部会分配到同一个 Worker 进程。
方法原型:bind($fd, $uid)  
   
#### 参数介绍
- $fd 客户端fd
- $uid  自定义的uid,不能为0

::: warning
仅在`dispatch_mode=5` 时有效.   
同一个连接只能被 bind 一次,如果已经绑定,再次调用会返回false/

在默认的 `dispatch_mode=2` 时,`server` 会按照 `fd` 取模来分配连接数据到不同的 `worker` 进程.但由于客户端断开后重新连接,fd会改变,这就造成了客户端数据将分配到不同的`worker`进程,通过该方法,即可实现自定义分配,只要uid相同,则会分配相同的`worker`进程  
客户端连接服务器后,连续发送多个包,可能会存在发送顺序不一致(时序问题).调用`bind`之后,可能后面的包已经分配到了原来的进程,所以只有在`bind`之后新收到的数据才会按照 `uid`取模分配.  
根据上面的特性,当客户端连接成功时,可以约定一个发送步骤,先发送数据进行握手,握手成功,等服务端`bind`之后,客户端再进行发送其他数据 
::: 

