---
title: easyswoole swoole-request对象
meta:
  - name: description
    content: easyswoole swoole-request对象
  - name: keywords
    content: easyswoole swoole-request对象|easyswoole|swoole
---

## request对象
命名空间:`Swoole\Http\Request`.   
开启`http server` 之后,客户端请求服务端,服务端将解析客户端的数据,并保存到`request对象`中.  
例如`get`,`post`,`cookie`,`header`,等数据.  
 

## 属性  

### $fd 
属性说明:客户端的连接标识.  
类似于tcp的fd,每次连接,都会给该连接启用一个socket标识,通过fd,可以获取到该连接的详细参数.  
```php
<?php
$server = new Swoole\Http\Server("0.0.0.0", 9501);

//当浏览器发送http请求时,将会到这里回调
$server->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response)use($server) {
        $fd = $request->fd;
        var_dump($server->getClientInfo($fd));
        $response->end("ok");
});
echo "http服务器启动成功\n";
$server->start();
``` 
访问该服务器之后,服务端将打印:  
```bash
[root@localhost tioncico-doc-3.3.x]# php test.php 
http服务器启动成功
array(10) {
  ["server_port"]=>
  int(9501)
  ["server_fd"]=>
  int(4)
  ["socket_fd"]=>
  int(13)
  ["socket_type"]=>
  int(1)
  ["remote_port"]=>
  int(51762)
  ["remote_ip"]=>
  string(13) "192.168.159.1"
  ["reactor_id"]=>
  int(1)
  ["connect_time"]=>
  int(1583202623)
  ["last_time"]=>
  int(1583202623)
  ["close_errno"]=>
  int(0)
}
```

### $header 
属性说明:客户端发送的header头,将保存到`$header`属性.  
```php
<?php
//array(9) {
//  ["host"]=>
//  string(9) "x.cn:9501"
//  ["connection"]=>
//  string(10) "keep-alive"
//  ["pragma"]=>
//  string(8) "no-cache"
//  ["cache-control"]=>
//  string(8) "no-cache"
//  ["user-agent"]=>
//  string(115) "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36"
//  ["accept"]=>
//  string(39) "image/webp,image/apng,image/*,*/*;q=0.8"
//  ["referer"]=>
//  string(17) "http://x.cn:9501/"
//  ["accept-encoding"]=>
//  string(13) "gzip, deflate"
//  ["accept-language"]=>
//  string(14) "zh-CN,zh;q=0.9"
//}

```    

### $server 
属性说明:保存了请求服务器的相关信息,  
```php
//array(10) {
//  ["request_method"]=>
//  string(3) "GET"
//  ["request_uri"]=>
//  string(12) "/favicon.ico"
//  ["path_info"]=>
//  string(12) "/favicon.ico"
//  ["request_time"]=>
//  int(1583204593)
//  ["request_time_float"]=>
//  float(1583204593.5854)
//  ["server_protocol"]=>
//  string(8) "HTTP/1.1"
//  ["server_port"]=>
//  int(9501)
//  ["remote_port"]=>
//  int(52778)
//  ["remote_addr"]=>
//  string(13) "192.168.159.1"
//  ["master_time"]=>
//  int(1583204593)
//}

```
### $cookie 
属性说明:保存了请求客户端的cookie信息
```php
var_dump($request->cookie);
```  

### $get 
属性说明:保存了客户端请求的get数据
```php
var_dump($request->get);
```  

### $files 
属性说明:文件上传后,将保存文件的上传信息  
```php
var_dump($request->get);
```  

### $post 
属性说明:保存了客户端请求的post数据  

```php
var_dump($request->post);
```  

## 方法
### rawContent()
获取原始的`post`包体数据.  
::: warning
当`post`请求为`multipart/form-data`格式时,可通过该方法获取元素数据.等同于`php-fpm`下的`fopen('php://input')`
:::
### getData()
获取原始的`http`请求数据,包括`header`和`body`


