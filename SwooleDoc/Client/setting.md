---
title: easyswoole swoole-客户端配置
meta:
  - name: description
    content: easyswoole swoole-客户端配置
  - name: keywords
    content: easyswoole swoole-客户端配置|easyswoole|swoole
---

## 客户端配置项

### 数据包协议解析类配置

为了解决[tcp粘包](/Cn/Socket/tcpSticky.md)粘包问题而出现的配置项,`client`与`server`都有类似的配置项.  

例如:  

```php
<?php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
//结束符检测
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",//检测必须有\r\n的结束符
    'package_max_length' => 1024 * 1024 * 2,//最大协议长度
));
//封包长度检测
//$client->set(array(
//    'open_length_check' => 1,
//    'package_length_type' => 'N',
//    'package_length_offset' => 0, //第N个字节是包长度的值
//    'package_body_offset' => 4, //第几个字节开始计算长度
//    'package_max_length' =>  1024 * 1024 * 2, //协议最大长度
//));
if (!$client->connect('192.168.159.1', 60000, -1)) {
    exit("连接失败: {$client->errCode}\n");
}
while(1){
    $data = $client->recv();

    var_dump($data);
}

```

::: warning
- 当开启协议解析后,`recv`的`$size`参数将不起作用.每次`recv`返回的都是一个完整的数据包  
:::


### ssl相关配置
```php
<?php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file'         => __DIR__ . '/ca/client-cert.pem',//加密证书路径
    'ssl_key_file'          => __DIR__ . '/ca/client-key.pem',//私钥路径
    'ssl_allow_self_signed' => true,//允许自签名证书.
    'ssl_verify_peer'       => true,//验证服务器端证书。
    'ssl_cafile'            => __DIR__ . '/ca/ca-cert.pem',// 用来验证远端证书所用到的 CA 证书
    'ssl_capath'            => __DIR__ . '/ca/ca-cert.pem',//如果`ssl_cafile` 路径错误时,会在 `ssl_capath` 所指定的目录搜索适用的证书.
));
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("连接失败 : {$client->errCode}\n");
}
echo "连接成功\n";
$client->send("hello easyswoole\n");
echo $client->recv();

```

### open_tcp_nodelay
关闭 `Nagle` 合并算法
```php
$client->set(array(
  'open_tcp_nodelay' => true,
));

```

### package_length_func
设置长度计算函数,此配置项跟`server`使用方法一致.  

### socks5_proxy
配置 `socks5` 代理.  
```php
$client->set([
  'socks5_host' => '192.168.159.1',//必填
  'socks5_port' => 1080,//必填
  'socks5_username' => 'userName',//选填
  'socks5_password' => 'userPassword',//选填
]);
```
### http_proxy
配置 `http` 代理.
```php
$client->set([
    'http_proxy_host' => '192.168.159.1',//代理host
    'http_proxy_port' => 1080,//代理端口
    'http_proxy_user' => 'tioncico',//选填,验证user
    'http_proxy_password' => '123456',//选填,验证密码
]);

```
### bind
使得该客户端强制绑定本机的ip和端口.  

```php
$client->set([
  'bind_address' => '192.168.1.100',
  'bind_port' => 36002,
]);
```
