---
title: Swoole Http service common problem
meta:
  - name: description
    content: Easyswoole, how to get the client IP
  - name: keywords
    content: swoole|swoole extension|swoole framework|Easyswoole|Get client IP|cross domain processing
---


# common problem

## How to handle static resources
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
        if (!-e $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```
## Swoole static file processor
```php
$server->set([
    'document_root' => '/data/webroot/example.com', // V4.4.0 or lower, here must be an absolute path
    'enable_static_handler' => true,
]);
```
Swoole comes with its own static file processor. The documentation can be found at https://wiki.swoole.com/wiki/page/783.html

## About cross-domain processing

Add the following code in the global event to block all requests to add cross-domain headers

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


## How to get $HTTP_RAW_POST_DATA
```php
$content = $this->request()->getBody()->__toString();
$raw_array = json_decode($content, true);
```
## How to get the client IP
For example, how to get the client IP in the controller
```php
//Real address
$ip = ServerManager::getInstance()->getSwooleServer()->connection_info($this->request()->getSwooleRequest()->fd);
var_dump($ip);
//Header address, for example after nginx proxy
$ip2 = $this->request()->getHeaders();
var_dump($ip2);
```

## HTTP status code is always 500
Since the swoole **1.10.x** and **2.1.x** versions, in the http server callback, if response->end() is not executed, all 500 status codes are returned.

## How to setCookie
Set the cookie by calling the setCookie method of the response object.
```php
  $this->response()->setCookie('name','value');
```
More operations can be seen [Response object] (response.md)


## How to customize the App name
Just modify the namespace registration of composer.json.
```
    "autoload": {
        "psr-4": {
            "App\\": "Application/"
        }
    }
```

