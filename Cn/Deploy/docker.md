---
title: easyswoole框架部署-docker
meta:
  - name: description
    content: easyswoole框架部署-docker
  - name: keywords
    content: easyswoole框架部署-docker
---

# Docker部署

`Docker` 是一个开源的应用容器引擎，让开发者可以打包他们的应用以及依赖包到一个可移植的镜像中，然后发布到任何流行的 `Linux` 或 `Windows` 机器上，也可以实现虚拟化。容器是完全使用沙箱机制，相互之间不会有任何接口。

:::tip
使用 `Docker` 部署前，需要用户自行安装[Docker](https://www.docker.com/get-started)。
:::

## 镜像拉取

*请在终端执行以下命令*
> docker pull easyswoole/easyswoole3

docker hub上的环境为 `php7.2` + `swoole4.4.17` + `easyswoole 3.3.x`


## 框架启动

> docker run -ti -p 9501:9501 easyswoole/easyswoole3

默认工作目录为: `/easyswoole`  
命令执行完成，自动进入工作目录，执行 `php easyswoole start`，宿主机浏览器访问 `http://127.0.0.1:9501/` 即可看到欢迎页。

## 如何开发

可以利用 Docker 的映射功能，将宿主机目录映射到容器中。在被映射的目录中根据框架安装文档重新安装 `easyswoole`。在宿主机开发，容器内进行同步测试。

:::tip
注意，在部分环境下，例如 `Win10` 系统的 `docker` 环境。      
不可把虚拟机共享目录作为 `EasySwoole` 的 `Temp` 目录，将会因为权限不足无法创建`socket`。这将产生报错：`listen xxxxxx.sock fail`， 为此可以手动在`dev.php` 配置文件里把 `Temp` 目录改为其他路径即可,如：`'/Tmp'`
:::

## Dockerfile

`Dockerfile` 是一个用来构建镜像的文本文件，文本内容包含了一条条构建镜像所需的指令和说明。

```
FROM centos:8

#version defined
ENV SWOOLE_VERSION 4.4.17
ENV EASYSWOOLE_VERSION 3.x

#install libs
RUN yum install -y curl zip unzip  wget openssl-devel gcc-c++ make autoconf git
#install php
RUN yum install -y php-devel php-openssl php-mbstring php-json php-simplexml
# composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer
# use aliyun composer
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# swoole ext
RUN wget https://github.com/swoole/swoole-src/archive/v${SWOOLE_VERSION}.tar.gz -O swoole.tar.gz \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && rm swoole.tar.gz \
    && ( \
    cd swoole \
    && phpize \
    && ./configure --enable-openssl \
    && make \
    && make install \
    ) \
    && sed -i "2i extension=swoole.so" /etc/php.ini \
    && rm -r swoole

# Dir
WORKDIR /easyswoole
# install easyswoole
RUN cd /easyswoole \
    && composer require easyswoole/easyswoole=${EASYSWOOLE_VERSION} \
    && php vendor/easyswoole/easyswoole/bin/easyswoole install

EXPOSE 9501
```