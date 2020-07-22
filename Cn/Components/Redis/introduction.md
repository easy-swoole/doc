---
title: easyswoole redis协程客户端
meta:
  - name: description
    content: easyswoole redis协程客户端
  - name: keywords
    content: easyswoole redis协程客户端|swoole协程redis客户端|swoole redis客户端
---
# redis协程客户端
虽然swoole有着自带的协程客户端,但是在生产环境中我们发现了一些问题:  
swoole的redis客户端并没有完全支持redis的全部命令，比如geo搜索，还有事务，特别是集群模式的redis，swoole客户端并不支持。为此，我们决定用swoole的tcp客户端实现一个完整版的redis客户端。

目前,该redis客户端组件,已经支持除去脚本外的所有方法(目前支持了178个方法):  

- 连接方法(connection)
- 集群方法(cluster)
- geohash
- 哈希(hash)
- 键(keys)
- 列表(lists)
- 订阅/发布(pub/sub)
- 服务器(server)
- 字符串(string)
- 有序集合(sorted sets)
- 集合 (sets)
- 事务 (transaction)
- 管道实现 (pipe)  

> 由于redis的命令较多,可能漏掉1,2个命令

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.4.0
- easyswoole/spl: ^1.2

## 安装方法

> composer require easyswoole/redis

## 仓库地址

[easyswoole/redis](https://github.com/easy-swoole/redis)

## 基本使用

### redis单机配置
redis在实例化时,需要传入`\EasySwoole\Redis\Config\RedisConfig`实例:

| 配置名         | 默认参数           | 说明             | 备注                                               |
|:---------------|:-------------------|:-----------------|:---------------------------------------------------|
| host           | 127.0.0.1          | redis ip         |                                                    |
| port           | 6379               | redis端口        |                                                    |
| unixSocket     | null               | unixSocket文件路径        | 此参数配置后,将忽略host,port参数,直接通过UnixSocket连接.(>=1.3.0才可使用)                                                   |
| auth           |                    | auth密码         |                                                    |
| db             | null               | redis数据库      | 当db配置不等于null时,在connect的时候会自动select该配置 |
| timeout        | 3.0                | 超时时间         |                                                    |
| reconnectTimes | 3                  | 客户端异常重连次数 |                                                    |
| serialize      | SERIALIZE_NONE     | 数据是否序列化    |   序列化参数有:SERIALIZE_NONE,SERIALIZE_PHP,SERIALIZE_JSON                                                 |

### redsi单机配置示例


```php
$config = new \EasySwoole\Redis\Config\RedisConfig([
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'auth'      => 'easyswoole',
        'db'        => null,
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
    ]);
```

### redis集群配置

redis集群在实例化时,需要传入`\EasySwoole\Redis\Config\RedisConfig`实例:

```php
$config = new \EasySwoole\Redis\Config\RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ], [
        'auth' => '',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_PHP
    ]);
```
::: warning
集群配置先传入一个ip,port的多维数组,再传入其他配置项,其他配置项和redis单机配置一致  
:::
::: warning
需要注意,auth密码需要集群所有节点相同,只支持一个密码
:::


### redis单机使用示例
::: warning
使用redis客户端(需要协程环境)
:::
```php
<?php
include "../vendor/autoload.php";
go(function (){
    $redis = new \EasySwoole\Redis\Redis(new \EasySwoole\Redis\Config\RedisConfig([
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => 'easyswoole',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
    ]));
    var_dump($redis->set('a',1));
    var_dump($redis->get('a'));
});
```

### redis集群使用示例
```php
<?php
include "../vendor/autoload.php";
go(function () {
    $redis = new \EasySwoole\Redis\RedisCluster(new \EasySwoole\Redis\Config\RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ], [
        'auth' => '',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_PHP
    ]));
    var_dump($redis->set('a',1));
    var_dump($redis->get('a'));
    var_dump($redis->clusterKeySlot('a'));

});
```

## 回调事件
在`redis`组件中,自定义了2个回调事件,用于代码跟踪调试,可在config中设置:
```php
<?php
$redisConfig = new RedisConfig([
   'host' => REDIS_HOST,
   'port' => REDIS_PORT,
   'auth' => REDIS_AUTH,
]);
// 命令执行之前将调用
$redisConfig->onBeforeEvent(function ($commandName,$commandData){
   var_dump ($commandName,$commandData);
});
//命令获取到结果后将调用
$redisConfig->onAfterEvent(function ($commandName,$commandData,$result){
   var_dump ($commandName,$commandData,$result);
});
```

::: warning
回调事件支持事务,pipe.   
在pipe模式中,只有最后excePipe时才会调用回调事件.
:::

## 异常处理

redis组件根据错误的级别,区分了2种错误信息

### 异常

当redis连接失败,无法和redis服务通信时,将会抛出` EasySwoole\Redis\Exception\RedisException` 异常,例如配置错误:

```php
PHP Fatal error:  Uncaught EasySwoole\Redis\Exception\RedisException: connect to redis host 127.0.0.1:6379 fail after retry 4 times in /www/easyswoole/tioncico_redis/src/Redis.php:2866
Stack trace:
#0 /www/easyswoole/tioncico_redis/src/Redis.php(579): EasySwoole\Redis\Redis->sendCommand(Array)
#1 /www/easyswoole/tioncico_redis/tests/test.php(17): EasySwoole\Redis\Redis->get('a')
#2 {main}
  thrown in /www/easyswoole/tioncico_redis/src/Redis.php on line 2866

```

### 接管异常

```php
go(function () {

    $redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
    $redisConfig->setAuth('easyswoole');
    $redis = new \EasySwoole\Redis\Redis($redisConfig);
    try{

        $data = $redis->rawCommand(['set','a','1','1']);//多了一个参数,redis将会报语法错误
         var_dump($data);

    }catch (\EasySwoole\Redis\Exception\RedisException $exception){

        var_dump($exception->getMessage());
        var_dump($exception->getRedisErrorCode());
        var_dump($exception->getRedisErrorMsg());
    }
});
```



