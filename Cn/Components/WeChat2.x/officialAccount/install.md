---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---


# 微信SDK EasySwoole WeChat 2.x

`EasySwoole WeChat` 是一个基于 `Swoole 4.x` 全协程支持的微信 `SDK 库`，告别同步阻塞，轻松编写高性能的微信公众号/小程序/开放平台业务接口。

该组件库是仿照 `easywechat API` 实现的协程安全的 `wechat sdk` 库。

用户可以在一切支持 `Swoole` 协程环境的框架（`EasySwoole`、`Hyperf`、`Swoft` 等）中使用，安装和使用方法请看下文。

> 注意：`WeChat` 组件目前最新版本为 `2.0.0-alpha` 测试版，`2.0.0` 正式版本暂时未发布。线上环境需要使用请先使用旧版 `1.2.x`，详细请看下文注意事项。

::: tip
  旧版 `1.2.x` 的文档请移步查看 **`1.2.x` 微信公众号 `SDK` 文档** - [安装](/Components/WeChat/install.md) 和 [API 文档](/Components/WeChat/officialAccount.md)
:::


## 说明

首先感谢 `easywechat` 作者们创造的优秀项目，但由于在协程环境下使用会有潜在的跨协程问题，故有了此项目。考虑到大多数用户的习惯，我们保留了绝大多数 `API` 的命名和风格习惯，以便用户更好地上手使用；但在部分可能存在潜在风险的地方， 我们则进行了重新设计，以保障协程环境下的运行安全。

## 组件要求

- php >= 7.2
- ext-swoole: ^4.4.19 (推荐使用 Swoole 4.4.23)
- ext-openssl: *
- ext-json: *
- ext-libxml: *
- ext-simplexml: *
- ext-openssl: *
- psr/log: ^1.1
- psr/http-message: ^1.0
- psr/simple-cache: ^1.0
- pimple/pimple: ^3.0
- easyswoole/utility: ^1.1

> 注意：在编译安装 `Swoole` 扩展时，请务必把编译参数 `--enable-openssl` 加上，以启用 `SSL` 支持

## 安装方法

> $ composer require easyswoole/wechat "v2.0.0-alpha"

## 仓库地址

[easyswoole/wechat 2.x](https://github.com/easy-swoole/wechat)

## 常见问题汇总

为了让用户在微信公众平台开发的道路上少掉坑，我们在这里将使用此组件进行开发时遇到的各种问题进行汇总，并给出对应的解决办法。这样用户就可以更效地进行开发了。

### 时区不对

- 报错情形如下：`Setting The Correct Timezone In CentOS And Ubuntu Servers With NTP`。
- 解决方法：使用命令 `date` 可以在服务器上查看当前时间，如果发现时区不对则需要修改时区。


### redirect_url 参数错误

- 出现原因：这是由于程序使用了 **网页授权** 而公众号没有正确配置 **【网页授权域名】** 所致。此时你需要登录微信公众平台，在 **【开发】 -> 【接口权限】** 页面找到 **网页授权获取用户基本信息** 进行配置并保存。

- 解决方法：
  - 网页授权域名应该为通过 `ICP` 备案的有效域名，否则保存时无法通过安全监测。
  - 网页授权域名即程序完成授权获得授权 `code` 后跳转到的页面的域名，一般情况下为你的业务域名。
  - 网页授权域名配置成功后会立即生效。
  - 公众号的网页授权域名只可配置一个，请合理规划你的业务，否则你会发现 …… 授权域名不够用哈。

### JSAPI config: invalid url domain

- 出现原因：在使用 `JS-SDK` 进行开发时，每个页面都需要调用 `wx.config()` 方法配置 `JSPAI` 参数。如果没有正确配置 `JSAPI` 安全域名并且开启了调试模式，此时就报此错误。
- 解决方法：遇到这个问题时，开发者需要登录微信公众平台，进入 **【公众号设置】->【功能设置】** 页面，将项目所使用的域名添加至 **【JSAPI 安全域名】** 列表中。
- 注意事项说明：
  - 一个公众号同时最多可绑定三个安全域名，并且这些域名必须为通过 `ICP` 备案的一级或一级以上的有效域名。
  - `JSAPI` 安全域名每个月限修改三次，修改任何一个都算，所以，请谨慎操作。