---
title: easyswoole 组件库-httpClient
meta:
  - name: description
    content: easyswoole 组件库-httpClient
  - name: keywords
    content: easyswoole 组件库-httpClient
---

# HttpClient

协程`Http`客户端，基于`\Swoole\Http\Client`实现，在协程内快速发起`http`请求。

## 安装

> composer require easyswoole/http-client

## 请求

需要在协程环境内发起请求。

### 请求实例

```php
$client = new \EasySwoole\HttpClient\HttpClient('http://easyswoole.com');
```

### 设置Url

可在实例化的时候，传入`Url`，或者如下：

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setUrl('http://easyswoole.com');
```

### 设置query

通过`url`传入.

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setUrl('http://easyswoole.com?a=1');
```

通过方法传入.

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setQuery(['a' => 1]);
```

注意：

`setQuery`方法将你原本`url`的参数也带过来.

### 设置Ssl

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setEnableSSL(true);
```

### 设置等待超时时间

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setTimeout(5);
```

### 设置连接超时时间

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setConnectTimeout(10);
```

### 设置Header

设置单项：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setHeader('test','test');
```

设置多项：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setHeaders(['test' => 'test']);
```

参数：
- `$isMerge` 默认：`true`，`false`清空原有`Header`重新赋值。
- `$strtolower` 默认：`true`，`false`不进行小写转换。

### 设置Cookie

设置单项：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->addCookie('test','test');
```

设置多项：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->addCookies(['test' => 'test']);
```

参数：
- `$isMerge` 默认：`true`，`false`清空原有`Cookie`重新赋值。

### 设置XMLHttpRequest

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setXMLHttpRequest();
```

### 设置ContentType

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setContentType($client::CONTENT_TYPE_APPLICATION_XML);
```

`json`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setContentTypeJson();
```

`xml`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setContentTypeXml();
```

`from-data`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setContentTypeFormData();
```

`from-urlencode`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setContentTypeFormUrlencoded();
```

### 设置BasicAuth

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setBasicAuth('admin','admin');
```

### 设置KeepAlive

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setKeepAlive(true);
```


### 设置客户端配置

单个设置：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setClientSetting('bind_address','127.0.0.1');
```

批量设置：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setClientSettings([
    'bind_address'=>'127.0.0.1',
    'bind_port'=>'8090'
]);
```

### 设置FollowLocation

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->enableFollowLocation(5);
```

参数：
- `$maxRedirect` 默认5，表示最多根据30x状态码进行的重定向次数。0 为关闭。

### 设置允许自签证书

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslVerifyPeer(true,true);
```

### 设置服务器主机名称
与ssl_verify_peer配置配合使用

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslHostName('');
```

### 设置验证用的Ssl证书

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslCafile('');
```

### 设置Ssl证书目录

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslCapath('');
```

### 设置Ssl证书文件

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslCertFile('');
```

### 设置Ssl证书私钥文件

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSslKeyFile('');
```

### 设置代理

http代理：

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setProxyHttp('127.0.0.1','1087','user','pass');
```

socks5代理：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setProxySocks5('127.0.0.1','1086','user','pass');
```

### 设置端口绑定

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->setSocketBind('127.0.0.1','8090');
```

### GET

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->get();
```

参数：
- `$headers` 设置`Header`

### HEAD

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->head();
```

### DELETE

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->delete();
```

参数：
- `$headers` 设置`Header`

### PUT

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->put();
```

参数：
- `$data` 请求的数据
- `$headers` 设置`Header`

### POST

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->post();
```

`post-xml`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->postXml();
```

`post-json`：
```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->postJson();
```

参数：
- `$data` 请求的数据
- `$headers` 设置`Header`

### PATCH

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->patch();
```

参数：
- `$data` 请求的数据
- `$headers` 设置`Header`

### OPTIONS

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->options();
```

参数：
- `$data` 请求的数据
- `$headers` 设置`Header`

### Download

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->download('./test.png');
```

参数：
- `$filename` 保存路径
- `$offset` 写入偏移量
- `$httpMethod` 请求方法
- `$rawData` 请求数据
- `$contentType` 设置`ContentType`

### 上传文件

```php
/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->post([
    'file' => new \CURLFile(__FILE__)
]);
```

分片上传：
```php

$file = new EasySwoole\HttpClient\Bean\CURLFile('file',__FILE__);

// 设置表单的名称
$file->setName('file');

// 设置要文件的路径

$file->setPath(__FILE__);

// 设置文件总大小
$file->setLength(filesize(__FILE__));

// 设置offset（分片上传的关键）
$file->setOffset(0);

// 设置文件类型
$file->setType('image/png');

/** @var \EasySwoole\HttpClient\HttpClient $client **/
$client->post([
    'file' => $file
]);
```

## 响应

以上快速发起`http`请求成功后，如（`GET`，`POST`），将会返回`EasySwoole\HttpClient\Bean\Response`。

### 获取响应体

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getBody();
```

当响应体为`json`，解析：

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->json();
```

参数：
- `$assoc` 默认`false`，`false`为`object`，`true`为数组。

当响应体为`jsonp`，解析：

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->jsonp();
```

参数：
- `$assoc` 默认`false`，`false`为`object`，`true`为数组。


当响应体为`xml`，解析：

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->xml();
```

参数：
- `$assoc` 默认`false`，`false`为`object`，`true`为数组。

### 获取错误码

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getErrCode();
```

### 获取错误信息

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getErrMsg();
```

### 获取响应状态码

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getStatusCode();
```

### 获取响应头及设置的Cookie

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getSetCookieHeaders();
```

### 获取请求的Cookie及响应头

```php
/** @var \EasySwoole\HttpClient\Bean\Response $response **/
$response->getCookies();
```

## WebSocket-Client

```php
$client = new \EasySwoole\HttpClient\HttpClient('127.0.0.1:9501');
$upgradeResult = $client->upgrade(true);
$frame = new \Swoole\WebSocket\Frame();
//设置发送的消息帧
$frame->data = json_encode(['action' => 'hello','content'=>['a'=>1]]);
$pushResult = $client->push($frame);
$recvFrame = $client->recv();
//将返回bool或一个消息帧，可自行判断
var_dump($recvFrame);
```

> recv只会接收一次服务器的消息，如果需要一直接收，请增加while(1)死循环。
