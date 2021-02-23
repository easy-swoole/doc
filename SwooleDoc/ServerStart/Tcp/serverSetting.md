---
title: easyswoole swoole-配置
meta:
  - name: description
    content: easyswoole swoole-配置
  - name: keywords
    content: easyswoole swoole-配置|easyswoole|swoole
---

## swoole配置

   
### reactor_num
说明:设置启动的`reactor`线程数    
默认值: cpu核数,超过8核默认8  
补充说明: 调节主进程内事件处理的线程数.   
建议调整为cpu核数的1-4倍,不能超过4倍  
::: warning
`reactor_num`如果大于`worker_num`,将会自动设置为`reactor_num`=`worker_num`

:::
   
### worker_num
说明:设置启动的worker进程数  
默认值:cpu核数    
补充说明:在默认情况(协程化异步io),建议设置为cpu核数的1-4倍,如果业务为同步io,需要根据请求响应时间和系统负载来调整.不应过高.    
::: warning
最大不能超过cpu核数*1000   
设置过高会导致cpu调度进程繁忙,占用更多的内存.  
:::
   
### max_request
说明:worker进程最大的处理任务数    
默认值:0(无限)  
补充说明:当worker的处理数到达`max_request`时,该进程会退出,重新启动一个新进程进行处理  
::: warning
当php代码内存有可能泄露时,通过控制最大任务数,在没有内存溢出之前退出进程,回收内存.  
但这个只是权宜之计,最主要的是解决内存泄漏问题.  
达到`max_request`后,进程会接收到关闭信号,但是进程需要处理完当前任务(进程非繁忙状态)才能处理关闭信号,进行退出.  
`SWOOLE_BASE`模式下,达到`max_request`重启进程会导致断开客户端连接.
:::
   
### max_conn (max_connection)
说明:最大连接数    
默认值:默认为`ulimit -n`,当`ulimit -n`超过10万时,默认值为10万  
补充说明:当客户端连接超过`max_conn`的数据,后面新进入的连接将会被拒绝  
::: warning
最大值不能超过`ulimit -n`,如果超出则会抛出警告信息并重置值
最小值不能小于`(worker_num + task_worker_num) * 2 + 32`  
每个tcp连接都会占用224字节,需要根据服务器内存来进行调整.

:::
   
### task_worker_num
说明:task进程的数量    
默认值:0 不启动task进程  
补充说明:当大于0时,将启动task功能,task功能启动需要注册`onTask`,`onFinish`回调函数.    
::: warning
最大值不能超过cpu核数*1000  
task默认为同步阻塞进程,不带协程环境  
根据worker进程投递任务的数量以及task处理任务的速度来调整task进程数.  
:::
   
### task_ipc_mode
说明:设置task进程和worker进程之间的通信方式    
默认值:1  
补充说明:  
- 1:unix socket通信
- 2:sysvmsg消息队列通信 
- 3:消息队列通信争抢模式   
::: warning
如果设置3,将使用系统的消息队列作为通信,没有指定`mssage_queue_key`时,server终止将删除消息队列,指定后不会删除,可通过`ipcrm -q` 手动删除
:::
   
### task_max_request
说明:设置task进程的最大任务数   
默认值:0  
补充说明:作用和`max_request`相同,当超出最大任务数,task将退出,并重新启动一个新的task进程   
   
### task_tmpdir
说明:设置task的数据临时目录    
默认值:/tmp  
补充说明:当投递task任务数据超过`8k`时,将启用临时文件来保存数据,这里设置的就是临时文件的存储目录.   
::: warning
底层默认会使用 `/tmp` 作为存储 `task` 数据的目录.  
如果 `/tmp`  目录不是内存文件系统,可以设置为 `/dev/shm/`  
`task_tmpdir` 如果目录不存在,底层会尝试自动创建.  
如果`task_tmpdir`不存在并且创建失败,则`server->start`将失败.  
:::
   
### task_enable_coroutine
说明:开启task协程支持    
默认值:false  
补充说明:开启后自动在`onTask`回调中创建协程和协程容器,在回调中可直接使用协程api.
::: warning
此参数必须在`enable_coroutine=true`时才可使用
:::
   
