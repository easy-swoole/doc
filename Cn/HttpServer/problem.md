---
title: easyswoole web服务场景问题
meta:
  - name: description
    content: easyswoole web服务场景问题
  - name: keywords
    content: easyswoole web服务场景问题|swoole静态文件处理
---


# 常见问题

## 如何处理静态资源

### Apache URl rewrite

```apacheconf
<IfModule mod_rewrite.c>
  Options +FollowSymlinks
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  # RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]  fcgi下无效
  RewriteRule ^(.*)$  http://127.0.0.1:9501/$1 [QSA,P,L]
   # 请开启 proxy_mod proxy_http_mod request_mod
</IfModule>
```

### Nginx URl rewrite

```nginx
server {
    root /data/wwwroot/;
    server_name local.swoole.com;
    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-f $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```

### Swoole 静态文件处理器

详细请可查看 [配置文件 章节](/QuickStart/config.md)

修改配置文件的 `dev.php` 或者 `produce.php`，实现 `Swoole` 对静态文件进行处理。

```php
<?php

return [
    // ...... 这里省略
    'MAIN_SERVER' => [
        // ...... 这里省略
        'SETTING' => [
            // ...... 这里省略
            
            # 设置处理 Swoole 静态文件
            'document_root' => EASYSWOOLE_ROOT . '/Static/',
            'enable_static_handler' => true,
        ],
        // ...... 这里省略
    ],
    // ...... 这里省略
];
```

