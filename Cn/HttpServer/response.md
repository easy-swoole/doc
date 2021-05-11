---
title: easyswoole http响应对象
meta:
  - name: keywords
    content: easyswoole http请求对象|easyswoole response
---
# Response 对象

响应客户端的请求

## 生命周期
`Response` 对象在系统中以单例模式存在，自收到客户端 `HTTP` 请求时自动创建，直至请求结束自动销毁。`Response` 对象完全符合 [PSR-7](https://www.php-fig.org/psr/psr-7/) 中的所有规范。
其他细节方法，有兴趣的同学可以在 `IDE` 中查看对应的代码。

在控制器中可以通过 `$this->response()` 获取到 `Response` 对象。

```php
$response = $this->response();
```

## 核心方法

### write

向客户响应数据。

```php
// 向客户端响应 字符串数据
$this->response()->write('hello world');
```

> 注意：当向客户端响应中文字符串时，请务必设置响应头，并在 `Content-Type` 属性中指定编码，否则将显示乱码。

示例：
```php
// 向客户端响应 中文字符串
// 设置响应头，并在 `Content-Type` 属性中指定编码
$this->response()->withHeader('Content-Type', 'text/html;charset=utf-8');
$this->response()->write('你好! easyswoole!');

// 向客户端响应 json 字符串
$this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
$this->response()->write(json_encode(['name' => 'easyswoole']));
```

::: tip
  在控制器中可直接调用 `$this->writeJson($statusCode = 200, $result = null, $msg = null)` 方法向客户端响应 json 字符串
:::

示例：
```php
// 在 `easyswoole` 控制器中，向客户端响应 json 字符串
$this->writeJson(200, ['name' => 'easyswoole'], 'success!');
```


### redirect

将请求重定向至指定的 `URL`

```php
$this->response()->redirect("/newURL/index.html");
```

### setCookie

向客户端设置一个 `Cookie`，用法与 `PHP` 原生的 `setcookie` 一致。

```php
$this->response()->setCookie(string $name, $value = null, $expire = null,string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '')
```

### getSwooleResponse

获取原始的 `swoole_http_response` 实例。

```php
$swooleResponse = $this->response()->getSwooleResponse();
```

### end

结束对该次 `HTTP` 请求响应，结束之后，无法再次向客户端响应数据。

```php
$this->response()->end();
```

> 注意：和 `Swoole` 原生 `swoole_http_response` 实例的 `end` 方法有所区别。 


### isEndResponse

判断该次 `HTTP` 请求是否结束响应，当你不知道是否已经结束响应时，可通过该方法判断是否能再次向客户端响应数据：

```php
if (!$this->response()->isEndResponse()) {
    $this->response()->write('继续发送数据');
}
```

### withStatus

向客户端发送 `HTTP` 状态码。

```php
$this->response()->withStatus($statusCode);
```

::: warning 
  注意：`$statusCode` 必须为标准的 `HTTP 允许状态码`，具体请见 `Http Message` 中 的 [Status 对象](https://github.com/easy-swoole/http/blob/2.x/src/Message/Status.php)。
:::

### withHeader

用于向 `HTTP` 客户端发送一个 `header`。

```php
$this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
```

## 其他响应

### 向客户端响应文件流，实现文件下载

1. 实现 `excel` 文件自动下载

示例如下：在控制器中响应客户端，实现 `excel` 文件自动下载

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        // 要下载 excel 文件的指定路径，例如这里是项目根目录下的 test.xlsx 文件
        $this->response()->readFile(EASYSWOOLE_ROOT . '/test.xlsx');
        // 设置文件流内容类型，这里以 xlsx 为例
        $this->response()->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // 设置要下载的文件名称，一定要带文件类型后缀
        $this->response()->withHeader('Content-Disposition', 'attachment;filename=' . 'download_test.xlsx');
        $this->response()->withHeader('Cache-Control', 'max-age=0');
        $this->response()->end();
    }
}
```

访问 `http://localhost:9501/` 就会自动下载 `download_test.xlsx` 文件了。

> 注意：这里必须使用 `withHeader` 设置响应头，一定不能使用 `php-fpm` 下的 `header` 函数设置。