### task_use_object
说明:使用面向对象风格的`task`回调格式.    
默认值:false  
补充说明:开启之后,onTask回调中,给task进程发送的数据,将会在$task->data中  
```php
<?php
$server = new Swoole\Server('0.0.0.0', 9501);
$server->set([
    'task_worker_num' => 1,
    'task_use_object' => true,
]);
$server->on('Receive', function (Swoole\Server $server, $fd, $tid, $data) {
    $server->task(['fd'=>$fd]);
});
$server->on('Task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    //此处$task是Swoole\Server\Task对象
    //获取数据需要通过$task->data获取
    $server->send($task->data['fd'], json_encode($server->stats()));

});
$server->start();
```
   
### dispatch_mode
说明:server主进程数据包分发策略    
默认值:2  
补充说明:
- 1,轮询模式,收到数据后会轮循分配给每一个 `worker` 进程
- 2,固定模式,根据连接的文件描述符(fd)分配,意味着同一个客户端,发送多次数据都只会分配给同一个进程
- 3,抢占模式,主进程会判断`worker`进程的繁忙状态,只会投递给空闲进程
- 4,ip分配,根据客户端ip进行取模分配,可以保证同一个来源ip的数据都会分配到同一个进程.算法为:`ip2long(ClientIP) % worker_num`
- 5,uid分配,需要在代码中调用`server->bind`给连接绑定uid,通过uid取模分配到进程.
- 7,stream模式,空闲的`Worker`会`accept`连接,并接受`reactor`线程的新请求
::: warning
同步阻塞无状态的server,可以使用3,异步非阻塞无状态可使用1
在udp协议中,`dispatch_mode=2/4/5`时将固定分配通过ip取模分配.`dispatch_mode=1/3`时将随机分配
在`dispatch_mode=SWOOLE_BASE`时该配置无效.worker进程直接接收,无需投递  
1/3 模式由于特性,无法保证`onConnect/onClose/onReceive`顺序,将屏蔽`onConnect/onClose`事件
tcp/http等状态式服务器请不要使用1/3模式.  
:::
   
### dispatch_func
说明:编写自定义数据包分发策略函数    
默认值:null  
补充说明:如果你觉得上面的6数据包分发策略不适合你的服务,可自行编写`c++/php`函数实现调度逻辑,具体实现略.  
   
### message_queue_key
说明:设置消息队列的 key    
默认值:ftok($php_script_file, 1)   
补充说明:   
仅在 `task_ipc_mode = 2/3` 时使用.设置的 `key` 仅作为 `task` 任务队列的 key,参考 `swoole` 下的 `ipc` 通讯.
::: warning
`task` 队列在 `server` 结束后不会销毁,重新启动程序后,`task` 进程仍然会接着处理队列中的任务. 如果不希望程序重新启动后执行旧的 `task` 任务.可以手工删除此消息队列.  
```
ipcs -q 
ipcrm -Q [msgkey]
```

:::
   
### daemonize
说明:服务是否开启守护进程    
默认值:0  
补充说明:设置为1后,程序将转入后台作为守护进程运行,长时间运行的服务器必须启用此项,否则当ssh终端退出后,程序也将直接退出.  
::: warning
- 开启守护进程后,标准输入和输出会被重定向到`log_file`,如果未设置`log_file`,将重定向到 `/dev/null`,所有打印数据将丢弃
- 开启守护进程后,cwd(当前目录)环境变量的值会发生变更,相对路径的文件读写会出错.PHP 程序中必须使用绝对路径
- 使用 `systemd` 或者 `supervisord` 管理 `Swoole` 服务时,请勿设置 daemonize = 1,否则会导致守护进程跟管理进程失去联系,导致管理`swoole`服务失败.  
:::
   
### backlog
说明:listen 队列的长度    
默认值:null  
补充说明:  
tcp在连接时存在一个握手机制,先提出需要握手,然后服务器响应握手,详细可查看[tcp](/Cn/NoobCourse/NetworkrPotocol/Tcp/tcp.md).当服务器来不及响应时,握手请求会先保存在`accept queue`队列中,队列长度由`backlog`控制,如果队列满了,后面进来的连接握手可能会失败.  
::: warning
linux2.2 之后握手 分为 `syn queue` 和 `accept queue` 两个队列,`syn queue` 长度由 `tcp_max_syn_backlog` 决定. 
   
### log_file
说明:swoole错误日志文件存放路径    
默认值:null  
补充说明:在`swoole`运行期间,发生的异常信息都会写入到这个文件,开启守护进程后,标准输出也会写入到这个文件,例如`var_dump,echo,print`等函数输出  
::: warning
log_file只是记录运行时候的错误记录,可以定期删除.  
日志id的符号表示日志写入的进程类型:  
- # `Master` 进程
- $ `Manager` 进程
- * `Worker` 进程
- ^ `Task` 进程
:::
   
