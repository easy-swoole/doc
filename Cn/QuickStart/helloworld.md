---
title: easyswoole hello world 入门指南
meta:
  - name: description
    content: easyswoole hello world 入门指南
  - name: keywords
    content: easyswoole hello world 入门指南
---

# Hello World
## 目录检查
我们在执行完框架的安装步骤后，可以在项目根目录下看到一个自动生成的App目录。目录结构如下
```
./App
└── HttpController
    ├── Index.php
    └── Router.php
```
如果缺少该目录，请返回框架安装步骤。

## 自动加载检查
打开```composer.json```文件，检查是否有注册了```App```命名空间。
```
"autoload": {
        "psr-4": {
            "App\\": "App/"
        }
}
```
在```composer.json```文件中，如果```psr-4```一处，缺少 ```App```命名空间的映射，
那么请手动补充。

## 更新自动加载
执行如下命令用以更新命名空间：
```bash
composer dump-autoload
```

## 启动服务
在项目根目录下执行如下命令以守护模式启动easyswoole
```bash
php easyswoole server start -d
```
在没有修改主服务端口的情况下，Easyswoole默认的HTTP服务端口为9501。我们可以CURL本地端口验证服务是否成功启动。
```
curl -I 127.0.0.1:9501

HTTP/1.1 200 OK
Server: EasySwoole
Content-Type: text/html;charset=utf-8
Connection: keep-alive
Date: Sat, 18 Jul 2020 03:32:15 GMT
Content-Length: 13143
```
看到200状态码说明服务已经成功启动。

## 停止服务
在以守护模式启动Easyswoole的时候，我们可以执行以下命令用于停止服务。
```bash
php easyswoole server stop
```
若无守护模式启动。在键盘```ctl+c```的时候，服务自动停止。若为远程终端，没有守护启动es，在终端掉线的时候，会导致服务
停止，甚至是服务成为僵尸进程，需要执行```killall``` 命令清除进程

