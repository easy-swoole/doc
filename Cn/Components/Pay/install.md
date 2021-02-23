---
title: easyswoole支付SDK
meta:
  - name: description
    content: easyswoole支付SDK
  - name: keywords
    content: easyswoole支付SDK|swoole支付SDK
---


# EasySwoole Pay

EasySwoole Pay 是一个基于 Swoole 4.x `全协程`支持的支付SDK库，告别同步阻塞。

## 组件要求

-   ext-json >= 1.0
-   ext-openssl >= 1.0
-   ext-bcmath: *
-   easyswoole/http >= ^1.2
-   easyswoole/spl >= ^1.1
-   easyswoole/http-client >= ^1.2.5
-   easyswoole/utility >= ^1.0

> 注意：请务必检查你的 `php` 环境有没有安装 `php-bcmath` 扩展，没有安装 `php-bcmath` 扩展时安装的 `pay` 组件的版本是 `1.2.17` 之前的版本(不是最新)。想要使用最新稳定版 `pay` 组件的功能，请先安装 `php-bcmath` 扩展，`php` 安装此扩展的方法请自行查询。

## 安装方法


> ```
> composer require easyswoole/pay
> ```

## 仓库地址

[easyswoole/pay](https://github.com/easy-swoole/pay)