---
title: easyswoole ORM核心文件
meta:
  - name: description
    content: easyswoole ORM核心文件
  - name: keywords
    content: easyswoole orm|swoole orm|swoole协程orm|swoole协程mysql客户端
---

# 核心文件

本章节对核心`Config`、`Connection`、`DbManager`源码分析，方便开发者快速上手。

## Config

`Orm`自带连接池，因此继承了`EasySwoole\Pool\Config`，并且父级继承了`EasySwoole\Spl\SplBean`具有对类属性快速赋值的操作。

**实例**

```php
$config = new \EasySwoole\ORM\Db\Config();
```

### 基本配置

**设置host**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setHost('127.0.0.1');
$config->setHost('127.0.0.1:3306');
```

**设置port**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setPort(3306);
```

**设置用户名**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setUser('root');
```

**设置密码**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setPassword('easyswoole');
```

**设置数据库**
```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setDatabase('easyswoole');
```

**设置charset**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setCharset('utf8');
```

**设置严格模式**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setStrictType(true);
```

**设置fetchMode**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setFetchMode(true);
```

**设置超时时间**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setTimeout(15);
```

**设置返回结果为结果集**

可以快速`all()->toArray()`

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setReturnCollection(true);
```
### 连接池配置

**设置获取连接池对象超时时间**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setGetObjectTimeout(3.0);
```
**设置检测连接存活执行回收和创建的周期**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setIntervalCheckTime(30*1000);
```

**连接池对象最大闲置时间(秒)**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setMaxIdleTime(15);
```

**设置最小连接池存在连接对象数量**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setMinObjectNum(5);
```

**设置最大连接池存在连接对象数量**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setMaxObjectNum(15);
```

**设置自动ping客户端链接的间隔**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$config->setAutoPing(5);
```

### 快速设置

```php
$config = new \EasySwoole\ORM\Db\Config([
    'host' => '127.0.0.1',
    'autoPing' => 5
]);
```

## Connection

**实例**

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$connection = new \EasySwoole\ORM\Db\Connection($config);
```

**获取池**
```php
/** @var \EasySwoole\ORM\Db\Connection $connection **/
$connection->getClientPool();
```

**defer**

`timeout`参数为空 默认获取`config`的`timeout`，此方法会自动回收对象，用户无需关心。

```php
/** @var \EasySwoole\ORM\Db\Connection $connection **/
$connection->defer();
$connection->defer(3.0);
```

**获取注入的config**

```php
/** @var \EasySwoole\ORM\Db\Connection $connection **/
$connection->getConfig();
```

## DbManager

`DbManager`采用单例，注意：进程间数据是隔离的。

**实例**

```php
\EasySwoole\ORM\DbManager::getInstance();
```

**回调事件**

具体注入参数请看[回调事件](/Components/Orm/Event/onQuery.html)

```php
\EasySwoole\ORM\DbManager::getInstance()->onQuery(function (){

});
```

**注入连接池**

参数：
- `$connection` 连接池对象
- `$connectionName` 连接池名称 默认`default` 可进行读写分离配置

```php
/** @var \EasySwoole\ORM\Db\Config  $config **/
$connection = new \EasySwoole\ORM\Db\Connection($config);
\EasySwoole\ORM\DbManager::getInstance()->addConnection($connection);

\EasySwoole\ORM\DbManager::getInstance()->addConnection($connection,'write');
```

**获取连接池**

参数：
- `$connectionName` 连接池名称

```php
\EasySwoole\ORM\DbManager::getInstance()->getConnection();
```

**query**

参数：
- `$builder` QueryBuilder
- `$raw` 是否raw执行，否则预处理
- `$connection` 指定连接池名称 或者 连接
- `$timeout` 超时时间

```php
\EasySwoole\ORM\DbManager::getInstance()->query(new \EasySwoole\Mysqli\QueryBuilder());
```

**事务**

注意事务协程上下文。

参数：
- `$con` 指定连接池名称 或者 连接
- `$timeout` 超时时间

```php
\EasySwoole\ORM\DbManager::getInstance()->startTransaction();
\EasySwoole\ORM\DbManager::getInstance()->commit();
\EasySwoole\ORM\DbManager::getInstance()->rollback();
```

**invoke**

使用`invoke`方式，让`ORM`查询结束后马上归还资源，可以提高资源的利用率。

参数：
- `$call` 回调函数
- `$connectionName` 指定连接池名称
- `$timeout` 超时时间

```php
\EasySwoole\ORM\DbManager::getInstance()->invoke(function (\EasySwoole\ORM\Db\ClientInterface $client){

    \EasySwoole\ORM\DbManager::getInstance()->startTransaction($client);
    \EasySwoole\ORM\DbManager::getInstance()->commit($client);
    \EasySwoole\ORM\DbManager::getInstance()->rollback($client);

});
```

**查询该连接是否处于事务上下文**

```php
/** @var \EasySwoole\ORM\Db\ClientInterface $client **/
\EasySwoole\ORM\DbManager::isInTransaction($client);
```
