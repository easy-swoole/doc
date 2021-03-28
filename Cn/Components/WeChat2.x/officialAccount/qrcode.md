---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 二维码

目前有 `2` 种类型的二维码：

- 临时二维码，是有过期时间的，最长可以设置为在二维码生成后的 `30` 天后过期，但能够生成较多数量。临时二维码主要用于帐号绑定等不要求二维码永久保存的业务场景
- 永久二维码，是无过期时间的，但数量较少（目前为最多 `10` 万个）。永久二维码主要用于适用于帐号绑定、用户来源统计等场景。

## 创建临时二维码

```php
<?php

$result = $officialAccount->qrcode->temporary('foo', 6 * 24 * 3600);

// 运行结果：
/*
Array
(
    [ticket] => gQFD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTmFjVTRWU3ViUE8xR1N4ajFwMWsAAgS2uItZAwQA6QcA
    [expire_seconds] => 518400
    [url] => http://weixin.qq.com/q/02NacU4VSubPO1GSxj1p1k
)
*/
```

## 创建永久二维码

```php
$result = $officialAccount->qrcode->forever(56);

// 或者
$officialAccount->qrcode->forever("foo");

// 运行结果：
/*
Array
(
    [ticket] => gQFD8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTmFjVTRWU3ViUE8xR1N4ajFwMWsAAgS2uItZAwQA6QcA
    [url] => http://weixin.qq.com/q/02NacU4VSubPO1GSxj1p1k
)
*/
```

## 获取二维码网址

```php
$url = $officialAccount->qrcode->url($ticket);
// https://api.weixin.qq.com/cgi-bin/showqrcode?ticket=TICKET
```

## 获取二维码内容

```php
<?php

$url = $officialAccount->qrcode->url($ticket);

$content = file_get_contents($url); // 得到二进制图片内容

// 在 EasySwoole 框架中
file_put_contents(EASYSWOOLE_ROOT . '/code.jpg', $content); // 写入文件，这里的路径请使用绝对路径

// 或者 在原生 Swoole 中
// file_put_contents(__DIR__ . '/code.jpg', $content); // 写入文件，这里的路径请使用绝对路径
```