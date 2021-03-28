---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 服务端

我们在入门小教程一节中以服务端为例讲解了一个基本的消息的处理，这里就不再讲服务器验证的流程了，请直接参考前面的入门实例即可。

服务端的作用，在整个微信开发中主要是负责 **接收用户发送过来的消息**，还有 **用户触发的一系列事件**。

首先我们得理清 **消息与事件的回复逻辑**，当你收到用户消息后（消息由微信服务器推送到你的服务器），在你对消息进行一些处理后，不管是选择回复一个消息还是什么不都回给用户，你也应该给微信服务器一个 **“答复”**，如果是选择回复一条消息，就直接返回一个消息 **xml** 就好，如果选择不做任何回复，你也得回复一个 `空字符串` 或者 `字符串` **SUCCESS**（不然用户就会看到 **该公众号暂时无法提供服务**）。

## 基本使用

在 `SDK` 中使用 `$officialAccount->server->push(callable $callback)` 来设置消息处理器：

```php
<?php

$server = $officialAccount->server;

/** 注册消息事件回调 */
$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    // $message->getType(); // 消息类型：消息类型：event、text ......
    return new \EasySwoole\WeChat\Kernel\Messages\Text("您好！欢迎使用 EasySwoole WeChat!");
});
```

这里我们使用 `push` 传入了一个 闭包（`Closure`），该闭包接收一个参数 `$message` 为消息对象（类型为实现了 `\EasySwoole\WeChat\Kernel\Contracts\MessageInterface` 接口的实例对象），你可以在全局消息处理器中对消息类型进行筛选：

```php
<?php

$server = $officialAccount->server;

$server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
    switch ($message->getType()) {
        case 'event':
            $text = '收到事件消息';
            break;
        case 'text':
            $text = '收到文字消息';
            break;
        case 'image':
            $text = '收到图片消息';
            break;
        case 'voice':
            $text = '收到语音消息';
            break;
        case 'video':
            $text = '收到视频消息';
            break;
        case 'location':
            $text = '收到坐标消息';
            break;
        case 'link':
            $text = '收到链接消息';
            break;
        case 'file':
            $text = '收到文件消息';
            break;
        // ... 其它消息
        default:
            $text = '收到其它消息';
            break;
    }

    // ...
    
    return new \EasySwoole\WeChat\Kernel\Messages\Text($text);
});
```

当然，因为这里 `push` 接收一个 `callable` 的参数，所以你不一定要传入一个 `Closure` 闭包，你可以选择传入一个函数名，一个 `[$class, $method]` 或者 `Foo::bar` 这样的类型。

## 注册多个消息处理器

有时候你可能需要对消息记日志，或者一系列的自定义操作，你可以注册多个 `handler`：

```php
<?php

$server = $officialAccount->server;

$server->push(MessageLogHandler::class);
$server->push(MessageReplyHandler::class);
$server->push(OtherHandler::class);
$server->push(...);
```

::: warning
  注意：
  - 最后一个非空返回值将作为最终应答给用户的消息内容，如果中间某一个 `handler` 返回值 `false`, 则将终止整个调用链，不会调用后续的 `handlers`。
  - 传入的自定义 `Handler` 类需要实现 `\EasySwoole\WeChat\Kernel\Contracts\EventHandlerInterface` 接口。
:::


## 注册指定消息类型的消息处理器

我们想对特定类型的消息应用不同的处理器，可以在第二个参数传入类型筛选：

> 注意，第二个参数必须是 `\EasySwoole\WeChat\Kernel\Messages\Message` 类的常量。

```php
<?php

use EasySwoole\WeChat\Kernel\Messages\Message;

$server = $officialAccount->server;

$server->push(ImageMessageHandler::class, Message::IMAGE); // 图片消息
$server->push(TextMessageHandler::class, Message::TEXT); // 文本消息

// 同时处理多种类型的处理器
// 当消息为 三种中任意一种都可触发
$server->push(MediaMessageHandler::class, [Message::VOICE, Message::VIDEO, Message::SHORT_VIDEO]);
```

