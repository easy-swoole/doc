---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 消息群发

微信的群发消息接口有各种乱七八糟的注意事项及限制，具体请阅读 [微信官方文档](https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html)。

## 发送消息

以下所有方法均有第二个参数 `$to` 用于指定接收对象：

- 当 `$to` 为整型时为标签 `id`
- 当 `$to` 为数组时为用户的 `openid` 列表（至少两个用户的 `openid`）
- 当 `$to` 为 `null` 时表示全部用户

```php
$officialAccount->broadcasting->sendMessage(\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message, array | int $to = null);
```

下面的别名方法 `sendXXX` 都是基于上面 `sendMessage` 方法的封装。

### 文本消息

```php
<?php
// 发送给全部用户
$officialAccount->broadcasting->sendText("大家好！欢迎使用 EasySwoole WeChat。");

// 指定目标用户
// 至少两个用户的 openid，必须是数组。
$officialAccount->broadcasting->sendText("大家好！欢迎使用 EasySwoole WeChat。", [$openid1, $openid2]);

// 指定标签组用户
$officialAccount->broadcasting->sendText("大家好！欢迎使用 EasySwoole WeChat。", $tagId); // $tagId 必须是整型数字
```

### 图文消息

```php
<?php
$officialAccount->broadcasting->sendNews($mediaId);

$officialAccount->broadcasting->sendNews($mediaId, [$openid1, $openid2]);

$officialAccount->broadcasting->sendNews($mediaId, $tagId);
```

### 图片消息

```php
<?php
$officialAccount->broadcasting->sendImage($mediaId);

$officialAccount->broadcasting->sendImage($mediaId, [$openid1, $openid2]);

$officialAccount->broadcasting->sendImage($mediaId, $tagId);


$mediaIds = [
    'aaa',
    'bbb',
    'ccc'
];
$extraParams = [
    'recomand' => 'xxx',
    'need_open_comment' => 1,
    'only_fans_can_comment' => 0
];
$officialAccount->broadcasting->sendImages($mediaIds, 2, [], $extraParams);
```

### 语音消息

```php
<?php
$officialAccount->broadcasting->sendVoice($mediaId);

$officialAccount->broadcasting->sendVoice($mediaId, [$openid1, $openid2]);

$officialAccount->broadcasting->sendVoice($mediaId, $tagId);
```

### 视频消息

用于群发的视频消息，需要先创建消息对象。

```php
<?php
// 1. 先上传视频素材用于群发：
$video = '/path/to/video.mp4';
$videoMedia = $officialAccount->material->uploadVideo($video, '视频标题', '视频描述');

// 结果如下：
//{
//  "media_id":"IhdaAQXuvJtGzwwc0abfXnzeezfO0NgPK6AQYShD8RQYMTtfzbLdBIQkQziv2XJc",
//  "url": "URL"
//}

// 2. 使用上面得到的 media_id 群发视频消息
$officialAccount->broadcasting->sendVideo($videoMedia['media_id']);


// to tag
$officialAccount->broadcasting->sendVideo($videoMedia['media_id'], $tagId);

// to user
$officialAccount->broadcasting->sendVideo($videoMedia['media_id'], [$openid1, $openid2]);
```

### 卡券消息

```php
<?php
$officialAccount->broadcasting->sendCard($cardId);

// to user
$officialAccount->broadcasting->sendCard($cardId, [$openid1, $openid2]);

// to tag
$officialAccount->broadcasting->sendCard($cardId, $tagId);
```

### 发送预览群发消息给指定的 openId 用户

```php
<?php
$officialAccount->broadcasting->previewText($text, $openId);
$officialAccount->broadcasting->previewNews($mediaId, $openId);
$officialAccount->broadcasting->previewVoice($mediaId, $openId);
$officialAccount->broadcasting->previewImage($mediaId, $openId);
$officialAccount->broadcasting->previewVideo($mediaId, $openId);
$officialAccount->broadcasting->previewCard($cardId, $openId);
```

### 发送预览群发消息给指定的微信号用户

> $wxanme 是用户的微信号，比如：easyswoole

```php
<?php
$officialAccount->broadcasting->previewText($text, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
$officialAccount->broadcasting->previewNews($mediaId, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
$officialAccount->broadcasting->previewVoice($mediaId, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
$officialAccount->broadcasting->previewImage($mediaId, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
$officialAccount->broadcasting->previewVideo($mediaId, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
$officialAccount->broadcasting->previewCard($cardId, $wxname, \EasySwoole\WeChat\OfficialAccount\Broadcasting\Client::PREVIEW_BY_NAME);
```

### 删除群发消息

```php
<?php
$officialAccount->broadcasting->delete($msgId);

$officialAccount->broadcasting->delete($msgId, $index);
```

### 查询群发消息发送状态

```php
$officialAccount->broadcasting->status($msgId);
```