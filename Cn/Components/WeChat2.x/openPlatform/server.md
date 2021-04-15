---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 服务端

## 第三方平台推送事件

公众号第三方平台推送的有四个事件：

> 如已经授权的公众号、小程序再次进行授权，而未修改已授权的权限的话，是没有相关事件推送的。

授权成功 `authorized`

​授权更新 `updateauthorized`

​授权取消 `unauthorized`

​VerifyTicket `component_verify_ticket`

`SDK` 默认会处理事件 `component_verify_ticket` ，并会缓存 `verify_ticket` 所以如果你暂时不需要处理其他事件，直接这样使用即可：

在 `EasySwoole` 框架中配置服务端验证，示例代码如下：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WeChat\Factory;

class Index extends Controller
{
    public function index()
    {
        $config = [
            // 开放平台第三方平台 APPID
            'appId' => 'wxefe41fdeexxxxxx', 
            
            // 开放平台第三方平台 Token
            'token' => 'dczmnau31ea9nzcnxxxxxxxxx',
            
            // 开放平台第三方平台 AES Key
            'aesKey' => 'easyswoole',
           
            // 开放平台第三方平台 Secret
            'secret' => 'your-AppSecret'
        ];
        
        // 开放平台
        $openPlatform = Factory::openPlatform($config);
        
        $server = $openPlatform->server;

        /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request */
        $psr7Request = $this->request();
        
        // $psr7esponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
        $psr7Response = $server->serve($psr7Request);
        
        $this->response()->withStatus($psr7Response->getStatusCode());

        // PSR-7 的 Header 并不是单纯的 k => v 结构
        foreach ($psr7Response->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }
        $this->response()->write($psr7Response->getBody()->__toString());
    }
}
```

使用原生 `Swoole` 配置服务端验证，示例代码如下：

`server.php` 的实现形式下面就以原生 `Swoole` 的 `http_server` 来启动一个服务，伪代码内容如下：

```php
<?php

use EasySwoole\WeChat\Factory;

require_once __DIR__ . '/vendor/autoload.php';

$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

    $config = [
        // 开放平台第三方平台 APPID
        'appId' => 'wxefe41fdeexxxxxx', 
        
        // 开放平台第三方平台 Token
        'token' => 'dczmnau31ea9nzcnxxxxxxxxx',
        
        // 开放平台第三方平台 AES Key
        'aesKey' => 'easyswoole',
       
        // 开放平台第三方平台 Secret
        'secret' => 'your-AppSecret'
    ];
    
    // 开放平台
    $openPlatform = \EasySwoole\WeChat\Factory::openPlatform($config);

    $server = $openPlatform->server;

    // 此处为实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
    /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request  */
    $psr7Request = new XxxReuest($request); // 伪代码

    /**
     * @var \Psr\Http\Message\ResponseInterface $psr7Response
     * forceValidate() 表示启用请求验证，以确保请求来自微信发送。默认不启用验证
     * serve() 会解析本次请求后回调之前注册的事件（包括 AES 解密和解析 XML）
     * serve() 接受一个显式实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
     */
    $psr7Response = $server->serve($psr7Request);

    /**
     * $replyResponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
     * 下面是一个原生 swoole 的响应方法
     */
    $response->status($psr7Response->getStatusCode());

    /**
     * PSR-7 的 Header 并不是单纯的 k => v 结构
     */
    foreach ($psr7Response->getHeaders() as $name => $values) {
        $response->header($name, implode(", ", $values));
    }

    // 将响应输出到客户端
    $response->write($psr7Response->getBody()->__toString());
});

$http->start();
```

## 自定义消息处理器

> 消息处理器详细说明见 [公众号开发 - 服务端章节](/Components/WeChat2.x/officialAccount/server.md)

```php
<?php

use EasySwoole\WeChat\OpenPlatform\Server\Guard;
use EasySwoole\WeChat\Kernel\Messages\Message;

$server = $openPlatform->server;
        
// 处理授权成功事件
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    // ...
}, Guard::EVENT_AUTHORIZED);

