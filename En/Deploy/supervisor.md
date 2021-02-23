---
title: deploying easyswoole with supervisord  
meta:
  - name: description
    content: deploying easyswoole with supervisord 
  - name: keywords
    content: deploying easyswoole with supervisord 
---


# Supervisor deployment

[Supervisor]( http://supervisord.org ）It is a `client / server` service developed with `Python`. It is a process management tool under `Linux / Unix` system and does not support `windows` system. It is convenient to monitor, start, stop and restart one or more processes. For processes managed by `supervisor`, when a process is accidentally `killed`, it will be automatically pulled up again. Developers do not need to write their own `shell` for process management to maintain their own services.

## Install Supervisor

Use the installation method under the 'Ubuntu' system to demonstrate:

> apt-get -y install supervisor

## Create profile

> vim /etc/supervisor/conf.d/easyswoole.conf

The contents of the document are as follows：
```
# Set the app name to easywoole
[program:easyswoole]
# Set running directory
directory=/data/wwwroot/EasySwoole3.4.x
# Start command for project
command=php easyswoole server start
# Set the user to run the process
user=www-data
# Do you want to start the application automatically when supervisor starts
autostart=true
# Does the process exit and restart automatically
autorestart=true
# How many seconds after the process starts is considered to be successful. The default is 1s
startsecs=1
# The maximum number of failed attempts is 3 by default
startretries=3
# stderr
stderr_logfile=/data/wwwlog/easyswoole-stderr.log
# stdout
stdout_logfile=/data/wwwlog/easyswoole-stdout.log
```

## 启动Supervisor

> service supervisor start

## Supervisorctl

*Start EasySwoole*
> supervisorctl start easyswoole

*Stop EasySwoole*
> supervisorctl stop easyswoole

*Restart EasySwoole*
> supervisorctl restart easyswoole

*View all monitored apps*
> supervisorctl status

*Reload configuration file*
> supervisorctl update

*Restart all apps*
> supervisorctl reload
