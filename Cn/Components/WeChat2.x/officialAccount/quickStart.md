---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 快速开始

在我们已经成功安装完成 `EasySwoole WeChat SDK` 组件后，就可以很快地开始使用它了，当然你还是有必要明白 `PHP` 的基本知识，如命名空间等，我这里就不赘述了。

接下来我们以完成 `服务器端验证` 与 `接收响应用户发送的消息` 为例来演示，首先我们有必要先了解一下微信交互的运行流程：

具体交互流程如下：

```
                                 +-----------------+                       +---------------+
+----------+                     |                 |    POST/GET/PUT       |               |
|          | ------------------> |                 | ------------------->  |               |
|   user   |                     |  wechat server  |                       |  your server  |
|          | < - - - - - - - - - |                 |                       |               |
+----------+                     |                 | <- - - - - - - - - -  |               |
                                 +-----------------+                       +---------------+
```

其实我们要做的就是图中 **微信服务器把用户消息转到我们的自有服务器（虚线返回部分）** 后的处理过程。

## 服务端验证

在微信接入开始有一个 **"服务器验证"** 的过程，这一步其实就是 **微信服务器** 向 **我们服务器** 发起一个请求（上图实线部分），传了一个名称为 `echostr` 的字符串过来，我们只需要原样返回就好了。

作为开发者，你应该知道，微信后台只能填写一个服务器地址，所以 **服务器验证** 与 **消息的接收与回复**，都在这一个链接内完成交互。

考虑到这些，我们已经把验证这一步给封装到 `SDK` 里了，你可以完全忽略这一步。

下面我们来配置一个基本的服务端，这里假设我们自己的服务器域名叫 `easyswoolewechat.com`，并且我们在服务端已经安装好了一个 `EasySwoole` 框架，或者我们在服务器上准备一个文件 `server.php`（使用原生 `Swoole` 实现，下文只提供伪代码）。

### 使用 `EasySwoole` 框架配置服务端验证
 
以下为了演示，我们只在 `App\HttpController\Index` 控制器类下进行编码实现配置服务端验证，用户可自行选择其他控制器类进行编码实现。

在服务器的 `EasySwoole` 框架的 `HTTP` 服务的 `控制器` 中来配置一个基本的服务端：

配置主服务为 `HTTP` 服务，然后我们可以在 `App\HttpController\Index` 控制器类下编写 `server` 方法，编写如下代码实现服务端验证： 

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WeChat\Factory;

class Index extends Controller
{
    public function server()
    {
        $config = [
            // 微信公众平台后台的 appid
            'appId' => 'wxefe41fdeexxxxxx',

            // 微信公众平台后台配置的 Token
            'token' => 'dczmnau31ea9nzcnxxxxxxxxx',

            // 微信公众平台后台配置的 EncodingAESKey
            'aesKey' => 'easyswoole'
        ];

        $officialAccount = Factory::officialAccount($config);

        $server = $officialAccount->server;

        /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request */
        $psr7Request = $this->request();

        /**
         * @var \Psr\Http\Message\ResponseInterface $replyResponse
         * forceValidate() 表示启用请求验证，以确保请求来自微信发送。默认不启用验证
         * serve() 会解析本次请求后回调之前注册的事件（包括 AES 解密和解析 XML）
         * serve() 接受一个显式实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
         */
        $replyResponse = $server->forceValidate()->serve($psr7Request);

        /**
         * $replyResponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
         * 下面是一个原生swoole的响应方法
         */
        $this->response()->withStatus($replyResponse->getStatusCode());

        /**
         * PSR-7 的 Header 并不是单纯的 k => v 结构
         */
        foreach ($replyResponse->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }

        $this->response()->write($replyResponse->getBody()->__toString());
    }
}
```

### 使用原生 `Swoole` 配置服务端验证

`server.php` 的实现形式我就以原生 `Swoole` 的 `http_server` 来启动一个服务，伪代码内容如下：

```php
<?php