> 上述配置是假设你的项目根目录有个 Static 目录是用来放置静态文件的。具体的使用可查看 [https://wiki.swoole.com/#/http_server?id=document_root](https://wiki.swoole.com/#/http_server?id=document_root)


## 关于跨域处理

在框架的初始化事件 [initialize 事件](/FrameDesign/event/initialize.md) 中进行注册。

注册示例代码如下：

```php
public static function initialize()
{
    date_default_timezone_set('Asia/Shanghai');

    // onRequest v3.4.x+
    \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
        $origin = $request->getHeader('origin')[0] ?? '*';
        $response->withHeader('Access-Control-Allow-Origin', $origin);
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, token');
        if ($request->getMethod() === 'OPTIONS') {
             $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
             return false;
        }
        return true;
    });
}
```

`EasySwoole 3.4.x` 版本之前：可在项目根目录的 `EasySwooleEvent.php` 中看到 `onRequest` 及 `afterRequest` 方法.

## 如何获取 $HTTP_RAW_POST_DATA

```php
$content = $this->request()->getBody()->__toString();
$raw_array = json_decode($content, true);
```

## 如何获取客户端 IP

举例，如何在控制器中获取客户端 IP

```php
// 真实地址
// 获取连接的文件描述符
$fd = $this->request()->getSwooleRequest()->fd;
$ip = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer()->connection_info($fd);
var_dump($ip);

// header 地址，例如经过 nginx proxy 后
$ip2 = $this->request()->getHeaders();
var_dump($ip2);
```

## HTTP 状态码总为500

自 swoole **1.10.x** 和 **2.1.x** 版本起，执行 `http server` 回调中，若未执行 `response->end()`，则全部返回 `500` 状态码

## 如何 setCookie  

调用 `response` 对象的 `setCookie` 方法即可设置 `cookie`。`setCookie` 方法和原生 `setcookie` 用法一致。

```php
$this->response()->setCookie('name', 'value');
```

更多操作可看 [Response 对象](/HttpServer/response.md)


## 如何自定义 App 命名空间对应目录

只需要修改项目根目录的 `composer.json` 的自动加载的 `App` 命名空间对应的目录即可，然后执行 `composer dumpautolaod -o` 注册就行。

```
{
    // ... 这里省略
    "autoload": {
        "psr-4": {
            "App\\": "Application/"
        }
    }
}
```

## 如何启用 Https

通常建议使用 `Nginx` 或者 `Lb` 来配置证书，将 `https` 请求解析为 `http` 反代到 `swoole` 

如果你仅是测试使用，可以在配置文件 (`dev.php` 或者 `produce.php`) 中添加和修改以下配置来启用https。

```php
<?php

return [
    // ...... 这里省略
    'MAIN_SERVER' => [
        // ...... 这里省略
        'SOCK_TYPE' => SWOOLE_TCP | SWOOLE_SSL, // 默认是 SWOOLE_TCP
        'SETTING' => [
            'ssl_cert_file' => '证书路径，仅支持.pem格式',
            'ssl_key_file' => '私钥路径',
        ]
        // ...... 这里省略
    ],
    // ...... 这里省略
];
```

详细请可查看 [配置文件 章节](/QuickStart/config.md)


## DNS Lookup resolve timeout 错误
该错误一般存在于 `http` 客户端并发调用时产生，原因是 `dns` 效率慢，导致多线程获取 `dns` 时超时，包括不限于以下场景:  
 - mysql host 设置为域名形式，并且设置最小连接高于 2(很难看到，一般是 10 才会偶尔报错)
 - HTTPClient 多个协程同时并发
 - csp 并发编程
等

::: warning
  解决方法为:   
  在并发之前，预先使用 `Swoole\Coroutine::gethostbyname('www.baidu.com')`; 去查询一次`dns ip`，`swoole` 底层才会自动缓存该 `ip`。
:::
 
例如:
```php
Swoole\Coroutine::gethostbyname('www.baidu.com');
for ($j = 0; $j < 100; $j++) {
    go(function () use ($j) {
        for ($i = 0; $i < 1000; $i++) {
            $client = new Swoole\Coroutine\Http\Client('www.baidu.com', 443, true);
            $client->get('/');
            if (empty($client->errMsg)) {
//var_dump($client->getBody());
            } else {
                var_dump($client->errMsg);
            }
        }
    });
}
```

## CURL 发送 POST请求 EasySwoole 服务器端超时
- 出现原因：`CURL` 在发送较大的 `POST` 请求(例如: 上传文件)时会先发一个 `100-continue` 的请求，如果收到服务器的回应才会发送实际的 `POST` 数据。而 `swoole_http_server`(即 `EasySwoole` 的 `Http` 主服务) 不支持 `100-continue`，就会导致 `CURL` 请求超时。
- 解决方法：

> 方法1：关闭 `CURL` 的 `100-continue`，在 `CURL` 的 `Header` 中配置关闭 `100-continue` 选项。

示例代码(php):
```php
<?php
// 创建一个新cURL资源
$ch = curl_init();
// 设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:9501");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1); // 设置为POST方式
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // 关闭 `CURL` 的 `100-continue`
curl_setopt($ch, CURLOPT_POSTFIELDS, array('test' => str_repeat('a', 800000)));// POST 数据
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

```

> 方法2：使用 `Nginx` 做前端代理，由 `Nginx` 处理 `100-Continue`(针对无法关闭 `100-continue`时)

## http 服务中公共函数如何引入
很多开发小伙伴在开发过程中可能遇到疑惑，在 `EasySwoole` 怎么像 `ThinkPHP` 框架那样引入自定义的公共函数，接下来简单说明下引入方法，这边推荐借助 `composer` 的自动加载机制 （`Files`）实现。

修改项目根目录的 `composer.json` 文件的 `autoload.files` 选项，示例如下：

```json
{
    // ... 这里省略
    "autoload": {
        // ... 这里省略
        "files": ["App/Common/functions.php"]
    }
}
```

然后新建文件 `App\Common\functions.php`，在 `functions.php` 中编写自己的自定义函数，再在项目根目录执行 `composer dumpautoload` 完成自动加载，就可以在框架的任意位置进行调用函数了。

示例如下：

```php
<?php
// functions.php
if (!function_exists('helloEasySwoole')) {
    function helloEasySwoole()
    {
        echo 'Hello EasySwoole!';
    }
}

// ... 更多自定义函数
```

调用示例：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        \helloEasySwoole();
    }
}
```

> 自定义函数都可以放在这个文件中。
