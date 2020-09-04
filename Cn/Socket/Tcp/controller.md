---
title: easyswoole tcp控制器
meta:
  - name: description
    content: easySwoole tcp控制器
  - name: keywords
    content: easySwoole tcp服务|swoole tcp服务|php tcp服务|swoole 硬件|swoole iot
---

# tcp控制器实现

在TCP中，我们如何实现像Http请求一样的路由，从而将请求分发到不同的控制器。

EasySwoole提供了一个解析的方案参考。（非强制，可自行扩展修改为符合业务所需）

## 安装

引入 socket 包:
```
composer require easyswoole/socket
```

::: danger
警告：请保证你安装的 easyswoole/socket 版本大 >= 1.0.7 否则会导致ws消息发送客户端无法解析的问题
:::

## 协议规则

在本实例中,传输json数据 使用pack N进行二进制处理从而实现处理粘包问题,json数据有3个字段,分别为
- controller ```所要调用控制器名称```
- action ```所要调用控制器行为```
- param ```参数```

如:

````json
{"controller":"Index","action":"index","param":{"name":"\u4ed9\u58eb\u53ef"}}
````

## 解析器的定义

## 实例控制器的定义

## 数据包的分发




::: warning 
实际生产中，一般是用户TCP连接上来后，做验证，然后以userName=>fd的格式，存在redis中，需要http，或者是其他地方，
:::

比如定时器往某个连接推送的时候，就是以userName去redis中取得对应的fd，再send。注意，通过addServer形式创建的子服务器，

::: warning 
以再完全注册自己的网络事件，你可以注册onclose事件，然后在连接断开的时候，删除userName=>fd对应。
:::
