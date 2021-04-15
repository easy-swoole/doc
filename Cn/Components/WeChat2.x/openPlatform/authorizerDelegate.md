---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 代授权方实现业务

> 授权方已经把公众号、小程序授权给你的开放平台第三方平台了，接下来的代授权方实现业务只需一行代码即可获得授权方实例。

## 实例化

```php
<?php
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

```

### 获取授权方实例

```php
// 代公众号实现业务
$officialAccount = $openPlatform->officialAccount(string $appId, string $refreshToken);

// 代小程序实现业务
$miniProgram = $openPlatform->miniProgram(string $appId, string $refreshToken);
```

::: warning 
  - `$appId` 为授权方公众号 `APPID`，非开放平台第三方平台 `APPID`
  - `$refreshToken` 为授权方的 `refresh_token`，可通过 [获取授权方授权信息](/Components/WeChat2.x/openPlatform/getStart.md#使用授权码换取接口调用凭据和授权信息) 接口获得。
:::

### 帮助授权方管理开放平台账号

```php
<?php
// 代公众号实现业务
$account = $officialAccount->account;

// 代小程序实现业务
$account = $miniProgram->account;


// 创建开放平台账号
// 并绑定公众号或小程序
$result = $account->create();

// 将公众号或小程序绑定到指定开放平台帐号下
$result = $account->bindTo($openAppId);

// 将公众号/小程序从开放平台帐号下解绑
$result = $account->unbindFrom($openAppid);

// 获取公众号/小程序所绑定的开放平台帐号
$result = $account->getBinding();
```

::: warning
  授权第三方平台注册的开放平台帐号只可用于获取用户 `unionid` 实现用户身份打通。第三方平台不可操作（包括绑定/解绑）通过 `open.weixin.qq.com` 线上流程注册的开放平台帐号。公众号只可将此权限集授权给一个第三方平台，授权互斥。
:::

### 代码示例（在 EasySwoole 框架中使用）

> 使用示例 1：在 `App\HttpController\Router.php` （即路由）中使用：

示例代码如下：

```php
<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // 假设你的公众号消息与事件接收 URL 为：https://easyswoole.wechat.com/callback?appId=Xxxx ...
        $routeCollector->post('/callback', function (Request $request, Response $response) {

            $appId = $request->getQueryParam('appId');

            // $openPlatform 为你实例化的开放平台对象，此处省略实例化步骤
            $officialAccount = $openPlatform->officialAccount($appId);

            // 这里的 server 为授权方的 server，而不是开放平台的 server，请注意！！！
            $server = $officialAccount->server;

            $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
                return new \EasySwoole\WeChat\Kernel\Messages\Text('Welcome!');
            });

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

        // 调用授权方业务例子
        $routeCollector->get('/how-to-use', function (Request $request, Response $response) {

            $officialAccount = $openPlatform->officialAccount('已授权的公众号 APPID', 'Refresh-token');

            // 获取用户列表：
            $officialAccount->user->list();

            $miniProgram = $openPlatform->miniProgram('已授权的小程序 APPID', 'Refresh-token');

            // 根据 code 获取 session
            $miniProgram->auth->session('js-code');

            // 其他同理


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
        // 假设你的公众号消息与事件接收 URL 为：https://easyswoole.wechat.com/callback?appId=Xxxx ...
        $routeCollector->post('/callback', '/Index/callback');

        // 调用授权方业务例子
        $routeCollector->get('/how-to-use', '/Index/how_to_use');
    }
}
```

然后在 `App\HttpController\Index.php` 控制器中处理事件：

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WeChat\Kernel\Messages\Message;

class Index extends Controller
{
    // 假设你的公众号消息与事件接收 URL 为：https://easyswoole.wechat.com/callback?appId=Xxxx ...
    public function callback()
    {
        $appId = $this->request()->getQueryParam('appId');

        // $openPlatform 为你实例化的开放平台对象，此处省略实例化步骤
        $officialAccount = $openPlatform->officialAccount($appId);

        // 这里的 server 为授权方的 server，而不是开放平台的 server，请注意！！！
        $server = $officialAccount->server;

        $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
            return new \EasySwoole\WeChat\Kernel\Messages\Text('Welcome!');
        });
        
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

    // 调用授权方业务例子
    public function how_to_use()
    {
        $officialAccount = $openPlatform->officialAccount('已授权的公众号 APPID', 'Refresh-token');

        // 获取用户列表：
        $officialAccount->user->list();

        $miniProgram = $openPlatform->miniProgram('已授权的小程序 APPID', 'Refresh-token');

        // 根据 code 获取 session
        $miniProgram->auth->session('js-code');

        // 其他同理
    }
}
```

