---
title: easyswoole ORM安装
meta:
  - name: description
    content: easyswoole ORM安装
  - name: keywords
    content: easyswoole orm|swoole orm|swoole协程orm|swoole协程mysql客户端
---

# ORM

`Easyswoole`提供的一个全新协程安全的`ORM`封装。

## 安装

依赖关系

- swoole `>= 4.4.8`
- Easyswoole  `>=3.3.2` 
- mysqli > `2.x`


> composer require easyswoole/orm


## 配置信息注册

`ORM` 的连接配置信息（数据库连接信息）需要注册到连接管理器中。

### 数据库连接管理器

ORM的连接管理由```EasySwoole\ORM\DbManager```类完成，它是一个单例类。

```php
use EasySwoole\ORM\DbManager;

DbManager::getInstance();
```


### 注册数据库连接配置

你**可以**在框架 `initialize` 主服务创建事件中注册连接

```php
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;


public static function initialize()
{
    $config = new Config();
    $config->setDatabase('easyswoole_orm');
    $config->setUser('root');
    $config->setPassword('');
    $config->setHost('127.0.0.1');
    $config->setTimeout(15); // 超时时间

    DbManager::getInstance()->addConnection(new Connection($config));

    // 设置指定连接名称 后期可通过连接名称操作不同的数据库
    DbManager::getInstance()->addConnection(new Connection($config),'write');
}
```


### 数据库连接自带连接池说明

在默认实现中，ORM自带了一个`基于连接池`实现的连接类

`EasySwoole\ORM\Db\Connection` 实现了连接池的使用

```php
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;


public static function initialize()
{
    $config = new Config();
    $config->setDatabase('easyswoole_orm');
    $config->setUser('root');
    $config->setPassword('');
    $config->setHost('127.0.0.1');
    $config->setTimeout(15); // 超时时间
    //连接池配置
    $config->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
    $config->setIntervalCheckTime(30*1000); //设置检测连接存活执行回收和创建的周期
    $config->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
    $config->setMinObjectNum(5); //设置最小连接池存在连接对象数量
    $config->setMaxObjectNum(20); //设置最大连接池存在连接对象数量
    $config->setAutoPing(5); //设置自动ping客户端链接的间隔

    DbManager::getInstance()->addConnection(new Connection($config));
}
```