use EasySwoole\WeChat\Factory;

require_once __DIR__ . '/vendor/autoload.php';

$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

    $config = [
        // 微信公众平台后台的 appid
        'appId' => 'wxefe41fdeexxxxxx',

        // 微信公众平台后台配置的 Token
        'token' => 'dczmnau31ea9nzcnxxxxxxxxx',

        // 微信公众平台后台配置的 EncodingAESKey
        'aesKey' => 'easyswoole'
    ];

    $officialAccount = Factory::officialAccount($config);
    
    $server = $officialAccount->server;

    // 此处为实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
    /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request  */
    $psr7Request = new XxxReuest($request); // 伪代码

    /**
     * @var \Psr\Http\Message\ResponseInterface $replyResponse
     * forceValidate() 表示启用请求验证，以确保请求来自微信发送。默认不启用验证
     * serve() 会解析本次请求后回调之前注册的事件（包括 AES 解密和解析 XML）
     * serve() 接受一个显式实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
     */
    $replyResponse = $server->forceValidate()->serve($psr7Request);

    /**
     * $replyResponse 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
     * 下面是一个原生 swoole 的响应方法
     */
    $response->status($replyResponse->getStatusCode());

    /**
     * PSR-7 的 Header 并不是单纯的 k => v 结构
     */
    foreach ($replyResponse->getHeaders() as $name => $values) {
        $response->header($name, implode(", ", $values));
    }

    // 将响应输出到客户端
    $response->write($replyResponse->getBody()->__toString());
});

$http->start();
```

> 上述 `$psr7Request` 请用户参考 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准自行实现 `Psr\Http\Message\ServerRequestInterface` 接口。


::: tip
  注意：安全模式下请一定要配置 `aesKey`。
:::

很简单，一个服务端带验证功能的代码已经完成，当然我们没有对消息做处理，别着急，后面我们再讲。

我们先来分析上面的代码：

```php
<?php

// 引入我们的主项目工厂类
use EasySwoole\WeChat\Factory;

// 一些配置
$config = [...];

// 使用配置来初始化一个公众号应用实例
$officialAccount = Factory::officialAccount($config);

// 得到一个 Server\Guard $server 实例
$server = $officialAccount->server;

// 构造 实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
// 此处为实现了 \Psr\Http\Message\ServerRequestInterface 的 request 对象
/** @var \Psr\Http\Message\ServerRequestInterface $psr7Request  */
$psr7Request = new XxxReuest($request); // 伪代码

// 得到一个实现了 `Psr\Http\Message\ResponseInterface` 接口的 response 响应实例对象
$replyReponse = $server->forceValidate()->serve($psr7Request);

### 构建 Swoole 响应给到客户端
// 设置响应 HTTP 状态码
$response->status($replyResponse->getStatusCode());
// 设置响应头 Header
foreach ($replyResponse->getHeaders() as $name => $values) {
    $response->header($name, implode(", ", $values));
}
// 将响应输出到客户端
$response->write($replyResponse->getBody()->__toString());
```

最后这一行我有必要详细讲一下：

- 我们的 `$server->forceValidate()->serve($psr7Request);` 就是执行服务端业务了，那么它的返回值是一个实现了 `Psr\Http\Message\ResponseInterface` 接口的实例对象。
- 我这里是直接调用了 `Swoole` 原生的响应方法 `write()`。在一些的 `Swoole` 相关的框架中，你可以直接拿到 `$replyResponse` 实例对象进行相关的操作，然后输出到客户端即可。在 `EasySwoole` 中，可以直接使用上文示例的方法操作即可输出到客户端。

OK，有了上面的代码，那么请你按 **微信官方的接入指引** 在公众号后台完成配置并启用，并相应修改上面的 `$config` 的相关配置。

::: warning
  `URL` 就是我们的 `http://easyswoolewechat.com/server`，这里我是举例哦，你可不要填写我的域名。由于我使用的是 `Swoole` 的 `9501` 端口提供服务，请用户自行进行反向代理配置，具体如何配置反向代理请看 [EasySwoole 反向代理](/QuickStart/proxy.md)。
