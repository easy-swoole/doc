---
title: easyswoole php源码加密原理
meta:
  - name: description
    content: easyswoole php源码加密原理
  - name: keywords
    content: php源码加密|swoole源码加密|easyswoole源码加密
---
# 使用
## 环境要求
- 保证 **PHP** 版本大于等于 **7.1**
- 使用 **Linux** / **FreeBSD** / **MacOS** 这三类操作系统
- 使用 **Composer** 作为依赖管理工具

## 安装拓展
- 克隆仓库 https://github.com/easy-swoole/compiler
- phpize
- ./configure
- make install
- php.ini加入```extension=easy_compiler.so```

> 注意swoole4.x的library hook也用到了此技术，请在swoole.so后引入easy_compiler.so。另外，swoole加密器也可能用到了该方式，因此可能会有冲突

## 修改默认加密密钥
在```/src/config.h```文件中可以修改自己的密钥。

## composer助手脚本
```
composer require easyswoole/compiler=dev-master
```
对任意文件加密
```
 php vendor/easyswoole/compiler/bin/easy-compiler App/HttpController/Index.php 
```

> 会自动替换文件，并生成App/HttpController/Index.php.bak

## 效果如下
![](/Images/Other/CodeEncrypt/encrypt.png)