---
title: deploying easyswoole with docker  
meta:
  - name: description
    content: deploying easyswoole with docker 
  - name: keywords
    content: deploying easyswoole with docker 
---

# Docker deployment

`Docker is an open source application container engine, which allows developers to package their applications and dependency packages into a portable image, and then publish them to any popular 'Linux' or 'windows' machine. It can also realize virtualization. Containers use sandbox mechanism completely, and there is no interface between them.

:::tip
Before using `docker `for deployment, users need to install [docker] by themselves（ https://www.docker.com/get-started ).:::

## Pull image

*Please execute the following command on the terminal*
> docker pull easyswoole/easyswoole3

The environment on the hub is `php7.2` + `swoole4.4.17` + `easyswoole 3.3.x`


## Framework startup

> docker run -ti -p 9501:9501 easyswoole/easyswoole3

The default working directory is: `/easyswoole`  
After the command is executed, the system will automatically enter the working directory, execute `PHP easywoole start` and access the host browser

## How to develop

The mapping function of docker can be used to map the host directory to the container. In the mapped directory, re install `easywoole` according to the framework installation document. In the host development, synchronous testing is carried out in the container.
:::tip
Note that in some environments, such as the `docker` environment of the `win10` system.
You can't use the virtual machine shared directory as the 'temp' directory of `easysoole`. Because of insufficient permissions, you can't create `socket`. This will result in an error: ` listen xxxxxx.sock  To do this, you can manually` dev.php `In the configuration file, change the 'temp' directory to another path, such as: `/ tmp`:::

## Dockerfile

`Dockerfile` It is a text file used to build an image. The text content contains instructions and instructions for building an image.

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
    && mv composer.phar /usr/bin/composer && chmod +x /use/bin/composer
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
