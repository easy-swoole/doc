---
title: 安装swoole
meta:
  - name: description
    content: swoole 4.x系列的安装文档
  - name: keywords
    content: swoole|swoole 拓展|swoole 安装框架|easyswoole|swoole 扩展|swoole框架|安装 swoole
---

## 安装swoole
### 下载
首先进入swoole的github下载地址:https://github.com/swoole/swoole-src/releases  
如果没有特殊需求,请选择最新版本开始下载(我这里是最新版是v4.4.16):   
```
tioncico@tioncico-PC:/tmp$ wget https://github.com/swoole/swoole-src/archive/v4.4.16.tar.gz ## 下载
tioncico@tioncico-PC:/tmp$ tar -zvxf v4.4.16.tar.gz  ## 解压到当前目录
tioncico@tioncico-PC:/tmp$ cd swoole-src-4.4.16/ ## cd目录
tioncico@tioncico-PC:/tmp/swoole-src-4.4.16$ phpize ## 使用phpize创建php编译检测脚本 ./configure
tioncico@tioncico-PC:/tmp/swoole-src-4.4.16$ ./configure --with-php-config=/usr/local/php-7.2.2/bin/php-config --enable-openssl  ## 创建编译文件,第一个--with,后面是php的安装路径/bin/php-config ,第二个--enable,是开启swoole的ssl功能
tioncico@tioncico-PC:/tmp/swoole-src-4.4.16$sudo make && make install  ## 编译swoole并把编译好的文件移动到php的扩展目录(前面的配置php版本的扩展目录) 需要root权限
```

这个时候已经安装成功,需要进入php.ini,在最后面增加上:
```
extension=swoole.so
```

成功安装swoole,通过`php --ri swoole` 查看swoole扩展的信息:

```
tioncico@tioncico-PC:/tmp/swoole-src-4.4.16$ php --ri swoole

swoole

Swoole => enabled
Author => Swoole Team <team@swoole.com>
Version => 4.4.16
Built => Feb 20 2020 11:18:54
coroutine => enabled
epoll => enabled
eventfd => enabled
signalfd => enabled
cpu_affinity => enabled
spinlock => enabled
rwlock => enabled
openssl => OpenSSL 1.1.0h  27 Mar 2018
pcre => enabled
zlib => 1.2.11
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

到此,swoole安装完毕

## 常见问题
### phpize 命令不存在
安装phpize 
```
yum install php-devel ## centos
sudo apt-get install php-dev  ## ubuntu
```

### 提示swoole.so.so类似的报错
说明你的phpize版本和php-config设定的版本不一致,请重新编译

::: warning
phpize命令也可以使用绝对路径:php路径/bin/phpize 用于执行  
在之后的--with-php-config也得使用同样的路径:php路径/bin/php-config
:::

### 安装成功 php --ri没有swoole
说明你的php命令行版本,和安装swoole的php版本不一致,可以通过:php路径/bin/php --ri swoole 进行确认是否安装成功