---
title: swoole常量
meta:
  - name: description
    content: swoole常量
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|EasySwoole|swoole|swoole常量
---

### swoole
- SWOOLE_VERSION			
说明：获取当前系统安装Swoole的版本号

### 构造方法
- SWOOLE_BASE	

说明：Base模式，业务代码在Master进程下的Reactor线程中执行
- SWOOLE_PROCESS	

说明：进程模式，业务代码在Manger进程下的Worker进程中执行

### Socket类型

- SWOOLE_SOCK_TCP 	

说明：创建TCP Socket
- SWOOLE_SOCK_TCP6		

说明：创建TCP IPv6 Socket
- SWOOLE_SOCK_UDP 			

说明：创建UDP Socket
- SWOOLE_SOCK_UDP6			

说明：创建UDP IPv6 Socket
- SWOOLE_SOCK_UNIX_DGRAM	

说明：创建 UNIX DGRAM Socket
- SWOOLE_SOCK_UNIX_STREAM	

说明：创建 UNIX STREAM Socket
- SWOOLE_SOCK_SYNC			

说明：创建同步客户端

### SSL加密
- SWOOLE_SSLv3_METHOD
- SWOOLE_SSLv3_SERVER_METHOD
- SWOOLE_SSLv3_CLIENT_METHOD	
- SWOOLE_SSLv23_METHOD（默认加密方法）	
- SWOOLE_SSLv23_SERVER_METHOD	
- SWOOLE_SSLv23_CLIENT_METHOD	
- SWOOLE_TLSv1_METHOD	
- SWOOLE_TLSv1_SERVER_METHOD	
- SWOOLE_TLSv1_CLIENT_METHOD	
- SWOOLE_TLSv1_1_METHOD	
- SWOOLE_TLSv1_1_SERVER_METHOD	
- SWOOLE_TLSv1_1_CLIENT_METHOD	
- SWOOLE_TLSv1_2_METHOD	
- SWOOLE_TLSv1_2_SERVER_METHOD	
- SWOOLE_TLSv1_2_CLIENT_METHOD	
 -SWOOLE_DTLSv1_METHOD	
- SWOOLE_DTLSv1_SERVER_METHOD	
- SWOOLE_DTLSv1_CLIENT_METHOD

### 日志等级
- SWOOLE_LOG_DEBUG		

说明：调试日志，内核开发调试使用，必须要开启--enable-debug-log可用
- SWOOLE_LOG_TRACE		

说明：跟踪日志，用于跟踪系统问题，并携带关键信息，必须要开启--enable-trace-log可用
- SWOOLE_LOG_INFO		

说明：普通信息提示
- SWOOLE_LOG_NOTICE		

说明：开启关闭提示信息
- SWOOLE_LOG_WARNING		

说明：警告信息
- SWOOLE_LOG_ERROR			

说明：错误信息，可能导致系统停止

### 跟踪标签
 使用trace_flags 设置跟踪日志标签，打印自己所需要的内容，并支持多个同事打印 中间用 | 隔开

- SWOOLE_TRACE_SERVER
- SWOOLE_TRACE_CLIENT
- SWOOLE_TRACE_BUFFER
- SWOOLE_TRACE_CONN
- SWOOLE_TRACE_EVENT
- SWOOLE_TRACE_WORKER
- SWOOLE_TRACE_REACTOR
- SWOOLE_TRACE_PHP
- SWOOLE_TRACE_HTTP2
- SWOOLE_TRACE_EOF_PROTOCOL
- SWOOLE_TRACE_LENGTH_PROTOCOL
- SWOOLE_TRACE_CLOSE
- SWOOLE_TRACE_HTTP_CLIENT
- SWOOLE_TRACE_COROUTINE
 -SWOOLE_TRACE_REDIS_CLIENT
- SWOOLE_TRACE_MYSQL_CLIENT
- SWOOLE_TRACE_AIO
- SWOOLE_TRACE_ALL    开启以上所有追踪


```php
//切记一定要开启--enable-trace-log
$server->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_PHP | SWOOLE_TRACE_WORKER,
]);
```
