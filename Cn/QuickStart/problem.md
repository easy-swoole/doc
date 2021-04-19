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

出现类似 `WARNING	swSocket_bind(:483): bind(0.0.0.0:9501) failed, Error: Address already in use[98]` 如下错误，可判定为端口被占用。
 
可以通过 `lsof` 命令来查询端口占用

```shell script
> lsof -i:9501
COMMAND PID USER   FD   TYPE  DEVICE SIZE/OFF NODE NAME
php      57 root    3u  IPv4 1744902      0t0  TCP *:9501 (LISTEN)
```

可以根据返回的 `pid` 使用 `kill` 命令来关闭占用端口的相关进程

```
> kill -9 57
```

也可以通过修改 `easyswoole` 的监听端口的方式启动 `easyswoole` 的服务，修改文件在dev.php（线上环境则应在produce.php）中
```php
<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501, // 此处修改 easyswoole 监听端口号
        ...
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null
];
```

然后重新启动服务，即可成功启动服务。

### Socket 监听失败

- 判断是否为端口占用所导致的监听失败，这里还是采用 `lsof` 命令来查看端口

```shell script
> lsof -i:9501
```

- `1024` 以下端口需要 `root` 权限监听，所以这里要特别注意

### 外网无法访问

::: tip
  注意：当服务成功启动后，如果外网无法访问，可以使用 `telnet` 客户端查看对应的端口是否开放成功，前提是首先环境得有 `telnet` 客户端 (具体如何安装 `telnet` 客户端请用户自行百度谷歌查询)，检查端口开放命令如下：`telnet 公网ip/内网ip 端口号`，例如：`telnet 192.168.0.1 9501`。端口开放成功，则会立刻跳转，不成功，则会有对应的提示。用户可根据对应的提示进行判断。
:::

- 检查服务监听端口是否为 `0.0.0.0`

- 检查防火墙是否对外开放

```shell script
> netstat -anp

// 如果相关端口被防火墙拦截，需要放开
> firewall-cmd --zone=public --add-port=9501/tcp --permanent
```

如果为阿里云、腾讯云等云服务器，请检查服务器安全组是否放行对应端口。同样可以使用上述注意事项的 `telnet` 客户端自行检查。

::: tip
  以上 `shell` 命令适用于 `centos 7`，其它 `linux` 发行版请自行查找相关命令。
:::

### 请求数据时 DNS 报错
- 原因：有时会发现在使用 `Swoole` 的 `MySQL、HttpClient、Redis` 等客户端发送请求时，出现了 `DNS` 错误，类似于 `DNS Lookup resolve failed...` 错误，这是由于 `Swoole` 底层对一些 `DNS` 不是很友好。
- 解决方法：建议使用阿里云公共 `DNS`，具体如何配置阿里云公共 `DNS`，请看 [https://www.alidns.com/knowledge?type=SETTING_DOCS#user_linux](https://www.alidns.com/knowledge?type=SETTING_DOCS#user_linux)
