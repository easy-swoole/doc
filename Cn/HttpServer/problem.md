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
  #RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]  fcgi下无效
  RewriteRule ^(.*)$  http://127.0.0.1:9501/$1 [QSA,P,L]
   #请开启 proxy_mod proxy_http_mod request_mod
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
### Swoole静态文件处理器
```php
[       
    'document_root' => EASYSWOOLE_ROOT.'/Static/',
    'enable_static_handler' => true,
]
```
> 假设你的项目根目录有个Static目录是用来放置静态文件的。


## 关于跨域处理

在全局事件添加以下代码 拦截所有请求添加跨域头

```php
public static function onRequest(Request $request, Response $response): bool
{
    // TODO: Implement onRequest() method.
    $response->withHeader('Access-Control-Allow-Origin', '*');
    $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->withHeader('Access-Control-Allow-Credentials', 'true');
    $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    if ($request->getMethod() === 'OPTIONS') {
        $response->withStatus(Status::CODE_OK);
        return false;
    }
    return true;
}
```

## 如何获取$HTTP_RAW_POST_DATA
```php
$content = $this->request()->getBody()->__toString();
$raw_array = json_decode($content, true);
```
## 如何获取客户端IP
举例，如何在控制器中获取客户端IP
```php
//真实地址
$ip = ServerManager::getInstance()->getSwooleServer()->connection_info($this->request()->getSwooleRequest()->fd);
var_dump($ip);
//header 地址，例如经过nginx proxy后
$ip2 = $this->request()->getHeaders();
var_dump($ip2);
```

## HTTP 状态码总为500
自 swoole **1.10.x** 和 **2.1.x** 版本起，执行http server回调中，若未执行response->end(),则全部返回500状态码

## 如何setCookie  
调用response对象的setCookie方法即可设置cookie
```php
  $this->response()->setCookie('name','value');
```
更多操作可看[Response对象](response.md)


## 如何自定义App名称
只需要修改composer.json的命名空间注册就行
```
    "autoload": {
        "psr-4": {
            "App\\": "Application/"
        }
    }
```

## 如何启用Https
通常建议使用Nginx 或者Lb来配置证书，将https请求解析为http 反代到swoole 
如果你仅测试使用，可以在配置文件中添加和修改以下配置来启用https

```php
'MAIN_SERVER' => [
        'SOCK_TYPE' => SWOOLE_TCP | SWOOLE_SSL, // 默认是 SWOOLE_TCP
        'SETTING' => [
            'ssl_cert_file' => '证书路径，仅支持.pem格式',
            'ssl_key_file' => '私钥路径',
        ]
    ],

```
## DNS Lookup resolve timeout错误
该错误一般存在与 http客户端并发时产生,原因是dns效率慢,导致多线程获取dns时超时,包括不限于以下场景:  
 - mysql host设置为域名形式,并且设置最小连接高于2(很难看到,一般是10才会偶尔报错)
 - HTTPClient 多个协程同时并发
 - csp并发编程
等  
::: warning
解决方法为:   
在并发之前,预先使用Swoole\Coroutine::gethostbyname('www.baidu.com'); 去查询一次dns ip,swoole底层才会自动缓存该ip
:::
 
例如:
```php
    Swoole\Coroutine::gethostbyname('www.baidu.com');
    for ($j = 0; $j < 100; $j++) {
        go(function () use ($j) {
            for ($i = 0; $i < 1000; $i++) {
                $client = new Swoole\Coroutine\Http\Client('www.baidu.com',443,true);
                $client->get('/');
                if (empty($client->errMsg)){
//var_dump($client->getBody());
                }else{
                    var_dump($client->errMsg);
                }
            }
        });
    }
```