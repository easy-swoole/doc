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

## 开发者必读

此部分对于ORM的学习和使用非常重要，遇到使用问题请先确保有认真看完和排查后再进行提问和反馈。

### 设计思想

`ORM`全称：`object relational mapping`，目的是想像操作对象一样操作数据库，是符合面向对象开发思想的。

如将一条数据的插入，映射成一个对象的实例化。

```php
$user = UserModel::create();
$user->data([
    'attr' => 'value'
]);
$user->save();
```

### 常见问题汇总

#### 重复使用Model对象

::: tip
一个对象映射一条数据，此时会有很多用习惯了db封装组件的小伙伴把Model当成了db封装使用，重复调用一个Model对象，如下
:::

```php
// 错误使用

// 假设id自增
$user = UserModel::create();

// 插入一条新用户
$user->data([
    'attr' => 'value'
]);
$user->save();

// 插入第二条新用户，此时由于重复调用同一个对象，产生报错，自增id主键重复
$user->data([
    'attr' => 'value2'
]);
$user->save();
```

#### ORM生成复杂sql

1.ORM是基于 `mysqli 2.x`组件完成，内部引用 mysqli中的 `QueryBuilder类`完成sql构造，并且在`查询`章节注明了闭包函数使用方式（可直接使用mysqli中的绝大部分连贯操作，如 having 等特殊条件）

2.如果mysqli的连贯操作也无法满足，你有以下几种方式解决该问题
 - 使用自定义sql执行
 - 尝试给组件贡献代码，新增功能特性
 - 提出反馈，在精力允许和大众所需时，我们会维护组件升级


#### 优雅删除数据

在ORM设计思想中，对数据的操作映射为对对象的操作，如果按照此原则，那么需要我们先查询出对象，然后再调用对象的`destroy()方法`进行删除。

但是对于执行效率的消耗来说，此次查询在部分业务场景下是无用的。

那么我们到底是否需要遵守设计原则？一般情况下是在`操作前需要校验数据是否存在`时遵守，无需校验则直接传参删除条件即可。

设计原则代表思想，在某些场景下遵守它需要付出一定代价，自己根据喜好去决定它。

```php
$user = User::create()->get($param['id']);

if (!$user){
    return '操作数据不存在，请检查再试';
}
$res = $user->destroy();
```


#### 连接预热
为了避免链接空档期突如其来的高并发，我们可以做数据库链接预热，也就是worker启动的是时候，提前准备好链接。
```php
<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        $config = new Config(GlobalConfig::getInstance()->getConf("MYSQL"));
        DbManager::getInstance()->addConnection(new Connection($config));
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $register->add($register::onWorkerStart,function (){
            // 链接预热
            // ORM 1.4.31 版本之前请使用 getClientPool() 
            // DbManager::getInstance()->getConnection()->getClientPool()->keepMin();
            
            DbManager::getInstance()->getConnection()->__getClientPool()->keepMin();
        });
    }
}
```


#### 断线问题

##### 为什么会断线？
在连接池模式下，一个连接创建后，并不会因为因为请求结束而断开，就好比php-fpm下的pconnect特性一样。而一个连接
建立后，可能会因为太久没有使用(执行sql),而被mysql服务端主动断了连接，或者是因为链路问题，切断了连接。而被切断的时候。我们并不知道这件事。因此就导致了我们用了
一个断线的连接去执行sql，从而出现断线错误或者是异常。

##### 如何解决
与java全家桶的原理一致，我们需要做的事情就是：
- 定时检查连接是否可用
- 定时检查连接的最后一次使用状态

因此我们在EasySwoole的orm中，我们的```IntervalCheckTime```配置项目指定的就是多久做一次周期检查，
```MaxIdleTime```指的是如果一个连接超过这个时间没有使用，则会被回收。```AutoPing```指的是多久执行一个```select 1```用来触发这个连接，使得被mysql服务端标记为活跃而不会被回收。
如果经常出现断线，可以适当缩短时间。

##### 百分百不会断线了?
理论上，做了上面的步骤，出现使用断线连接的概率是非常低的，但是、并不是真的就百分百稳了，比如极端情况，mysql服务重启，或者是链路断线了。
因此，我们一定要做以下类似事情：
```
try{
    $client = $pool->getClient()
    $cilient->query(xxxxxx);
}catch(\Throwable $t){}

```
也就是，任何orm的使用，一定要try。至于为何，请参考java为何强制对任何数据库io作try.

##### 为何不能做自动重连
我们可以看到，在某些自以为很聪明的框架中，有这样的操作
```
$client = $pool->getClient();
try{
    return  $client->query();
}catch(\Throwable $t){
     //2006 2002 为断线
    if($client->getError() == '2006'){
        $client->connect();
        return $client->query();
    }else{
        throw $t;
    }
}
```
乍一看，没有什么问题。实际上,按照上面的重连，我们来看看：
```
$client = $pool->getClient();
$client->startTransaction();
$client->query(query one);
//client disconnect case network
$client->reconnect();
$client->query(query two);
$client->commit();
```
这样，在极端情况下，会导致query one结果丢失，但是query two却执行了，这对于事务来说，是不可原谅的。
此刻又会有人说，那我判断下链接是不是在事务中不就好了。实际上，远远没这么简单。为此，最好的方式就是我们养成良好的习惯。任何的数据库io，都做try操作，与java一致。
