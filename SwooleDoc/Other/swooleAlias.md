---
title: swoole函数别名
meta:
  - name: description
    content: swoole函数别名
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|EasySwoole|swoole|swoole函数别名
---

### 协程短名称

```php
//Swoole\Coroutine 别称 co
//创建协程也就是Swoole\Coroutine::create 等同于go
go(function(){
	//Coroutine::sleep(1) 也就等同于 \co::sleep(1);
	\co::sleep(1);
	echo "EasySwoole Good";
});

//通道操作 $chan = new Swoole\Coroutine\Channel(1) 等同于 $chan = new chan(1);
$c = new chan(6);
$c->push($row);//向通道写入数据
 //延迟执行 Swoole\Coroutine::defer 也就等同于defer
go(function(){

	defer(function(){

 	});
})
 
```

### 类别名关系
  小写下划线(不推荐使用，即将废弃)  	  命名空间风格
- swoole_server						Swoole\Server
- swoole_client						Swoole\Client
- swoole_process					Swoole\Process
- swoole_timer						Swoole\Timer
- swoole_table						Swoole\Table
- swoole_lock						Swoole\Lock
- swoole_atomic						Swoole\Atomic
- swoole_buffer						Swoole\Buffer
- swoole_redis						Swoole\Redis
- swoole_event						Swoole\Event
- swoole_mysql						Swoole\MySQL
- swoole_mmap						Swoole\Mmap
- swoole_channel					Swoole\Channel
- swoole_serialize					Swoole\Serialize
- swoole_http_server				Swoole\Http\Server
- swoole_http_client				Swoole\Http\Client
- swoole_http_request				Swoole\Http\Request
- swoole_http_response				Swoole\Http\Response
- swoole_websocket_server			Swoole\WebSocket\Server