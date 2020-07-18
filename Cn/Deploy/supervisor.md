---
title: easyswoole框架部署-supervisor
meta:
  - name: description
    content: easyswoole框架部署-supervisor
  - name: keywords
    content: easyswoole框架部署-supervisor
---

# Supervisor部署

[Supervisor](http://supervisord.org)是用`Python`开发的一个`client/server`服务，是`Linux/Unix`系统下的一个进程管理工具，不支持`Windows`系统。很方便的监听、启动、停止、重启一个或多个进程。用`Supervisor`管理的进程，当一个进程意外被`Kill`，会自动将它重新拉起，不需要开发者自己编写`shell`进行进程管理来维护自己的服务。

## 安装Supervisor

采用`Ubuntu`系统下的安装方式进行演示：

> apt-get -y install supervisor

## 创建配置文件

> vim /etc/supervisor/conf.d/easyswoole.conf

文件内容如下：
```
# 设置应用名称为easyswoole
[program:easyswoole]
# 设置运行目录
directory=/data/wwwroot/EasySwoole3.4.x
# 项目的启动命令
command=php easyswoole server start
# 设置用户来运行该进程
user=www-data
# 是否随着supervisor启动时 自动启动该应用
autostart=true
# 进程退出 是否自动重启进程
autorestart=true
# 进程启动多少秒之后被认为是启动成功 默认1s
startsecs=1
# 失败最大尝试次数 默认3
startretries=3
# stderr
stderr_logfile=/data/wwwlog/easyswoole-stderr.log
# stdout
stdout_logfile=/data/wwwlog/easyswoole-stdout.log
```

## 启动Supervisor

> service supervisor start

## Supervisorctl

*启动EasySwoole应用*
> supervisorctl start easyswoole

*停止EasySwoole应用*
> supervisorctl stop easyswoole

*重启EasySwoole应用*
> supervisorctl restart easyswoole

*启动EasySwoole应用*
> supervisorctl start easyswoole

*查看所有监控的应用*
> supervisorctl status

*重新加载配置文件*
> supervisorctl update

*重启所有应用*
> supervisorctl reload
