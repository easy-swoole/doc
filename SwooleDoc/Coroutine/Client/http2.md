---
title: easyswoole swoole-协程http2客户端
meta:
  - name: description
    content: easyswoole swoole-协程http2客户端
  - name: keywords
    content: easyswoole swoole-协程http2客户端|easyswoole|swoole|coroutine
---

# Coroutine\Http2\Client

## 方法

### __construct
作用：构造       
方法原型：__construct(string $host, int $port, bool $ssl = false): void;     
参数：
- $host 目标ip或者域名
- $port 目标端口 http一般为80 https一般为443
- $ssl 是否开启 tls/ssl 隧道加密 如果网站为https 这里必须为true

> `$ssl` 需依赖 `openssl` 编译 `swoole` 时启用 `--enable-openssl`

### set
作用：设置客户端参数      
方法原型：set(array $options): void;     

### connect
作用：连接目标服务器 发起connect 底层自动会进行协程调度        
方法原型：connect(): bool;   

### send
作用：向服务端发送请求 可以同时发起多个请求      
方法原型：send(\Swoole\Http2\Request $request): int|false;       
参数：
- $request `\Swoole\Http2\Request` 对象

Request对象：
- `headers` 数组 `http` 请求头
- `method` 字符串 请求方法 如 `GET`,`POST`
- `path` 字符串 设置 `url` 路径 如 `/index?a=1` 必须以/开始
- `cookies` 数组 设置 `cookies`
- `data` 请求的 `body` 为字符串时将直接作为 `RAW form-data` 进行发送
- `data` 为数组的时候 底层自动打包为 `x-www-form-urlencoded` 格式的 `POST` 内容 并设置 `Content-Type为application/x-www-form-urlencoded`
- `pipeline` 布尔 如设置为 `true`，发送完 `$request` 后 不关闭 `stream` 可以继续写入数据内容

### recv
作用：接收请求     
方法原型：recv(float $timeout): Swoole\Http2\Response;       
参数：
- $timeout 超时时间

Response对象：
```php
/**@var $response Swoole\Http2\Response */
var_dump($response->statusCode); // 服务器发送的Http状态码 如200、502
var_dump($response->headers); // 服务器发送的Header信息
var_dump($response->cookies); // 服务器设置的COOKIE信息
var_dump($response->set_cookie_headers); // 服务器端返回的原始COOKIE信息，包括了domain和path项
var_dump($response->data); // 服务器发送的响应包体
```

### write
作用：向服务端发送数据帧        
方法原型：write(int $streamId, mixed $data, bool $end = false): bool;        
参数：
- $streamId 流编号 调用 `send` 方法返回
- $data 数据帧内容 字符串数组都可以
- $end 是否关闭流

### read
作用：和 `recv` 基本一致 用于 `pipeline` 类型的响应        
方法原型：read(float $timeout): Swoole\Http2\Response;       
参数：
- $timeout 超时时间

### close
作用：关闭连接     
方法原型：close(): bool;

## 简单示例代码
```php
<?php
Swoole\Coroutine::create(function (){
    $client = new \Swoole\Coroutine\Http2\Client('blog.gaobinzhan.com',443,true);

    $client->connect();
    $request = new \Swoole\Http2\Request();
    $request->path = '/';
    $request->method = 'GET';
    $client->send($request);
    /** @var \Swoole\Http2\Response $response */
    $response = $client->recv();
    var_dump($response->data);
});
```