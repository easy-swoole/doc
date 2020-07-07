# Coroutine\System
> 系统相关`api`的协程封装，`v4.4.6+`正式版可用。大部分`api`基于`aio`线程池实现。

:::tip
`v.4.4.6`以前的版本，请使用`Co`短名或者`Swoole\Coroutine`调用，如：`Co::sleep`或`Swoole\Coroutine::sleep`  
`v.4.4.6`及以后版本推荐`Co\System::sleep`或者`Swoole\Coroutine\System::sleep`    
`v.4.4.6+`保持向下兼容    
:::

## 方法

### statvfs
作用：获取文件系统信息     
方法原型：System::statvfs(string $path): array|false;    
参数：
- $path 文件系统挂载的目录

示例：
```php
<?php
Swoole\Coroutine::create(function (){
    $result = \Swoole\Coroutine\System::statvfs('/');
    var_dump($result);
});
```

### fread
作用：协程方式读取文件     
方法原型：System::fread(resource $handle, int $length = 0): string|false;    
参数：
- $handle 文件句柄 （必须是`fopen`打开的文件类型`stream`资源）
- $length 读取的长度 默认0 表示读取全部

示例：
```php
<?php
$fp = fopen("/tmp/test.txt","r");
Swoole\Coroutine::create(function ()use ($fp){
    $result = Swoole\Coroutine\System::fread($fp);
    var_dump($result);
});
```
:::warning
`v4.0.4+` 版本 `fread` 方法支持了非文件类型的 `stream` 资源。
:::
### fwrite
作用: 协程方式写入数据        
方法原型：System::fwrite(resource $handle, string $data, int $length = 0): int|false;        
参数：
- $handle 文件句柄 （必须是`fopen`打开的文件类型`stream`资源）
- $data 写入的数据 可以为文本或者二进制
- $length 写入的长度 0 默认写入$data的全部内容 $length必须小于$data的长度

示例：
```php
<?php
$fp = fopen("/tmp/test.txt","w");
Swoole\Coroutine::create(function ()use ($fp){
    $result = Swoole\Coroutine\System::fwrite($fp,'EasySwoole');
    var_dump($result);
});
```
### fgets
作用：协程方式按行读取文件内容 底层使用了`php_stream`缓存区，默认大小为`8192`字节，使用`stream_set_chunk_size`设置缓存区尺寸。        
方法原型：System::fgets(resource $handle): string|false;     
参数：
- $handle 文件句柄 （必须是`fopen`打开的文件类型`stream`资源）

返回值：
- 读取到`EOL`（`\r` 或者 `\n`）将返回一行数据，包括`EOL`。
- 未读取到 `EOL`，长度超过`php_stream`缓存区大小，将返回缓存区内数据。
- 达到文件末尾 `EOF` 时，返回空字符串，可用 `feof` 判断文件是否已读完。
- 读取失败返回 `false`，使用 `swoole_last_error` 函数获取错误码。

示例：
```php
<?php
$fp = fopen("/tmp/test.txt","r");
Swoole\Coroutine::create(function ()use ($fp){
    $result = Swoole\Coroutine\System::fgets($fp);
    var_dump($result);
});
```
:::warning
`v4.4.4+` 可用 `fgets` 函数仅可用于文件类型的 `stream` 资源。
:::
### readFile
作用：协程方式读取文件     
方法原型：System::readFile(string $filename): string|false;      
参数：
- $filename 文件名

返回值：
- 读取成功返回字符串内容，读取失败返回 `false`，可使用 `swoole_last_error` 获取错误信息。
- `readFile` 方法没有尺寸限制，读取的内容会存放在内存中，所以读取超大文件时可能会占用过多内存。

示例：
```php
<?php
$filename = "/tmp/test.txt";
Swoole\Coroutine::create(function ()use ($filename){
    $result = Swoole\Coroutine\System::readFile($filename);
    var_dump($result);
});
```
### writeFile
作用：协程方式写入文件     
方法原型：System::writeFile(string $filename, string $fileContent, int $flags): bool;        
参数：
- $filename 文件名 文件必须有可写权限
- $fileContent 写入到文件的内容 最大可写`4M`
- $flag 写入的选项 默认清空文件内容重新写入 可使用`FILE_APPEND`追加到文件尾部

示例：
```php
<?php
$filename = "/tmp/test.txt";
Swoole\Coroutine::create(function ()use ($filename){
    $result = Swoole\Coroutine\System::writeFile($filename,"EasySwoole",FILE_APPEND);
    var_dump($result);
});
```
### sleep
作用：进入等待状态 相当于`php`的`sleep`函数，不同的是`Coroutine::sleep`是协程调度器实现的。       
方法原型：System::sleep(float $seconds): void;       
参数：
- $seconds 睡眠的时间 必须大于0 最小精度为`0.001s`

示例：
```php
<?php
$server = new Swoole\Http\Server("127.0.0.1", 9502);

$server->on('Request', function($request, $response) {
    // 等待2s后向浏览器发送响应
    Swoole\Coroutine\System::sleep(2);
    $response->end("<h1>EasySwoole</h1>");
});

$server->start();
```
### exec
作用：执行一条`shell` 底层自动协程调度     
方法原型：System::exec(string $cmd): array;      
参数：
- $cmd 执行的`shell`指令

