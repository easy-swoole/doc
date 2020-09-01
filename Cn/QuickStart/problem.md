---
title: easyswoole 常见问题
meta:
  - name: description
    content: easyswoole 常见问题
  - name: keywords
    content: easyswoole 常见问题
---
## 常见问题
### 端口占用
可以通过lsof命令来查询端口占用
```shell script
> lsof -i:9501
COMMAND PID USER   FD   TYPE  DEVICE SIZE/OFF NODE NAME
php      57 root    3u  IPv4 1744902      0t0  TCP *:9501 (LISTEN)
```
可以根据返回的pid使用kill命令来关闭相关进程
```
> kill -9 57
```
也可以通过修改easyswoole的监听端口的方式启动easyswoole的服务，修改文件在dev.php（线上环境则应在produce.php）中
```php
<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501, //此处修改easyswoole监听端口号
        ...
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null
];
```

### Socket监听失败
- 判断是否为端口占用所导致的监听失败，这里还是采用lsof命令来查看端口
```shell script
> lsof -i:9501
```

- 1024以下端口需要root权限监听，所以这里要特别注意

### 外网无法访问
- 检查服务监听端口是否为0.0.0.0

- 检查防火墙是否对外开放
```shell script
> netstat -anp

//如果相关端口被防火墙拦截，需要放开

> firewall-cmd --zone=public --add-port=9501/tcp --permanent
```
::: tip
以上shell命令适用于centos7，其它linux发行版请自行查找相关命令
:::
