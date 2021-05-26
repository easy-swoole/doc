---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 群机器人

## 使用说明

使用前必须先在群组里面添加机器人，然后将 `Webhook 地址` 中的 `key` 取出来，作为示例中 `$groupKey` 的值。

:::tip
- `Webhook 地址` 示例：[https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=`ab4f609a-3feb-427c-ae9d-b319ca712d36`](https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=`ab4f609a-3feb-427c-ae9d-b319ca712d36`)

- 微信文档：[https://work.weixin.qq.com/api/doc#90000/90136/91770](https://work.weixin.qq.com/api/doc#90000/90136/91770)
:::

## 发送文本类型消息

> 快速发送文本消息

```php
<?php
// 获取 Messenger 实例
$messenger = $work->group_robot_messenger;

// 群组 key
$groupKey = 'ab4f609a-3feb-427c-ae9d-b319ca712d36';

$messenger->message('大家好，我是本群的"喝水提醒小助手"')->toGroup($groupKey)->send();
// 或者写成
$messenger->toGroup($groupKey)->send('大家好，我是本群的"喝水提醒小助手"');
```

> 使用 `Text` 发送文本消息

```php
<?php
// 准备消息
$text = new \EasySwoole\WeChat\Work\GroupRobot\Messages\Text('hello');

// 发送
$messenger->message($text)->toGroup($groupKey)->send();
```

> @某人：

```php
<?php
// 通过构造函数传参
$text = new \EasySwoole\WeChat\Work\GroupRobot\Messages\Text('hello', 'her-cat', '18700000000');
//$text = new Text('hello', ['her-cat', 'easyswoole'], ['18700000000', '18700000001']);

// 通过 userId
$text->mention('her-cat');
//$text->mention(['her-cat', 'easyswoole']);

// 通过手机号
$text->mentionByMobile('18700000000');
//$text->mentionByMobile(['18700000000', '18700000001']);

// @所有人
$text->mention('@all');
//$text->mentionByMobile('@all');

$messenger->message($text)->toGroup($groupKey)->send();
```

## 发送 `Markdown` 类型消息

```php
<?php
$content = '
# 标题一
## 标题二
<font color="info">绿色</font>
<font color="comment">灰色</font>
<font color="warning">橙红色</font>
> 引用文字
';

$markdown = new \EasySwoole\WeChat\Work\GroupRobot\Messages\Markdown($content);

$messenger->message($markdown)->toGroup($groupKey)->send();
```

## 发送图片类型消息

```php
<?php
$img = file_get_contents('http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png');

$image = new \EasySwoole\WeChat\Work\GroupRobot\Messages\Image(base64_encode($img), md5($img));

$result = $messenger->message($image)->toGroup($groupKey)->send();
```

## 发送图文类型消息

```php
<?php
$items = [
    new \EasySwoole\WeChat\Work\GroupRobot\Messages\NewsItem([
        'title' => '中秋节礼品领取',
        'description' => '今年中秋节公司有豪礼相送',
        'url' => 'https://www.easyswoole.com',
        'image' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png',
    ]),

    //...
];

$news = new \EasySwoole\WeChat\Work\GroupRobot\Messages\News($items);

$messenger->message($news)->toGroup($groupKey)->send();
```

## 其他方式

使用 `group_robot` 发送消息。

```php
$work->groupRobot->message('大家好，我是本群的"喝水提醒小助手"')->toGroup($groupKey)->send();
```