## 请求消息的属性

当你接收到用户发来的消息时，可能会提取消息中的相关属性，参考：

请求消息基本属性 (以下所有消息都有的基本属性)：

- `ToUserName` 接收方帐号（该公众号 `ID`）
- `FromUserName` 发送方帐号（`OpenID`, 代表用户的唯一标识）
- `CreateTime` 消息创建时间（时间戳）
- `MsgId` 消息 `ID`（`64` 位整型）

### 文本：

- `MsgType` `text`
- `Content` 文本消息内容

### 图片：

- `MsgType` `image`
- `MediaId` 图片消息媒体 `id`，可以调用多媒体文件下载接口拉取数据。
- `PicUrl` 图片链接

### 语音：

- `MsgType` `voice`
- `MediaId` 语音消息媒体 `id`，可以调用多媒体文件下载接口拉取数据。
- `Format` 语音格式，如 `amr`、`speex` 等
- `Recognition` * 开通语音识别后才有

> 请注意，开通语音识别后，用户每次发送语音给公众号时，微信会在推送的语音消息 `XML` 数据包中，增加一个 `Recongnition` 字段

### 视频：

- `MsgType` `video`
- `MediaId` 视频消息媒体 `id`，可以调用多媒体文件下载接口拉取数据。
- `ThumbMediaId` 视频消息缩略图的媒体 `id`，可以调用多媒体文件下载接口拉取数据。

### 小视频：

- `MsgType` `shortvideo`
- `MediaId` 视频消息媒体 `id`，可以调用多媒体文件下载接口拉取数据。
- `ThumbMediaId` 视频消息缩略图的媒体 `id`，可以调用多媒体文件下载接口拉取数据。

### 事件：

- `MsgType` `event`
- `Event` 事件类型 （如：`subscribe` (订阅)、`unsubscribe` (取消订阅) ...， `CLICK` 等）

扫描带参数二维码事件：

- `EventKey` 事件 `KEY` 值，比如：`qrscene_123123`，`qrscene_` 为前缀，后面为二维码的参数值
- `Ticket` 二维码的 `ticket`，可用来换取二维码图片

上报地理位置事件：

- `Latitude` `23.137466` 地理位置纬度
- `Longitude` `113.352425` 地理位置经度
- `Precision` `119.385040` 地理位置精度

自定义菜单事件：

- `EventKey` 事件 `KEY` 值，与自定义菜单接口中 `KEY` 值对应，如：`CUSTOM_KEY_001`、`www.qq.com`

### 地理位置：

- `MsgType` `location`
- `Location_X` 地理位置纬度
- `Location_Y` 地理位置经度
- `Scale` 地图缩放大小
- `Label` 地理位置信息

### 链接：

- `MsgType` `link`
- `Title` 消息标题
- `Description` 消息描述
- `Url` 消息链接

### 文件：

- `MsgType` file 
- `Title` 文件名 
- `Description` 文件描述，可能为 `null`
- `FileKey` 文件 `KEY` 
- `FileMd5` 文件 `MD5` 值 
- `FileTotalLen` 文件大小，单位字节

## 回复消息

回复的消息可以为 `null`，此时 `SDK` 会返回给微信一个 `"SUCCESS"`，你也可以回复一个普通字符串，比如：`欢迎关注 EasySwoole WeChat.`，此时 `SDK` 会对它进行一个封装，产生一个 `\EasySwoole\WeChat\Kernel\Messages\Text` 类型的消息并在最后的 `$officialAccount->server->serve();` 时生成对应的消息 `XML` 格式。

如果你想返回一个自己手动拼的原生 `XML` 格式消息，请返回一个 `\EasySwoole\WeChat\Kernel\Messages\Raw` 实例即可。

## 消息转发给客服系统

参见：[多客服消息转发](/Components/WeChat2.x/officialAccount/messageTransfer.md)

关于消息的使用，请参考 [消息](/Components/WeChat2.x/officialAccount/messages.md) 章节。