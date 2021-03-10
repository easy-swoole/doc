---
title: easyswoole 微服务-Rpc
meta:
  - name: description
    content: EasySwoole中用RPC实现分布式微服务架构
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|easyswoole|Rpc服务端|swoole RPC|swoole微服务|swoole分布式|PHP 分布式
---

# EasySwoole RPC

## 基础概念介绍

很多传统的 `Phper` 并不懂 `RPC` 是什么，`RPC` 全称 `Remote Procedure Call`，中文译为 `远程过程调用`，其实你可以把它理解为是一种架构性上的设计，或者是一种解决方案。

例如在某庞大商场系统中，你可以把整个商场拆分为 `N` 个微服务（理解为 `N` 个独立的小模块也行），例如：
    
- 订单系统
- 用户管理系统
- 商品管理系统
- 等等 

那么在这样的架构中，就会存在一个 `API 网关` 的概念，或者是叫 `服务集成者`。我的 `API 网关` 的职责，就是把一个请求，拆分成 `N` 个小请求，分发到各个小服务里面，再整合各个小服务的结果，返回给用户。例如在某次下单请求中，那么大概发送的逻辑如下：
- API 网关接受请求
- API 网关提取用户参数，请求用户管理系统，获取用户余额等信息，等待结果
- API 网关提取商品参数，请求商品管理系统，获取商品剩余库存和价格等信息，等待结果
- API 网关融合用户管理系统、商品管理系统的返回结果，进行下一步调用（假设满足购买条件）
- API 网关调用用户管理信息系统进行扣款，调用商品管理系统进行库存扣减，调用订单系统进行下单（事务逻辑和撤回可以用 `请求 id` 保证，或者自己实现其他逻辑调度）
- API 网关返回综合信息给用户

而在以上发生的行为，就称为 `远程过程调用`。而调用过程实现的通讯协议可以有很多，比如常见的 `HTTP` 协议。而 `EasySwoole RPC` 采用自定义短链接的 `TCP` 协议实现，每个请求包，都是一个 `JSON`，从而方便实现跨平台调用。

## 微服务相关概念说明

> 什么是服务熔断？

简单理解，一般是 `某个服务故障` 或者是 `异常` 引起的，类似现实世界中的 "保险丝"，当某个异常条件被触发，直接熔断整个服务，而不是一直等到此服务超时。

> 什么是服务降级?

简单理解，一般是从整体负荷考虑，就是当某个服务熔断之后，服务器将不再被调用，此时客户端可以自己准备一个本地的 `fallback` 回调，返回一个缺省值，这样做，虽然服务水平下降，但总比服务直接挂掉要强。服务降级处理是在客户端实现完成的，与服务端没有关系。

> 什么是服务限流？

简单理解，例如某个服务器最多同时仅能处理 `100` 个请求，或者是 `CPU 负载达到百分之80` 的时候，为了保护服务的稳定性，则不再希望继续收到 新的连接。那么此时就要求客户端不再对其发起请求。因此 `EasySwoole RPC` 提供了 `NodeManager` (节点管理器)接口，你可以以任何形式来 监控你的服务提供者，在 `getNodes()` 方法中，返回对应的服务器节点信息即可。


## RPC 5.x 全新特性
- 协程调度
- 服务自动发现
- 服务熔断
- 服务降级
- Openssl 加密
- 跨平台、跨语言支持
- 支持接入第三方注册中心

::: tip
  目前最新稳定版本的 `RPC` 组件为 `RPC 5.x`。4.x 版本的 `RPC` 组件的使用，请看 [RPC 4.x](/Microservices/Rpc/rpc_4.x.md)。其他旧版本组件的使用文档请看 [Github](https://github.com/easy-swoole/rpc/tree/3.x)
:::


## 组件要求
- php: >=7.1.0
- ext-swoole: >=4.4.5
- ext-json: >=1.0
- ext-sockets: *
- ext-openssl: >=7.1
- easyswoole/spl: ^1.0
- easyswoole/utility: ^1.0
- easyswoole/component: ^2.0


## 安装方法

> composer require easyswoole/rpc=5.x


## 仓库地址
[easyswoole/rpc=5.x](https://github.com/easy-swoole/rpc)


## 执行流程

![](/Images/Passage/easyswoole-rpc-5.x.jpg)
