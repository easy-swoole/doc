---
title: easyswoole基础使用-command
meta:
  - name: description
    content: easyswoole基础使用-command
  - name: keywords
    content: easyswoole基础使用-command
---
# 基本管理命令

## 框架安装

> php easyswoole install

## 服务管理

::: tip
  注意：以下命令只针对 `EasySwoole 3.4.x` 及以后版本，`EasySwoole 3.4.x` 之前版本管理命令请查看 [旧版本管理命令](https://github.com/easy-swoole/doc-3.3.x/blob/master/Cn/BaseUsage/baseCommand.md) 
:::

`EasySwoole` 框架主命令。

可执行 `php easyswoole server -h` 来查看具体操作。

**服务启动**

> php easyswoole server start

**守护进程方式启动**

> php easyswoole server start -d

**指定配置文件启动服务**

默认为 `dev`，即 `-mode` 参数默认为 `dev`，即默认以项目根目录的 `dev.php` 作为框架运行的配置文件。

指定以项目根目录的 `produce.php` 作为框架运行的配置文件，请运行如下命令：

`-d` 可选参数：守护进程

> php easyswoole server start -mode=produce

**停止服务**

> php easyswoole server stop

**强制停止服务**

> php easyswoole server stop -force

**热重启**

仅会重启 `worker` 进程

> php easyswoole server reload

**重启服务**

`-d` 可选参数：守护进程

> php easyswoole server restart

**服务状态**

> php easyswoole server status

## 进程管理

`EasySwoole` 内置对于 `Process` 的命令行操作，方便开发者友好地去管理 `Process`。

可执行 `php easyswoole process -h` 来查看具体操作。

**显示所有进程**

> php easyswoole process show

**如果想要以 `MB` 形式显示：**

> php easyswoole process show -d

**杀死指定进程(PID)**

> php easyswoole process kill --pid=PID

**杀死指定进程组(GROUP)**

> php easyswoole process kill --group=GROUP_NAME

**杀死所有进程**

> php easyswoole process killAll

**强制杀死进程**

需要带上 `-f` 参数，例如：

> php easyswoole process kill --pid=PID -f


## Crontab 管理

`EasySwoole` 内置对于 `Crontab` 的命令行操作，方便开发者友好地去管理 `Crontab`。

可执行 `php easyswoole crontab -h` 来查看具体操作。

**查看所有注册的 Crontab**

> php easyswoole crontab show

**停止指定的 Crontab**

> php easyswoole crontab stop --name=TASK_NAME

**恢复指定的 Crontab**

> php easyswoole crontab resume --name=TASK_NAME

**立即跑一次指定的 Crontab**

> php easyswoole crontab run --name=TASK_NAME

## Task 管理

**查看 `Task` 进程状态**

> php easyswoole task status


## 单元测试

::: tip
 注意：需要先使用命令 `composer require easyswoole/phpunit` 安装单元测试组件包，然后才可以执行如下命令。详细使用请看 [单元测试](/Components/phpunit.md) 章节。
:::

**协程方式执行单元测试**

单元测试用例存放在项目根目录的 `tests` 目录。

> php easyswoole phpunit tests

**非协程方式执行单元测试**

单元测试用例存放在项目根目录的 `tests` 目录。

> php easyswoole phpunit tests --no-coroutine


## 生成 API 文档

::: tip
 注意：此命令在 `EasySwoole 3.4.4` 及以上版本中才可用。
:::

`EasySwoole` 内置了针对 `注解控制器` 中的注解方法生成对应 `API` 接口文档的命令，方便开发者可以更加高效地提供 `API` 接口文档用于对接。关于 `注解` 如何使用，详细请看 [注解](/HttpServer/Annotation/install.md) 章节。

可执行 `php easyswoole doc -h` 来查看具体操作。

**指定需要生成 API 文档的控制器目录**

> php easyswoole doc --dir=App/HttpController/

**指定生成 API 文档的额外说明**

> php easyswoole doc --extra=API_README.md

**指定需要生成 API 文档的控制器目录和文档额外说明**

> php easyswoole doc --extra=API_README.md --dir=App/HttpController/

以上命令执行完成之后，开发者即可在项目根目录看到 `easyDoc.html` API 接口文档。
