---
title: easyswoole http请求对象
meta:
  - name: keywords
    content: easyswoole http请求对象|easyswoole request
---
# Request 对象

接收客户端的 `HTTP` 请求对象

## 生命周期

`Request` 对象在系统中以单例模式存在，自收到客户端 `HTTP` 请求时自动创建，直至请求结束自动销毁。`Request` 对象完全符合 [PSR-7](https://www.php-fig.org/psr/psr-7/) 中的所有规范。

## 核心方法

### getRequestParam()

用于获取用户通过 `POST` 或者 `GET` 提交的参数（注意：若 `POST` 与 `GET` 存在同键名参数，则以`POST` 为准）。

示例：

```php
// 在控制器中 可以通过 $this->request() 获取到 Request 对象
// $request = $this->request()；

// 获取 `POST` 或者 `GET` 提交的所有参数
$data = $request->getRequestParam();
var_dump($data);

// 获取 `POST` 或者 `GET` 提交的单个参数
$orderId = $request->getRequestParam('orderId');
var_dump($orderId);

// 获取 `POST` 或者 `GET` 提交的多个参数
$mixData = $request->getRequestParam("orderId","type");
var_dump($mixData);
```

### getSwooleRequest()

获取当前的 `swoole_http_request` 对象。

### getCookieParams()

获取 `HTTP` 请求中的 `cookie` 信息

```php
// 获取所有 `cookie` 信息
$all = $request->getCookieParams();
var_dump($all);

// 获取单个 `cookie` 信息
$who = $request->getCookieParams('who');
var_dump($who);
```

### getUploadedFiles()

获取客户端上传的全部文件信息。

```php
// 获取一个上传文件，返回的是一个 \EasySwoole\Http\Message\UploadFile 的对象
$img_file = $request->getUploadedFile('img');

// 获取全部上传文件返回包含 \EasySwoole\Http\Message\UploadFile 对象的数组
$data = $request->getUploadedFiles();
var_dump($data);
```

点击查看 [UploadFile对象](./uploadFile.html)

### getBody()

获取以非 `form-data` 或 `x-www-form-urlenceded` 编码格式 `POST` 提交的原始数据，相当于PHP中的 `$HTTP_RAW_POST_DATA`。

### 获得 get 内容

```php
$get = $request->getQueryParams();
```

### 获得 post 内容

```php
$post = $request->getParsedBody();
```

### 获得 raw 内容

```php
$content = $request->getBody()->__toString();
$raw_array = json_decode($content, true);
```

### 获得头部

```php
$header = $request->getHeaders();
```

### 获得 server

```php
$server = $request->getServerParams();
```

### 获得 cookie

```php
$cookie = $request->getCookieParams();
```
