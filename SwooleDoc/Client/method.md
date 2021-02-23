---
title: easyswoole swoole-客户端方法
meta:
  - name: description
    content: easyswoole swoole-客户端方法
  - name: keywords
    content: easyswoole swoole-客户端方法|easyswoole|swoole
---

## 客户端方法
类命名空间:`\Swoole\Coroutine\Client`.   

### __construct()
构造方法.  
方法原型:__construct(int $sockType, int $isSync = SWOOLE_SOCK_SYNC, string $key);    
参数说明:   
- $sockType  socket的类型,例如:SWOOLE_SOCK_TCP.可查看[server socket参数介绍](/Cn/Swoole/ServerStart/Tcp/method.html#参数介绍)
- $isSync 只有`SWOOLE_SOCK_SYNC`一个参数,同步阻塞模式,兼容参数
- $key 用于实现长连接的key,默认为"ip:port"作为key.   

### set()  
配置一些客户端的参数.  
方法原型:set(array $settings);   
参数说明:  
- $settings 配置的参数.  
```php
//配置客户端的数据结束符检测,确保每次接受的数据都是\r\n结尾
$client->set([
    'open_eof_check' => true,
    'package_eof' => "\r\n",
    'package_max_length' => 1024 * 1024 * 2,
]);
```

::: warning
其他参数配置,可查看[配置](/Cn/Swoole/Client/setting.md)
:::  

### connect()  
连接到远程服务器.  
方法原型:connect(string $host, int $port, float $timeout = 0.5, int $flag = 0): bool  
参数说明:  
- $host 服务器地址,可输入ip或域名
- $port 服务器端口
- $timeout 连接超时时间
- $flag   
   - udp客户端时,代表是否启用 `udp_connect` 设定此选项后将绑定 `$host` 与 `$port`,该客户端将会丢弃非指定host:port的数据包.  
   - tcp类型时,`$flag=1` 代表设置为非阻塞 `socket`,之后此客户端操作会变成`异步IO`,此方法会立即返回.在调用 `send/recv` 前必须使用 `swoole_client_select` 来检测是否连接成功.
::: warning
如果连接成功将返回true,失败返回false.    
在异步io模式,会立即返回true,但不一定连接成功.   
:::
其他情况:    
- 失败重连.  
当`connect`失败之后,如果需要重连,需要先调用`close`方法关闭.然后再进行`connect`连接. 
- udp模式`connect`
在协议上,udp是无需连接的,调用`connect`后,将创建一个`socket`,任何客户端都可以向该端口发送数据包.  
但如果`$flag=1`,则调用connect后,只接受当前connect传入的ip:port的数据.其他客户端的数据将丢弃.  

### isConnected()
检测当前客户端的连接状态.   
方法原型:isConnected(): bool;    
::: warning
该方法只能获取到应用层已经连接成功的状态,例如客户端当前连接成功,并且没有执行`close`方法关闭.  
但是如果连接成功之后,服务端动断开连接,该方法并不能获取到连接失败.  
只有当执行`send/recv`方法时,才能获取到最终的连接状态.  
:::
### getSocket()  
获取底层的`socket`句柄.通过句柄可设置底层的`socket`参数.    
方法原型:getSocket();    
```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo '配置socket失败:'. socket_strerror(socket_last_error()) . PHP_EOL;
}
```
::: warning
此方法使用必须在编译时开启`--enable-sockets`选项.  
:::
### getSockName()
获取客户端本地的`host:port`
方法原型:getsockname(): array|false;   
```php
<?php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('192.168.159.1', 60000, -1)) {
    exit("连接失败: {$client->errCode}\n");
}
var_dump($client->getsockname());
//array(2) {
//  ["port"]=>
//  int(57420)
//  ["host"]=>
//  string(15) "192.168.159.131"
//}
```
### getPeerName() 
获取对端socket的`ip:port`
方法原型:getpeername(): array|false;   
::: warning
- 只支持 `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM` 类型.
- 此方法必须在调用`recv`方法之后使用.
:::
### getPeerCert()  
获取服务端证书信息.成功将返回`x509`证书字符串数据,否则false.       
方法原型:getPeerCert(): string|false;    
::: warning
- 只有在ssl握手完成后才能使用该方法.  
- 编译时需要开启`--enable-openssl`选项.  
::: 
### verifyPeerCert()
验证服务端证书.  
方法原型:verifyPeerCert();    
### send()
发送数据到服务器.只有连接成功才可以发送.    
方法原型:send(string $data): int|false;    
参数说明:   
- $data 需要发送的数据.  
::: warning
成功返回已经发送的数据长度,失败返回false.  
如果发送的数据包过大,可能会造成阻塞等待.  
:::
### sendto()
向任意`ip:port`发送`udp`数据包.  
方法原型:sendto(string $ip, int $port, string $data): bool;  
参数说明:  
- $ip  目标服务器ip
- $port 目标服务器端口
- $data 发送的数据包(不能超过64kb).  
::: warning
只支持`SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6`类型.  
:::
### sendfile()
发送文件到服务器,本方法基于`sendfile操作系统调用`.  
方法原型:sendfile(string $filename, int $offset = 0, int $length = 0): bool;    
参数说明:   
- $filename 文件路径
- $offset 偏移量,默认为文件开始
- $length  发送长度,默认为整个文件
::: warning
此方法不能用于`udp客户端`和`ssl隧道加密连接`.
:::
### recv()  
从服务端接收数据.  
方法原型:recv(int $size = 65535, int $flags = 0): string | false    
参数说明:    
- $size  接收数据的缓存区最大长度
- $flags 额外参数设置,例如:`Client::MSG_WAITALL`
::: warning
    - 成功后将返回接收到的字符串
    - 连接关闭返回空字符串
    - 失败返回false,可通过`$client->errCode`获得原因
    - 当客户端设置`EOF/Length`检测后,无需设置`$size/$flags`参数,底层会自动分包,如果收到不符合协议错误的数据包,将会返回空字符串.  
:::
### close()
关闭连接.  
方法原型:close(bool $force = false): bool   
参数说明:  
- $force 强制关闭.  
::: warning
在`client`析构时,会自动调用`close`.
调用`close`之后,不能继续调用`connect`,需要重新new一个对象.  
:::
### enableSSL()
动态开启 `SSL` 隧道加密.  
方法原型:enableSSL(): bool    
### swoole_client_select
使用`select`做io事件循环
方法原型:int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);  
参数说明:  
- $read   //可读文件描述符
- $write   //可写文件描述符
- $error   //错误描述符
- $timeout   //超时时间  

