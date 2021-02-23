---
title: swoole编译安装教程
meta:
  - name: description
    content: swoole编译安装教程
  - name: keywords
    content: centos swoole编译安装|linux swoole编译安装
---

## 安装 Swoole
### 下载
首先进入 `Swoole` 的 `Github` 下载地址: https://github.com/swoole/swoole-src/releases
  
如果没有特殊需求，请选择最新稳定版本开始下载(我这里是稳定版v4.4.23):   
```
## 下载
tioncico@tioncico-PC:/tmp$ wget https://github.com/swoole/swoole-src/archive/v4.4.23.tar.gz

## 解压到当前目录
tioncico@tioncico-PC:/tmp$ tar -zvxf v4.4.23.tar.gz

## cd 到解压之后的目录
tioncico@tioncico-PC:/tmp$ cd swoole-src-4.4.23/ 

## 使用 phpize 创建 php 编译检测脚本 ./configure
##【注意：需要选择 php 对应版本的 phpize，这里使用的是绝对路径，否则编译安装无法生效】
tioncico@tioncico-PC:/tmp/swoole-src-4.4.23$ /usr/local/php-7.2.2/bin/phpize

## 创建编译文件，第一个 --with，后面是 php-config 的所在路径(这个路径一般和 php 在同一个目录) /usr/local/php-7.2.2/bin/php-config，第二个 --enable，是开启 Swoole 的 ssl 功能，第三个 --enable(可选参数)，是开启 Swoole 支持 http2 相关的功能
tioncico@tioncico-PC:/tmp/swoole-src-4.4.23$ ./configure --with-php-config=/usr/local/php-7.2.2/bin/php-config --enable-openssl --enable-http2

## 编译 Swoole 并把编译好的文件移动到 php 的扩展目录(前面的配置 php 版本的扩展目录) 需要root权限
tioncico@tioncico-PC:/tmp/swoole-src-4.4.23$ sudo make && make install 

## 编译成功会显示如下：
Build complete.
Don't forget to run 'make test'.

Installing shared extensions:     /usr/local/php-7.2.2/lib/php/extensions/no-debug-non-zts-20160303/
Installing header files:          /usr/local/php-7.2.2/include/php/

```

这个时候已经安装成功，需要修改 `php` 配置文件 `php.ini`，在最后面增加如下内容:
```
extension=swoole.so
```
注意：不知道 php.ini 所在目录时，您可以通过运行 `php --ini` 确定。
例如，我这里 `php.ini` 是在 `/usr/local/php-7.2.2/etc` 目录：
```
tioncico@tioncico-PC:/tmp/swoole-src-4.4.23$ php --ini
Configuration File (php.ini) Path: /usr/local/php-7.2.2/etc
Loaded Configuration File:         /usr/local/php-7.2.2/etc/php.ini
Scan for additional .ini files in: (none)
Additional .ini files parsed:      (none)
```

成功安装 `Swoole` 之后，通过 `php --ri swoole` 查看 `Swoole 扩展` 的信息:

```
tioncico@tioncico-PC:/tmp/swoole-src-4.4.23$ php --ri swoole

swoole

Swoole => enabled
Author => Swoole Team <team@swoole.com>
Version => 4.4.23
Built => Jan 23 2021 18:16:30
coroutine => enabled
epoll => enabled
eventfd => enabled
signalfd => enabled
cpu_affinity => enabled
spinlock => enabled
rwlock => enabled
openssl => OpenSSL 1.0.2k-fips  26 Jan 2017
http2 => enabled
pcre => enabled
zlib => 1.2.7
mutex_timedlock => enabled
pthread_barrier => enabled
futex => enabled
async_redis => enabled

Directive => Local Value => Master Value
swoole.enable_coroutine => On => On
swoole.enable_library => On => On
swoole.enable_preemptive_scheduler => Off => Off
swoole.display_errors => On => On
swoole.use_shortname => On => On
swoole.unixsock_buffer_size => 8388608 => 8388608
```

到此，`Swoole` 扩展就安装完毕。

## 常见问题
### phpize 命令不存在
安装phpize 
```
yum install php-devel ## centos
sudo apt-get install php-dev  ## ubuntu
```

### 提示 swoole.so.so 类似的报错
说明你的 phpize 版本和 php-config 设定的版本不一致，请重新编译

::: warning
phpize 命令也可以使用绝对路径: php安装路径/bin/phpize 用于执行  
在之后的 --with-php-config 也得使用同样的路径: php安装路径/bin/php-config
:::

### 安装成功 php --ri 没有 Swoole 扩展信息
说明你的 php 命令行版本，和安装 Swoole 的 php 版本不一致，可以通过: php安装路径/bin/php --ri swoole 进行确认是否安装成功