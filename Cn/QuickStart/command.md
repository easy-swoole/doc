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

`EasySwoole` 框架主命令。  

可执行 `php easyswoole server -h` 来查看具体操作。

**服务启动**

> php easyswoole server start

**守护进程方式启动**

> php easyswoole server start -d

**指定配置文件启动服务**

默认为 `dev`

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


## Crontab管理

`EasySwoole` 内置对于 `Crontab` 的命令行操作，方便开发者友好地去管理 `Crontab`。

可执行 `php easyswoole crontab -h` 来查看具体操作。

**查看所有注册的Crontab**

> php easyswoole crontab show

**停止指定的Crontab**

> php easyswoole crontab stop --name=TASK_NAME

**恢复指定的Crontab**

> php easyswoole crontab resume --name=TASK_NAME

**立即跑一次指定的Crontab**

> php easyswoole crontab run --name=TASK_NAME

## Task管理

**查看 `Task` 进程状态**

> php easyswoole task status


## 单元测试

**协程方式**

> php easyswoole phpunit tests

**非协程方式**

> php easyswoole phpunit --no-coroutine
