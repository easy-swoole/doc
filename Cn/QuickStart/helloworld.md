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

我们在执行完框架的安装步骤后，可以在项目根目录下看到一个自动生成的 `App` 目录。目录结构如下：

```
./App
└── HttpController
    ├── Index.php
    └── Router.php
```

如果缺少该目录，请返回 [框架安装步骤](/QuickStart/install.md)，进行重新安装，然后选择愿意释放 `App` 目录到项目根目录。

## 自动加载检查
打开 ```composer.json``` 文件，检查是否有注册了 ```App``` 命名空间。

注册成功 ```App``` 命名空间时 `composer.json` 文件结构大体如下：

```json
{
    "require": {
        "easyswoole/easyswoole": "3.4.x",
    },
    "autoload": {
        "psr-4": {
            "App\\": "App/"
        }
    }
}
```

在 ```composer.json``` 文件中，如果在 ```psr-4``` 处，缺少 ```App``` 命名空间的映射，那么请自行手动补充。

## 更新自动加载
执行如下命令用于更新命名空间：

```bash
composer dump-autoload
```

## 启动服务
在项目根目录下执行如下命令以守护模式启动 `easyswoole`

```bash
php easyswoole server start -d
```

在没有修改主服务端口的情况下，`EasySwoole` 默认的 `HTTP` 服务端口为 `9501`。我们可以 `CURL` 本地端口验证服务是否成功启动。

```
curl -I 127.0.0.1:9501

HTTP/1.1 200 OK
Server: EasySwoole
Content-Type: text/html;charset=utf-8
Connection: keep-alive
Date: Sat, 18 Jul 2020 03:32:15 GMT
Content-Length: 13143
```

看到 `200` 状态码说明服务已经成功启动。

## 停止服务
在使用守护模式启动 `EasySwoole` 的时候，我们可以执行以下命令用于停止服务。

```bash
php easyswoole server stop
```

若没用使用守护模式启动，则按键盘 ```Ctrl+C``` 键的时候，服务就会自动停止。若为远程终端，并且没有使用守护模式启动 `EasySwoole`，则在终端掉线的时候，会导致服务停止，甚至是服务成为僵尸进程，需要执行 ```killall``` 命令清除进程。