// 处理授权更新事件
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    // ...
}, Guard::EVENT_UPDATE_AUTHORIZED);

// 处理授权取消事件
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    // ...
}, Guard::EVENT_UNAUTHORIZED);
```

### 使用示例（在 EasySwoole 框架中使用）

> 使用示例 1：在 `App\HttpController\Router.php` （即路由）中使用：

示例代码如下：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\WeChat\OpenPlatform\Server\Guard;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // 假设你的开放平台第三方平台设置的授权事件接收 URL 为: https://easyswoole.wechat.com/openPlatform （其他事件推送同样会推送到这个 URL）
        $routeCollector->post('/openPlatform', function (Request $request, Response $response) {
            
            // $openPlatform 为你实例化的开放平台对象，此处省略实例化步骤
            // $psr7esponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
            $psr7Response = $openPlatform->server->serve($request); // Done!

            $response->withStatus($psr7Response->getStatusCode());

            // PSR-7 的 Header 并不是单纯的 k => v 结构
            foreach ($psr7Response->getHeaders() as $name => $values) {
                $response->withHeader($name, implode(", ", $values));
            }
            $response->write($psr7Response->getBody()->__toString());
            
            
            return false;
        });


        // 处理事件
        $routeCollector->post('/openPlatform', function (Request $request, Response $response) {
            
            // $openPlatform 为你实例化的开放平台对象，此处省略实例化步骤
            $server = $openPlatform->server;

            // 处理授权成功事件，其他事件同理
            $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
                // $message 为微信推送的通知内容，不同事件不同内容，详看微信官方文档
                // 获取授权公众号 AppId： $message['AuthorizerAppid']
                // 获取 AuthCode：$message['AuthorizationCode']
                // 然后进行业务处理，如存数据库等...
            }, Guard::EVENT_AUTHORIZED);

            // $psr7esponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
            $psr7Response = $server->serve($request); // Done!
            
            $response->withStatus($psr7Response->getStatusCode());

            // PSR-7 的 Header 并不是单纯的 k => v 结构
            foreach ($psr7Response->getHeaders() as $name => $values) {
                $response->withHeader($name, implode(", ", $values));
            }
            $response->write($psr7Response->getBody()->__toString());
            
            return false;
        });
    }
}
```

> 使用示例 2：在 `App\HttpController\Index.php` （即控制器类）中使用，用户可在自定义其他控制器中实现：

假设你的开放平台第三方平台设置的授权事件接收 `URL` 为: `https://easyswoole.wechat.com/openPlatform` （其他事件推送同样会推送到这个 `URL`）

示例代码如下：

首先在 `App\HttpController\Router.php` 中定义路由：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\WeChat\OpenPlatform\Server\Guard;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // 假设你的开放平台第三方平台设置的授权事件接收 URL 为: https://easyswoole.wechat.com/openPlatform （其他事件推送同样会推送到这个 URL）
        $routeCollector->post('/openPlatform', '/Index/openPlatform');
    }
}
```

然后在 `App\HttpController\Index.php` 控制器中处理事件：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WeChat\OpenPlatform\Server\Guard;

class Index extends Controller
{
    public function openPlatform()
    {
        // $openPlatform 为你实例化的开放平台对象，此处省略实例化步骤
        $server = $openPlatform->server;

        // 处理授权成功事件，其他事件同理
        $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
            // $message 为微信推送的通知内容，不同事件不同内容，详看微信官方文档
            // 获取授权公众号 AppId： $message['AuthorizerAppid']
            // 获取 AuthCode：$message['AuthorizationCode']
            // 然后进行业务处理，如存数据库等...
        }, Guard::EVENT_AUTHORIZED);

        /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request */
        $psr7Request = $this->request();

        // $psr7esponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
        $psr7Response = $server->serve($psr7Request);

        $this->response()->withStatus($psr7Response->getStatusCode());

        // PSR-7 的 Header 并不是单纯的 k => v 结构
        foreach ($psr7Response->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }
        $this->response()->write($psr7Response->getBody()->__toString());
    }
}
```
