---
title: easyswoole swoole编译参数
meta:
  - name: description
    content: easyswoole swoole编译参数
  - name: keywords
    content: easyswoole swoole编译参数|easyswoole|swoole|
---

## 编译参数
调用`./configure` 有着额外的编译配置,用于开启某些功能以及配置swoole安装路径等.

### --with-php-config
当服务器存在多个php版本时,需要使用该配置参数指定php版本的php-config.例如:  
```
./configure --with-php-config=/usr/local/php-7.2.2/bin/php-config 
```

### --enable-openssl
开启`ssl`支持,但是需要操作系统存在`libssl.so`动态链接库:  

```
./configure --enable-openssl
```

### --with-openssl-dir  
开启`ssl`支持 并 指定 `openssl` 库的路径,例如:  
```
./configure --with-openssl-dir=/opt/openssl/
```
    
### --enable-http2
开启`http2`支持

### --enable-debug

打开调试模式,使其可以使用 `gdb` 跟踪.

### --enable-debug-log

打开swoole 内核debug日志.  

### --enable-trace-log

打开追踪日志.内核调试时使用