:::

::: warning
  请一定要将微信后台的开发者模式 **”启用”** ！！！！！！看到红色 **“停用”** 才真正的是启用了。最后，请不要用浏览器访问这个地址，它是给微信服务器访问的，不是给人访问的。
:::

## 接收 & 回复用户消息

上述完成服务端验证通过后，接下来我们就来试一下接收消息吧。

在刚刚上文代码最后一行使用 `$this->response()->write($replyResponse->getBody()->__toString());` (在 `EasySwoole` 框架中响应) 或 使用 `$response->write($replyResponse->getBody()->__toString());` (原生 `Swoole` 响应); 在前面，现在我们调用 `$officialAccount->server` 的 `push()` 方法来注册一个消息处理器，这里用到了 `PHP 闭包` 的知识，如果你不熟悉赶紧补课去。

> `EasySwoole` 中 `App\HttpController\Index.php` 实现：

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WeChat\Factory;

class Index extends Controller
{

    public function server()
    {
        // 这里省略 

        $server = $officialAccount->server;
        
        /** 注册消息事件回调 */
        $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
            return new \EasySwoole\WeChat\Kernel\Messages\Text("您好！欢迎使用 EasySwoole WeChat!");
        });
        
        $psr7Request = $this->request();
        /** @var \Psr\Http\Message\ResponseInterface $replyResponse */
        $replyResponse = $server->forceValidate()->serve($psr7Request);
        
        $this->response()->withStatus($replyResponse->getStatusCode());
        foreach ($replyResponse->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }

        // 将响应输出到客户端
        $this->response()->write($replyResponse->getBody()->__toString());
    }
}
```

> 原生 `Swoole` 中单独实现 `server.php`：

```php
<?php

use EasySwoole\WeChat\Factory;

require_once __DIR__ . '/vendor/autoload.php';

// 这里省略

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    
    // 这里省略
    
    $server = $officialAccount->server;

    /** 注册消息事件回调 */
    $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
        return new \EasySwoole\WeChat\Kernel\Messages\Text("您好！欢迎使用 EasySwoole WeChat!");
    });

    /** @var \Psr\Http\Message\ServerRequestInterface $psr7Request  */
    $psr7Request = new XxxReuest($request); // 伪代码

    $replyResponse = $server->forceValidate()->serve($psr7Request);
    $response->status($replyResponse->getStatusCode());
    foreach ($replyResponse->getHeaders() as $name => $values) {
        $response->header($name, implode(", ", $values));
    }

    // 将响应输出
    $response->write($replyResponse->getBody()->__toString());
});

// 这里省略
```

OK，打开你的微信客户端，向你的公众号发送任意一条消息，你应该会收到回复：`您好！欢迎使用 EasySwoole WeChat!`。

如果您没有收到回复，但是看到了 **“你的公众号暂时无法提供服务”**，好，那检查一下你的日志吧，日志在哪儿？我们的配置里写了日志路径了(`sys_get_temp_dir() . '/wechat.log'`)。没有这个文件？看看权限。

一个基本的服务端验证就完成了。

## 总结

所有的应用服务都通过主入口 `EasySwoole\WeChat\Factory` 类来创建：

```php
<?php

use EasySwoole\WeChat\Factory;

// 公众号
$officialAccount = Factory::officialAccount($config);

// 小程序
$miniProgram = Factory::miniProgram($config);

// 开放平台
$openPlatform = Factory::openPlatform($config);

// 企业微信
$work = Factory::work($config);
```

## 最后

希望您在使用本 `SDK` 的时候如果您发现 `SDK` 的不足，欢迎提交 [`PR`](https://github.com/easy-swoole/wechat/pulls) 或者给我们 [提建议 & 报告问题](https://github.com/easy-swoole/wechat/issues)。