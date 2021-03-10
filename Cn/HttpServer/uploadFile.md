---
title: easyswoole Http服务-文件上传
meta:
  - name: description
    content: easyswoole Http服务-文件上传
  - name: keywords
    content:  easyswoole Http服务-文件上传
---

# UploadFile对象

基于`Psr`规范封装的`UploadFile`。

::: tip
  注意，当上传大于 `2M` 的文件时请调整配置文件 `MAIN_SERVER.SETTING.package_max_length` 参数，详细请看 [配置文件](/QuickStart/config.md)。
:::

在控制器内获取上传的文件：

```php
<?php
$request=  $this->request();
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file = $request->getUploadedFile('file');//获取一个上传文件,返回的是一个\EasySwoole\Http\Message\UploadFile的对象
$files = $request->getUploadedFiles();
```

## 获取临时文件名

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getTempName();
```

## 获取Stream

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getStream();
```

## 移动到指定位置

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->moveTo('/xxx/xxx/xxx.png'); // 失败这里会抛出异常 \EasySwoole\Http\Exception\FileException
```

## 获取文件大小

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getSize();
```

## 获取错误码

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getError();
```

## 获取客户端文件名

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getClientFilename();
```

## 获取文件类型

```php
/** @var \EasySwoole\Http\Message\UploadFile $file */
$file->getClientMediaType();
```
