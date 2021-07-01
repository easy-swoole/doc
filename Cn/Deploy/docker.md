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

## 部署前必看

部分机器(例如 `Docker` 环境)在使用框架时遇到类似 `DNS Lookup resolve failed...` 错误，请更换机器的 `DNS` 为阿里云公共 DNS `223.5.5.5` 和 `223.6.6.6`。具体更换步骤可查看 [更换 DNS](https://www.alidns.com/knowledge?type=SETTING_DOCS#user_linux)


## 镜像拉取

*请在终端执行以下命令*
> docker pull easyswoole/easyswoole3

docker hub上的环境为 `php7.4` + `swoole4.4.26` + `easyswoole 3.4.x`


## 框架启动

> docker run -ti -p 9501:9501 easyswoole/easyswoole3

默认工作目录为: `/easyswoole`  
命令执行完成，自动进入工作目录，执行 `php easyswoole server start` 启动服务，宿主机浏览器访问 `http://127.0.0.1:9501/` 即可看到欢迎页。如果访问欢迎页遇到如下情形：`not controller class match`，请重新执行安装命令 `php easyswoole install`，并且输入 `Y`、`Y`，再次执行 `php easyswoole server start` 启动服务，就可以正常访问欢迎页了，详见 [框架安装](/QuickStart/install.md)。

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
ENV SWOOLE_VERSION 4.4.26
ENV EASYSWOOLE_VERSION 3.4.x

#install libs
RUN yum install -y curl zip unzip  wget openssl-devel gcc-c++ make autoconf git epel-release
RUN dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
#install php
RUN yum --enablerepo=remi install -y php74-php php74-php-devel php74-php-mbstring php74-php-json php74-php-simplexml php74-php-gd

RUN ln -s /opt/remi/php74/root/usr/bin/php /usr/bin/php \
    && ln -s /opt/remi/php74/root/usr/bin/phpize /usr/bin/phpize \
    && ln -s /opt/remi/php74/root/usr/bin/php-config /usr/bin/php-config

# composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer
# use aliyun composer 由于最近阿里云镜像不稳定，废弃使用
# RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

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
    && sed -i "2i extension=swoole.so" /etc/opt/remi/php74/php.ini \
    && rm -r swoole

# Dir
WORKDIR /easyswoole
# install easyswoole
RUN cd /easyswoole \
    && composer require easyswoole/easyswoole=${EASYSWOOLE_VERSION} \
    && php vendor/easyswoole/easyswoole/bin/easyswoole install

EXPOSE 9501
```