### log_level
说明:设置swoole输出日志的错误等级,低于这个级别的日志,信息将忽略    
默认值:SWOOLE_LOG_DEBUG(所有级别都打印)  
补充说明:可查看[swoole常量](/Cn/Swoole/Other/swooleConsts.md)  
::: warning
`SWOOLE_LOG_DEBUG` 和 `SWOOLE_LOG_TRACE` 仅在编译为 `--enable-debug-` 和 `--enable-trace-log` 版本时可用.
:::
   
### open_tcp_keepalive
说明:是否启用`tcp keepalive`检测死链接   
子配置项: 
- tcp_keepidle 一个连接连续`tcp_keepidle`秒没有请求,系统则进行探测
- tcp_keepcount 超过`tcp_keepcount`次数没有请求,将关闭连接
- tcp_keepinterval 探测的间隔时间
默认值:0  
 
示例:
   
```php
<?php

$server = new Swoole\Server("0.0.0.0", 9501, SWOOLE_PROCESS);
$server->set(array(
    'worker_num' => 1,
    'open_tcp_keepalive' => 1,//开启tcp keepalive
    'tcp_keepidle' => 4, //4s没有数据传输就进行检测
    'tcp_keepinterval' => 1, //1s探测一次
    'tcp_keepcount' => 5, //探测的次数，超过5次后还没回包close此连接
));

$server->on('connect', function ($server, $fd) {
    echo "客户端 {$fd} 连接成功\n";
});

$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    echo "客户端 {$fd} 发来消息:{$data} \n";

    /**
     * @var $server \Swoole\Server
     */
    $server->send($fd, "服务器响应: ".$data);
});

$server->on('close', function ($server, $fd) {
    echo "客户端 {$fd} 关闭\n";
});

$server->start();
```

   
### heartbeat_check_interval  

说明:是否启用心跳检测    
默认值:false  
补充说明:每`heartbeat_check_interval`遍历一次tcp连接,如果在`heartbeat_idle_time`内没有向服务器发送数据,此连接将直接关闭.
::: warning
该配置项只支持tcp类连接(tcp/http/websocket等)
server端不会向客户端主动发送心跳数据包,需要客户端自行发送,发送的数据可以为任意数据.  
直接关闭也将触发`onClose`回调  
:::
   
### heartbeat_idle_time


说明:连接最大允许空闲的时间,需要跟`heartbeat_check_interval`结合使用   
默认值:`heartbeat_check_interval`*2  
补充说明:  
如果只设置了`heartbeat_idle_time` 未设置 `heartbeat_check_interval`   
底层将不会创建心跳检测线程,可以调用 `heartbeat` 方法手动处理超时的连接.  
```php
[
    'heartbeat_idle_time'      => 300, // 表示一个连接如果300秒内未向服务器发送任何数据，此连接将被强制关闭
    'heartbeat_check_interval' => 60,  // 表示每60秒遍历一次
];
```
   
### open_eof_check
说明:开启`eof`检测    
默认值:false  
补充说明:  
用于tcp粘包处理,可查看[tcp粘包](/Cn/Socket/tcpSticky.md)   
```php
[
    'open_eof_check' => true,   //打开EOF检测
    'package_eof'    => "\r\n", //设置EOF
];
```
::: warning
开启之后将检测客户端发来的数据,只有接收到结尾为`package_eof`字符串才会投递给`worker`,代表接收了一条完整的消息.  
如果没有收到,则会一直保留在缓冲区,拼接数据,直到接收或者缓存区满才会终止.终止后将关闭连接  
需要跟`package_eof`结合使用  
此配置只对`tcp/Unix Socket Stream`有效,udp无效
eof只能保证结尾是`package_eof`,但不能保证中间是否还存在`package_eof`字符串,所以需要使用`explode(package_eof, $data)` 进行拆分数据包,也可启用`open_eof_split`自动拆分
:::

   
### open_eof_split
说明:启用`eof`自动封包    
默认值:false  
补充说明:此配置和`package_eof`结合使用,开启此配置后,默认开启`open_eof_check`. 
::: warning
开启后,底层将从数据包中寻找`package_eof`并拆分数据包,确保`onReceive`每次都收到一个完整的以`package_eof`字符串结尾的数据包,但是会消耗更多的cpu资源.  
:::
   
