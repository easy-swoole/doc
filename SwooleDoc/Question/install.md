---
title: easyswoole swoole-安装问题
meta:
  - name: description
    content: easyswoole swoole-安装问题
  - name: keywords
    content: easyswoole swoole-安装问题|easyswoole|swoole
---

# 安装问题

## phpinfo中有swoole php -m中没有

先确认 `php --ri swoole` 是否有
     
输出 `swoole` 扩展信息说明安装成功

其实不需要去管 `php -m` 和 `phpinfo` 中是否有 `swoole`  

首先 `swoole` 是在 `cli` 模式下进行的运行   

传统 `fpm` 模式下功能是有限的

`fpm` 模式下 任何异步/协程等功能无法使用

其次一定要理解 `swoole` 的运行模式 不要去思考一些无脑问题

### 原因

编译安装 `swoole` 后，`php-fpm/apache` 的 `phpinfo` 页面可以看到 `swoole`
    
`php -m` 看不到，有可能因为 `cli/fpm/apache` 使用的 `php.ini` 配置不同

### 解决方案

- 确认 `php.ini` 文件所在的位置

在 `cli` 模式下，执行 `php -i | grep php.ini` 或者 `php --ini` 找到 `php.ini` 文件绝对路径

`php-fpm/apache` 是查看 `phpinfo` 输出页面找到的 `php.ini` 的文件绝对路径

- 查看对应 `php.ini` 是否有 `swoole.so`

```bash
cat /path/php.ini | grep swoole.so
```

## pcre.h: No such file or directory

缺少 `pcre` 需要安装 `libpcre`

- centos/redhat

```bash
sudo yum install pcre-devel
```

- ubuntu/debian

```bash
sudo apt-get install libpcre3 libpcre3-dev
```

- 其它

自行去 [prce官网](http://www.pcre.org/) 下载对应的源码包 编译安装

## '__builtin_saddl_overflow' was not declared in this scope

`gcc` 缺少必须的定义

```bash
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```

## fatal error: 'openssl/ssl.h' file not found

需要在编译的时候 通过 `--with-openssl-dir` 指定 `openssl` 库的绝对路径


## make 编译错误

`php` 版本和编译时所使用的 `phpize` 和 `php-config` 不对应 指定一下就好了

```bash
# 先 phpize 生成 configure 需要对应的 php-config
# 假设我的 php-config 在 /usr/local/Cellar/php/7.3.12/bin/php-config
./configure --with-php-config=/usr/local/Cellar/php/7.3.12/bin/php-config
```