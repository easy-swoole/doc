---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 临时素材

它的使用是不基于应用的，或者说基于任何一个应用都能访问这些 `API`，所以在用法上是直接调用 `work` 实例的 `media` 属性：

**上传的媒体文件限制：**

所有文件 `size` 必须大于 `5` 个字节

:::tip
- 图片（image）：2MB，支持 JPG、PNG 格式
- 语音（voice）：2MB，播放长度不超过 60s，支持 AMR 格式
- 视频（video）：10MB，支持 MP4 格式
- 普通文件（file）：20MB
:::

## 上传图片

:::warning
 注意：微信图片上传服务有敏感检测系统，图片内容如果含有敏感内容，如色情、商品推广、虚假信息等，上传可能失败。
:::

```php
// $path 为本地文件路径
$work->media->uploadImage($path);
```

## 上传声音

```php
$work->media->uploadVoice($path);
```

## 上传视频

```php
$work->media->uploadVideo($path);
```

## 上传普通文件

```php
<?php
$path = '/path/to/企业微信操作手册.pdf';

$form = [ 
    // 可选 发送时，中文文件名不显示或被过滤，可传此参数
    'filename' => '企业微信操作手册.pdf'
];

$work->media->uploadFile(string $path, array $form = []);
```

## 获取素材

```php
$work->media->get($mediaId);
```