示例：
```php
<?php
Swoole\Coroutine::create(function (){
    $result = Swoole\Coroutine\System::exec('ls');
    // code 进程退出的状态码
    // signal 信号
    // output 输出内容
    var_dump($result);
    /**
     *array(3) {
    ["code"]=>
    int(0)
    ["signal"]=>
    int(0)
    ["output"]=>
    string(164) "App
    Cn
    EasySwooleEvent.php
    En
    Log
    Static
    Temp
    bootstrap.php
    composer.json
    composer.lock
    dev.php
    easyswoole
    pool.php
    produce.php
    test.php
    vendor
    "
    }
     */
});
```
### gethostbyname
作用：将域名解析为ip，基于同步线程池模拟实现，底层自动调度      
方法原型：System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false;       
参数：
- $domain 域名
- $family 域族 `AF_INET`表示`IPv4` `AF_INET6`表示`IPv6`
- $timeout 超时时间 最小精度 `0.001s`

示例：
```php
Swoole\Coroutine::create(function (){
    $ip = Swoole\Coroutine\System::gethostbyname('www.easyswoole.com');
    var_dump($ip);
});
```
### getaddrinfo
作用：进行dns解析，查询域名对应ip地址，与`gethostbyname`不同，会返回多个ip结果      
方法原型：System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false;     
参数：
- $domain 域名
- $family 域族 `AF_INET`表示`IPv4` `AF_INET6`表示`IPv6`
- $socktype 协议类型 `SOCK_STREAM`,`SOCK_DGRAM`,`SOCK_RAW`
- $protocol 协议 `STREAM_IPPROTO_TCP`,`STREAM_IPPROTO_UDP`,`STREAM_IPPROTO_STCP`,`STREAM_IPPROTO_TIPC`,0
- $service 无
- $timeout 超时时间 最小精度 `0.001s`

示例：
```php
<?php
Swoole\Coroutine::create(function (){
    $ip = Swoole\Coroutine\System::getaddrinfo('www.easyswoole.com');
    var_dump($ip);
});

```
### dnsLookup
作用：域名地址查询。与`gethostbyname`不同，`dnsLookup`基于`udp`客户端实现 仅支持ipv4解析。     
方法原型：System::dnsLookup(string $domain, float $timeout = 5): string|false;       
参数：
- $domain 域名
- $timeout 超时时间 最小精度 `0.001s`

常见错误：
- `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`：此域名无法解析，查询失败
- `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`：解析超时，DNS 服务器可能存在故障，无法在规定的时间内返回结果

示例：
```php
<?php
Swoole\Coroutine::create(function (){
    $ip = Swoole\Coroutine\System::dnsLookup('www.easyswoole.com');
    var_dump($ip);
});
```
### wait
作用：对应原有的[Process::wait](/Cn/Swoole/Process/introduction.html#wait) 不同的是此api是协程，会造成协程挂起。`v4.5.0+`可用      
方法原型：System::wait(float $timeout = -1): array|false;        
参数：
- $timeout 超时时间 最小精度 `0.001s`

返回值：
- 成功返回数组包含（进程pid，退出状态码，被哪种信号kill）
- 失败直接false

示例：
```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'EasySwoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    var_dump($status);
});
```
### waitPid
和wait方法一样，这个可用指定等待特定的进程 `v4.5.0+`可用     
方法原型：System::waitPid(int $pid, float $timeout = -1): array|false;       

参数：
- $pid 进程id (-1表示任意进程 此时等价wait)
- $timeout 超时时间 最小精度 `0.001s`

返回值：
- 成功返回数组包含（进程pid，退出状态码，被哪种信号kill）
- 失败直接false

示例：
```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'EasySwoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```
:::warning
每个子进程启动后，父进程必须都要派遣一个协程调用 `wait()`(或 `waitPid()`) 进行回收，否则子进程会变成僵尸进程，会浪费操作系统的进程资源。
:::
### waitSignal
作用：协程版本的信号监听器，会阻塞当前协程直到信号触发。`v4.5.0+`可用     
方法原型：System::waitSignal(int $signo, float $timeout = -1): bool;     
参数：
- $signo 信号类型
- $timeout 超时时间 -1永不超时

返回值：
- 收到信号返回true
- 超时未收到直接false

示例：
```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```
### waitEvent
作用：协程版本的事件监听器，会阻塞当前协程直到事件触发。等待io事件 `v4.5.0+`可用      
方法原型：System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false;       
参数：
- $socket 文件描述符
- $events 事件类型 `SWOOLE_EVENT_WRITE` 或 `SWOOLE_EVENT_READ` | `SWOOLE_EVENT_WRITE`
- $timeout 超时事件 -1永不超时

返回值：
- 返回触发的事件类型的和（可能是多个位），和`$events`传入值有关系
- 失败直接false `swoole_last_error`获取错误信息

示例：

> 同步阻塞代码通过此api会变成协程非阻塞

```php
<?php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.easyswoole.com:80', $errno, $errstr, -1);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    if (!$events == SWOOLE_EVENT_WRITE) throw new \Exception(swoole_last_error());


    fwrite($client, "GET / HTTP/1.1\r\nHost: www.easyswoole.com:80\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    if (!$events == SWOOLE_EVENT_READ) throw new \Exception(swoole_last_error());

    $response = fread($client, 8192);
    echo $response;
});
```