调用成功后,会返回事件的数量,并修改 `$read/$write/$error` 数组.
可使用 `foreach` 遍历数组,然后执行 `$item->recv/$item->send/$item->close` 来操作socket.
`swoole_client_select` 返回`0` 表示在规定的时间内,没有任何 `IO` 可用,`select` 调用已超时.

```php
<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/2/28 0028
 * Time: 16:07
 */

$clientList = [];

for ($i = 0; $i < 5; $i++) {
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
    $result = $client->connect('192.168.159.1', 60000, 5, 0);
    if (!$result) {
        echo "连接服务器失败:" . $client->errCode."\n";
    } else {
        echo "连接成功\n";
        $client->send("hello easyswoole.\n");
        $clientList[$client->sock] = $client;
    }
}

while (!empty($clientList)) {
    $write = $error = array();
    $read = array_values($clientList);
    //批量监听已读事件(当这些服务端有数据返回时,会触发事件)
    $n = swoole_client_select($read, $write, $error, 0.6);
    //$n>0说明有$n个事件触发
    if ($n > 0) {
        /**
         * @var $client \Swoole\Client
         */
        //通过遍历可读事件,获取所有有数据返回的客户端.
        foreach ($read as $index => $client) {
            //接收服务端返回的数据
            $data= $client->recv();
            echo "收到回复 #{$client->sock}: " . $data . "\n";
            //如果为'',代表连接关闭
            if($data===''){
                echo "{$client->sock}连接关闭\n";
                unset($clientList[$client->sock]);
            }
        }
    }
}


```