### package_eof
说明:设置eof字符串    
默认值:null  
补充说明:用于tcp粘包处理,可查看[tcp粘包](/Cn/Socket/tcpSticky.md),需要配合`open_eof_check/open_eof_split`使用  
::: warning
最大只能设置`8k`的字符串  
:::  

   
### open_length_check
说明:开启包长检测.需要客户端提供 `包头(带了这次数据包的长度)+数据包主体`的数据包,确保`worker`进程每次都收到一个完整的数据包    
默认值:false  
补充说明:用于tcp粘包处理,可查看[tcp粘包](/Cn/Socket/tcpSticky.md).   
::: warning
包长检测需要客户端提供`固定包头+包体` 这种格式数据.开启后,可以保证`worker` 进程 `onReceive` 每次都会收到一个完整的数据包.   
并且该方法只需要计算一次长度,数据处理只需要进行指针偏移,性能极高,推荐使用.   
:::
该参数需要配合以下参数使用:   
- package_length_type  长度值类型,跟php的[`pack`](https://www.php.net/manual/zh/function.pack.php)函数一致,可查看该配置详细说明(就在下面)
- package_body_offset  从第几个直接开始计算长度(直接计算包头+包体长度和只计算包体长度)
- package_length_offset 长度值在包头的第几个字节
- package_max_length  最大数据包字节长度 

   
### package_length_type
说明:长度值类型,跟php的[`pack`](https://www.php.net/manual/zh/function.pack.php)函数一致    
默认值:null  
补充说明:目前swoole支持以下类型:  
- c	有符号,1 字节  
- C	无符号,1 字节  
- s	有符号,主机字节序,2 字节  
- S	无符号,主机字节序,2 字节  
- n	无符号,网络字节序,2 字节  
- N	无符号,网络字节序,4 字节  
- l	有符号,主机字节序,4 字节(小写 L)  
- L	无符号,主机字节序,4 字节(大写 L)  
- v	无符号,小端字节序,2 字节  
- V	无符号,小端字节序,4 字节      

   
### package_length_func
说明:设置长度解析函数,略    

   
### package_max_length
说明:设置数据包最大尺寸    
默认值:2M  
补充说明:开启 `open_length_check/open_eof_check/open_eof_split/open_http_protocol/open_http2_protocol/open_websocket_protocol/open_mqtt_protocol`等协议解析后,swoole底层将进行数据包拼接,最大不能超过`package_max_length`   
`open_length_check` 如果解析包头发现包长超过`package_max_length`,将直接丢弃数据并且关闭连接  
`open_eof_check` 数据将会一直保存到缓冲区,直到超过`package_max_length`,将直接丢弃数据并且关闭连接  
`open_http_protocol` get请求固定为 8K,post请求将检测`Content-Length`,超过`package_max_length`,将直接丢弃数据,响应`http 400` 错误,并且关闭连接  
::: warning
如果同时有 5000个 客户端在发送数据,每个数据包 2M,那么最极限的情况下,就会占用 10G 的内存空间.所以不建议设置过大.
:::

   
### open_http_protocol
说明:启用http协议处理      
默认值:false  
补充说明:当`server`为`Swoole\Http\Server`自动启用 

   
### open_websocket_protocol
说明:启用websocket协议处理    
默认值:false  
补充说明:当`server`为`Swoole\WebSocket\Server`自动启用,并将`open_http_protocol`也启用   

   
### open_mqtt_protocol
说明:启用mqtt协议处理    
默认值:false  
补充说明:启用后,要求客户端每次都发送 完整的 `mqtt` 数据包  

   
### open_websocket_close_frame
说明:启用`websocket`协议中关闭帧.     
默认值:false  
补充说明:开启后,可在 `Swoole\WebSocket\Server` 中的 onMessage 回调中接收到客户端或服务端发送的关闭帧(opcode 为 0x08 的帧),开发者可自行对其进行处理.
     

   
### open_cpu_affinity
说明:开启cpu亲和性设置    
默认值:false   
补充说明:在多核的硬件平台中,开启后会将 `swoole` 的 `reactor线程/worker进程`绑定到固定的一个cpu核上.可以避免`进程/线程`运行时在多个核之间切换,提高 CPU Cache 的命中率.   
   
### cpu_affinity_ignore
说明:接受一个数组作为参数,例如[0, 1] 表示swoole不使用 CPU0,CPU1,只用来处理网络中断      
默认值:null  
补充说明:I/O密集型程序中,所有网络中断都是使用CPU0来处理.但是如果网络I/O过于重,CPU0占用过高会导致网络中断无法及时处理,那么网络收发包的能力就会下降.   
如果不设置此选项,swoole将会使用全部CPU核,底层根据reactor_id或worker_id与CPU核数取模来设置CPU绑定.
::: warning
此参数必须和`open_cpu_affinity`同时配置才能生效
:::
   
### open_tcp_nodelay
说明:启用`open_tcp_nodelay`    
默认值:false  
补充说明:启用后 客户端 TCP 连接发送数据时会关闭 `Nagle` 合并算法,立即发往对端 TCP 连接,在某些场景下,可以提升响应速度,请自行搜索 Nagle 算法。  

   
### tcp_defer_accept
说明: 启用`tcp_defer_accept`特性    
默认值:false  
补充说明: 开启 `tcp_defer_accept` 特性之后,`accept` 和 `onConnect` 对应的时间会发生变化,如果设置为 10 秒:  
- 客户端连接到服务器后不会立即触发 accept 
- 在 10 秒内客户端发送数据,将会同时顺序触发 accept/onConnect/onReceive
- 在 10 秒内客户端没有发送任何数据,将会触发 accept/onConnect 

   
### ssl_cert_file/ssl_key_file
说明:设置ssl隧道加密的证书和私钥路径    
默认值:null  
补充说明:2个参数必须传入文件的`绝对路径`.    
```php
<?php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => __DIR__.'/config/ssl.crt',
    'ssl_key_file' => __DIR__.'/config/ssl.key',
]);
```  
文件必须为 PEM 格式,不支持 DER 格式,可使用 openssl 工具进行转换.
- PEM 转 DER 格式
```
openssl x509 -in cert.crt -outform der -out cert.der
```
DER 转 PEM 格式
```
openssl x509 -in cert.crt -inform der -outform pem -out cert.pem
```

::: warning
编译swoole时必须开启` --enable-openssl`选项  
wss(https websocket) 中,发起 WebSocket 连接的页面必须使用 https  
浏览器不信任 SSL 证书将无法使用 wss   
:::
   
### ssl_method
说明:设置openssl隧道加密的算法    
默认值:SWOOLE_SSLv23_METHOD  
补充说明:支持的类型可查看[swoole常量](/Cn/Swoole/Other/swooleConsts.md)  
::: warning
server 与 client 使用的算法必须一致,否则 SSL/TLS 握手将会失败,连接会直接中断.
:::
   
### ssl_ciphers
说明:设置openssl加密算法    
默认值:EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH  
补充说明:如果配置为空,openssl将自行选择加密算法  

   
### ssl_verify_peer
说明:服务`ssl`设置验证对端证书    
默认值:false  
补充说明:如果开启,需要同时配置`ssl_client_cert_file`  
```php
$server = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file'         => __DIR__ . '/config/ssl.crt',
    'ssl_key_file'          => __DIR__ . '/config/ssl.key',
    'ssl_verify_peer'       => true,// 开启验证对端证书功能
    'ssl_allow_self_signed' => true,// 允许自签名证书
    'ssl_client_cert_file'  => __DIR__ . '/config/client.crt',//客户端正证书
]);
```
   
### user
说明:设置`worker`进程和`task`进程的所属用户(只有在使用root用户启动时才可配置)    
默认值:执行脚本的用户  
补充说明:可以指定子进程运行在普通用户权限下,这样可以避免因root用户执行,当出现漏洞时root权限过大的问题  
::: warning
修改之后,无法在`worker/task`进程执行`shutdown/reload`方法,权限不足,需要root用户在终端发送kill命令.  
:::
   
### group
说明:设置`worker`进程和`task`进程的所属用户组(只有在使用root用户启动时才可配置)      
默认值:执行脚本的用户组   
补充说明:同user配置,降低进程权限   

   
### chroot
说明:重定向`worker`进程的根目录路径    
默认值:null  
补充说明:此配置可以使得进程对文件系统的读写路径与实际的操作系统文件系统隔离,提升安全性  

   
### pid_file
说明:设置pid文件路径    
默认值:swoole服务启动的时候,会将主进程(master)进程id写入到这个文件,在服务关闭后自动删除这个文件.  
补充说明:如果服务进程异常退出,文件无法被删除,可以通过向这个进程id发送进程信号0,来探测进程是否存在.    


   
### buffer_output_size
说明:配置发送输出缓冲区的大小    
默认值:2*1024 *1024  
补充说明:单位为字节,默认`server->send`,`Http\Server->end/write`,`WebSocket\Server->push`单次最大允许发送2M数据  
::: warning
该配置只在 `SWOOLE_PROCESS` 模式中生效.  
该配置不应配置过大,否则会占用过多的内存  
:::
   
### socket_buffer_size
说明:配置客户端连接最大允许占用`socket_buffer_size`的内存      
默认值:2*1024 *1024  
补充说明:单位为字节,该配置决定了一个客户端连接,发送缓冲区最大只能占用`socket_buffer_size`  
::: warning
`buffer_output_size`是限制单次发送,`socket_buffer_size`限制的是总共发送不能超过.
:::
   
### enable_unsafe_event
说明:在`dispatch_mode=1/3`时,开启`onConnect/onClose`事件    
默认值:false  
补充说明:如果程序在`dispatch_mode=1/3`时,扔需要`onConnect/onClose`事件,可开启此配置,但不能保证`onConnect/onReceive/onClose`事件的顺序.    

   
### discard_timeout_request
说明:丢弃已关闭连接的数据请求    
默认值:true  
补充说明:当`dispatch_mode=1/3`时,由于无法保证`onConnect/onReceive/onClose`事件的顺序,所以客户端的一些数据可能会在连接关闭之后才到达`worker`进程  
该配置开启后,只要连接关闭,将丢弃还未发送到`worker`进程的数据    

   
### enable_reuse_port
说明:配置端口重用,可重复启动监听同一个端口的 `server` 程序    
默认值:false  
补充说明: 只有在 `Linux-3.9.0` 以上版本的内核才可使用   
   
### enable_delay_receive
说明:配置 `accept`客户端连接后将不会自动加入`event loop`  
默认值:false 
补充说明:小编没测试成功,不会用   

   
### reload_async
说明:配置异步安全重启    
默认值:true  
补充说明:开启之后,如果`server`重启,`worker`进程会等待异步事件完成后安全退出  

   
### max_wait_time
说明:设置`worker`进程接收到重启信号后,等待异步事件完成最大的等待时间     
默认值:3  
补充说明:
管理进程收到重启,关闭信号,或者请求次数到达 `max_request` 时,管理进程将重启该 `worker` 进程:  
- 增加一个 (`max_wait_time`) 秒的定时器,触发定时器后(也就是到了最大等待时间),检查进程是否依然存在,如果存在,会强制杀掉重新创建一个worker进程.
- worker进程可以在 `onWorkerStop` 回调里面做收尾工作,但是需要在 `max_wait_time` 秒内执行完.
- 依次向目标进程发送 `SIGTERM` 信号,杀掉进程.    

   
### tcp_fastopen
说明:启用tcp快速握手    
默认值:false  
补充说明: 开启后可以提升 `TCP` 短连接的响应速度,在客户端完成握手的第三步:发送 SYN 包时携带数据.
      
   
### request_slowlog_file
说明:开启请求慢日志。    
默认值:false  
补充说明:在协程环境下没卵用的参数,忽略  
   
### enable_coroutine
说明:开启协程服务器支持    
默认值:On  参数为On/Off(php.ini中配置swoole.enable_coroutine),和true/false(直接swoole->set配置)  
补充说明:开启后,`server`服务的各个回调事件中,将默认创建协程环境,影响的回调事件如下:  
- onWorkerStart  
- onConnect  
- onOpen  
- onReceive  
- redis_onReceive  
- onPacket  
- onRequest  
- onMessage  
- onPipeMessage  
- onFinish  
- onClose  
- tick/after 定时器  

   
### max_coroutine
说明:单进程最大协程数    
默认值:3000  
补充说明:超过 `max_coroutine` 将无法继续创建新协程,底层会抛出错误,并直接关闭连接.


   
### send_yield
说明:当缓冲区内存不足时,是否自动切换协程(yield),等待缓冲区清空后自动切回(resume)    
默认值:`dispatch_mode=2/4` 时默认开启 
补充说明:超过 `max_coroutine` 将无法继续创建新协程,底层会抛出错误,并直接关闭连接.


   
### hook_flags
说明:设置协程HOOK的函数范围  
默认值:null
补充说明:可查看[协程 HOOK](/Cn/Swoole/Coroutine/hook.md) 

