---
title: easyswoole ORM 必读章节
meta:
  - name: description
    content: easyswoole ORM 必读章节
  - name: keywords
    content:  easyswoole ORM 必读章节
---

# 开发者必读

此部分对于 `ORM` 的学习和使用非常重要。遇到使用问题时，请先确保自己认真看完了 `ORM` 文档，在对问题进行排查后，实在不能解决再进行提问和反馈。

## 设计思想

`ORM` 全称：`object relational mapping`，目的是想像操作对象一样去操作数据库，是符合面向对象开发思想的。

如将一条数据的插入，映射成一个对象的实例化。伪代码如下所示：

```php
<?php
$user = UserModel::create();
$user->data([
    'attr' => 'value'
]);
$user->save();
```

## 常见问题汇总

### 重复使用 `Model` 对象

::: tip
  一个对象映射一条数据，此时会有很多用习惯了 `db` 封装组件的小伙伴把 `Model` 当成了 `db` 封装使用，重复调用一个 `Model` 对象，如下所示：
:::

```php
<?php
// 错误使用

// 假设id自增
$user = UserModel::create();

// 插入一条新用户
$user->data([
    'attr' => 'value'
]);
$user->save();

// 插入第二条新用户，此时由于重复调用同一个对象，产生报错，自增 id 主键重复
$user->data([
    'attr' => 'value2'
]);
$user->save();
```

### `ORM` 生成复杂 `sql`

1. `ORM` 是基于 `mysqli 2.x` 组件实现，内部引用 `mysqli` 组件中的 `QueryBuilder 类` 完成 `sql` 的构造，并且在 [`查询` 章节](/Components/Orm/query.md) 注明了闭包函数使用方式（可直接使用 `mysqli` 组件中的绝大部分连贯操作，如 `having` 等特殊条件）

2. 如果 `mysqli` 组件的连贯操作也无法满足您的需求，您有以下几种方式解决该问题：
 - 使用自定义 `sql` 执行
 - 尝试给组件贡献代码，新增功能特性
 - 提出反馈，我们会在精力允许和大众所需时，对组件进行维护升级

### 优雅删除数据

在 `ORM` 设计思想中，对数据的操作映射为对对象的操作，如果按照此原则，那么需要我们先查询出对象，然后再调用对象的 `destroy()` 方法进行删除。

但是对于执行效率的消耗来说，此次查询在部分业务场景下是无用的。

那么我们到底是否需要遵守设计原则？一般情况下是在 `操作前需要校验数据是否存在` 时遵守，无需校验则直接根据删除条件传参删除即可。

设计原则代表思想，在某些场景下遵守它需要付出一定代价，开发者可以根据喜好去决定是否遵守。

删除数据伪代码如下所示：

```php
<?php
$user = User::create()->get($param['id']);

if (!$user){
    return '操作数据不存在，请检查再试';
}

$res = $user->destroy();
```

### 连接预热

为了避免连接空档期突如其来的高并发，我们可以对数据库连接预热，也就是 `Worker` 进程启动的时候，提前准备好数据库连接。

对连接进行预热使用实例如下所示：

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
### 断线问题

#### 为什么会断线？

在连接池模式下，一个连接创建后，并不会因为因为请求结束而断开，就好比 `php-fpm` 下的 `pconnect` 特性一样。而一个连接建立后，可能会因为太久没有使用(执行 `sql` )，而被`mysql` 服务端主动断开了连接，或者是因为链路问题，切断了连接。而连接被切断的时候。我们并不知道这件事。因此就导致了我们用了一个断线的数据库连接去执行 `sql`，从而出现断线错误或者异常。

#### 如何解决短线问题？

与 `java` 全家桶的原理一致，我们需要做的事情就是：
- 定时检查连接是否可用
- 定时检查连接的最后一次使用状态

因此在 `EasySwoole` 的 `ORM` 中，`ORM` 组件提供了 ```IntervalCheckTime``` 配置项，它指定的就是多久做一次周期检查，```MaxIdleTime``` 配置项指的是如果一个连接超过这个时间没有使用，则会被回收。```AutoPing``` 配置项指的是多久执行一个 ```select 1``` 用来触发这个连接，让这个连接被 `mysql` 服务端标记为活跃而不会被回收。如果经常出现断线，可以适当缩短 `周期性检查` 和 `AutoPing` 的时间（即调整 ```IntervalCheckTime```、```AutoPing``` 配置项的值）。

#### 百分百不会断线了？

理论上，做了上面的步骤，出现使用断线连接的概率是非常低的，但是并不是真的就百分百稳了，比如极端情况：`mysql` 服务重启，或者是链路断线了。因此，我们一定要做类似如下这样的处理措施：

```php
<?php
try {
    $client = $pool->getClient();
    $cilient->query(xxxxxx);
} catch (\Throwable $t) {}
```

也就是说，任何 `orm` 的使用，一定要 `try`。至于为何，请参考 `java` 为何强制对任何数据库 `io` 做 `try` 处理。

#### 为何不能做自动重连

我们可以看到，在某些自以为很聪明的框架中，有下面这样的操作：

```php
<?php
$client = $pool->getClient();
try {
    return $client->query();
} catch(\Throwable $t) {
    // 2006 2002 为断线
    if ($client->getError() == '2006') {
        $client->connect();
        return $client->query();
    } else {
        throw $t;
    }
}
```

乍一看，没有什么问题。实际上，按照上面的重连，我们来看看下面的伪代码：

```php
<?php
$client = $pool->getClient();
$client->startTransaction();
$client->query(query one);
// client disconnect case network
$client->reconnect();
$client->query(query two);
$client->commit();
```

这样，在极端情况下，会导致 `query one` 结果丢失，但是 `query two` 却执行了，这对于事务来说，是不可原谅的。此刻又会有人说，那我判断下链接是不是在事务中不就好了。实际上，远远没这么简单。为此，最好的方式就是我们养成良好的习惯。任何的数据库 `io`，都做 `try` 操作，与 `java` 一致。
