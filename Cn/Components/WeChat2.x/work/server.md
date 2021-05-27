---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 服务端

我们在企业微信应用开启接收消息的功能，将设置页面的 `token` 与 `aeskey` 配置到 `agents` 下对应的应用内：

```php
<?php
$config = [
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    // 企业微信后台的 agentid
    'agentId' => 100022,

    // server config
    'token' => 'xxxxxxxxx',
    'aesKey' => 'xxxxxxxxxxxxxxxxxx',

    //...
];

$work = \EasySwoole\WeChat\Factory::work($config);
```

接着配置服务端与公众号的服务端用法一样：

```php
<?php
/** 注册消息事件回调 */
$work->server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    return new \EasySwoole\WeChat\Kernel\Messages\Text('Hello EasySwoole WeChat!');
});

/** @var \Psr\Http\Message\ServerRequestInterface $psr7Request */
$psr7Request = $this->request();

$response = $work->server->serve($psr7Request);

/**
 * $response 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信
 * 下面是一个使用 EasySwoole 的响应方法
 */
$this->response()->withStatus($response->getStatusCode());
/**
 * PSR-7 的 Header 并不是单纯的 k => v 结构
 */
foreach ($response->getHeaders() as $name => $values) {
    $this->response()->withHeader($name, implode(", ", $values));
}
$this->response()->write($response->getBody()->__toString());
```

`$response` 是一个显式实现了 PSR-7 的对象，用户只需要处理该对象即可正确响应给微信

具体使用可查看 [公众号-快速开始章节](/Components/WeChat2.x/officialAccount/quickStart.md)