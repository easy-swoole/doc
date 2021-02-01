---
title: EasySwooleUniversal connection pool
meta:
  - name: description
    content: EasySwooleUniversal connection pool,Universal connection pool,easyswooleUniversal connection pool
  - name: keywords
    content: swoole|swoole extension|swoole framework|easyswoole|Universal connection pool|swoole Universal connection pool|Universal connection pool
---
# Common connection pool 

Easysoole's common coroutine connection pool management.

## Component requirements

- php: >=7.1.0
- ext-json: *
- easyswoole/component: ^2.2.1
- easyswoole/spl: ^1.3
- easyswoole/utility: ^1.1

## Install 

> composer require easyswoole/pool

## Warehouse address

[easyswoole/pool](https://github.com/easy-swoole/pool)

## Config
When instantiating a connection pool object, you need to pass in a connection pool configuration object`EasySwoole\Pool\Config`,The properties of the object are as follows:

| Configuration item             | Default value  | explain                    | remarks                                                                                  |
|:-------------------|:--------|:------------------------|:--------------------------------------------------------------------------------------|
| $intervalCheckTime | 30*1000 | Timer execution frequency           | It is used to periodically perform connection pool object recycling and creation operations  |
| $maxIdleTime       | 15      | Maximum idle time of connection pool objects (seconds) | After this time, unused objects will be recycled by the timer     |
| $maxObjectNum      | 20      | Maximum number of connection pools           | Each process can create at most $maxobjectnum connection pool objects. If all objects are in use, it will return null or wait for the connection to be idle        |
| $minObjectNum      | 5       | Minimum number of connection pools (hot start)    | When the total number of connection pool objects is less than $minobjectnum, the connection will be created automatically to keep the connection active, so that the controller can get the connection as soon as possible |
| $getObjectTimeout  | 3.0     | Gets the timeout of the connection pool      | When the connection pool is empty, it will wait for $getobjecttimeout seconds. If there are connections idle during this period, it will return the connection object, otherwise it will return null    |
| $extraConf         |         | Additional configuration information             | Before instantiating the connection pool, you can put some additional configuration here, such as database configuration information, redis configuration, and so on|

## Pool Manager

Pool manager can do global connection pool management, such as in` EasySwooleEvent.php `After that, you can get the connection pool in the controller to get the connection

```php
public static function initialize()
{
    // TODO: Implement initialize() method.
    date_default_timezone_set('Asia/Shanghai');

    $config = new \EasySwoole\Pool\Config();

    $redisConfig1 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS1'));
    $redisConfig2 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS2'));
    //Register connection pool management objects
    \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config,$redisConfig1),'redis1');
    \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config,$redisConfig2),'redis2');

}
```

The controller gets the connection pool connection:
```php
public function index()
{
    //Take out the connection pool management object and getobj
   
    $redis1=\EasySwoole\Pool\Manager::getInstance()->get('redis1')->getObj();
    $redis2=\EasySwoole\Pool\Manager::getInstance()->get('redis1')->getObj();

    $redis1->set('name','test');
    var_dump($redis1->get('name'));

    $redis2->set('name','test2');
    var_dump($redis2->get('name'));

    //Recycling objects
    \EasySwoole\Pool\Manager::getInstance()->get('redis1')->recycleObj($redis1);
    \EasySwoole\Pool\Manager::getInstance()->get('redis2')->recycleObj($redis2);
}
```

## Pool object method

| Method name      | parameter                                     | explain                                                        | 备注                                         |
|:--------------|:-----------------------------------------|:-----------------------------------------------------------|:--------------------------------------------|
| createObject  |                                          | Abstract method, create connection object                                        |                                             |
| recycleObj    | $obj                                     | Recycle a connection                                                |                                             |
| getObj        | float $timeout = null, int $tryTimes = 3 | Get a connection, timeout $timeout, try to get $trytimes times            |                                             |
| unsetObj      | $obj                                     | Release a connection directly                                             |                                             |
| idleCheck     | int $idleTime                            | Recycle connections that are not queued for more than $idletime                             |                                             |
| itemIntervalCheck | ObjectInterface $item                            | Determine whether the current client is still available                             |                                             |
| intervalCheck |                                          | Reclaim the connection, and the hot start method, allowing external calls to the hot start                      |                                             |
| keepMin       | ?int $num = null                         | Keep minimum connection (hot start)                                        |                                             |
| getConfig     |                                          | Gets the configuration information of the connection pool                                        |                                             |
| status        |                                          | Get connection pool status information                                           | Get the current connection pool created, used, maximum created, minimum created data |
| isPoolObject  | $obj                                     | Check whether the $obj object was created by the connection pool                                |                                             |
| isInPool      | $obj                                     | Gets whether the current connection is not used in the connection pool                               |                                             |
| destroyPool   |                                          | Destroy the connection pool                                                |                                             |
| reset         |                                          | Reset the connection pool                                                |                                             |
| invoke        | callable $call,float $timeout = null     | Get a connection, pass it into the $call callback function for processing, and automatically recycle the connection after the callback |                                             |
| defer         | float $timeout = null                    | Get a connection and recycle it automatically after the collaboration                               |                                             |


### getObj
Gets the object of a connection pool:
```php
go(function (){
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redis = $redisPool->getObj();
    var_dump($redis->echo('仙士可'));
    $redisPool->recycleObj($redis);
});
```
::: warning
The objects obtained by getobj method must be recycled by calling unsetobj or recycleobj, otherwise the connection pool objects will be fewer and fewer
:::

### unsetObj
Release a connection pool object directly. Other collaborators can no longer obtain the connection, but will create a new one

::: warning
After release, the object is not destroyed immediately, but after the end of the scope
:::

### recycleObj
Recycle a connection object. After recycling, other coroutines can get the connection normally
::: warning
After recycling, other coroutines can get the connection normally, but at this time, the connection is still in the current coroutine. If the connection is called again for data operation, the coroutine will be confused, so developers need to restrict themselves. When recycleobj can no longer operate the object
:::

### invoke
Get a connection, pass it into the $call callback function for processing, and automatically recycle the connection after the callback:
```php
go(function (){
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redisPool->invoke(function (\EasySwoole\Redis\Redis $redis){
        var_dump($redis->echo('test'));
    });
});

```
::: warning
This method does not need to recycle the connection manually, but will recycle automatically after the callback function is finished
:::

### defer
Get a connection and recycle it automatically after the collaboration
```php
go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redis = $redisPool->defer();
    var_dump($redis->echo('test'));
});
```
::: warning
This method does not need to manually reclaim the connection, but automatically reclaims the connection after the end of the collaboration
:::

::: warning
Note that the defer method does not recycle until the end of the collaboration. If your current collaboration runs for a long time, it will not be recycled until the end of the collaboration
:::

### keepMin
Keep minimum connection (hot start)
Because easywoole/pool
When a service starts with excessive concurrency, it may suddenly need dozens or hundreds of connections. At this time, in order to disperse the time of connection creation, you can warm up and start the connection by calling keepmin
After calling this method, n connections will be created in advance for the controller to obtain the connection directly after the service is started
In`EasySwooleEvent.php`In `mainservercreate`, when the worker process starts, the connection is hot started
```php

public static function mainServerCreate(EventRegister $register)
{
    $register->add($register::onWorkerStart,function (\swoole_server $server,int $workerId){
        if ($server->taskworker == false) {
            //Each worker process pre creates a connection
            \EasySwoole\Pool\Manager::getInstance()->get('redis')->keepMin(10);
            var_dump(\EasySwoole\Pool\Manager::getInstance()->get('redis')->status());
        }
    });

    // TODO: Implement mainServerCreate() method.
}
```
Will output:
```
array(4) {
  ["created"]=>
  int(10)
  ["inuse"]=>
  int(0)
  ["max"]=>
  int(20)
  ["min"]=>
  int(5)
}
```

::: warning
 Keepmin creates different connections according to different processes. For example, if you have 10 worker processes, you will output 10 times, creating a total of 10 * 10 = 100 connections
:::

### getConfig
Gets the configuration of the connection pool:
```php
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->getConfig());

```

### destroyPool
Destroy connection pool  
After the call, all the remaining links in the connection pool will unsetobj, and the connection queue will be closed. After the call, getobj and other methods will be invalid
```php
go(function (){
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->getObj());
    $redisPool->destroyPool();
    var_dump($redisPool->getObj());
});
```
### reset
Reset the connection pool. After calling reset, the destroypool will be automatically called to destroy the connection pool, and the connection pool will be reinitialized at the next getobj
```php
go(function (){
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->getObj());
    $redisPool->reset();
    var_dump($redisPool->getObj());
});
```

### status
Get the current state of the connection pool. After calling, the output will be:
```
array(4) {
  ["created"]=>
  int(10)
  ["inuse"]=>
  int(0)
  ["max"]=>
  int(20)
  ["min"]=>
  int(5)
}
```
### idleCheck
Reclaim idle timed out connections

### intervalCheck
After calling this method, idlecheck and keepmin methods are called to manually recycle idle connections and manually hot start connections
```php
public function intervalCheck()
{
    $this->idleCheck($this->getConfig()->getMaxIdleTime());
    $this->keepMin($this->getConfig()->getMinObjectNum());
}
```

### itemIntervalCheck
When the internal timer discards the timeout client (if the client is idle for more than a specified time, it will be disconnected first), the itemintervalcheck function will be triggered, and the client will be passed in, which can realize the user's own logic to judge whether the client is available.
If the function returns true, it means available (by default) and returns false, which will cause the client to discard directly.
Can be used to: maintain client heartbeat, etc. For example, the usage scenarios in ORM are as follows: maintain the MySQL connection and reduce the probability of MySQL offline gone away

```php
    /**
     * @param MysqliClient $item
     * @return bool
     */
    public function itemIntervalCheck($item): bool
    {
        /*
         * If the last use time exceeds the autoping interval
         */
        /** @var Config $config */
        $config = $this->getConfig();
        if($config->getAutoPing() > 0 && (time() - $item->__lastUseTime > $config->getAutoPing())){
            try{
                //Execute an SQL to trigger active information
                $item->rawQuery('select 1');
                //Mark the usage time to avoid being used again
                $item->__lastUseTime = time();
                return true;
            }catch (\Throwable $throwable){
                //Exception indicates that there is an error in the link, return to recycle
                return false;
            }
        }else{
            return true;
        }
    }
```

## Basic use

### Define pool objects
```php
class Std implements \EasySwoole\Pool\ObjectInterface {
    function gc()
    {
        /*
         * When this object is unset by pool
         */
    }

    function objectRestore()
    {
        /*
         * Back to the connection pool
         */
    }

    function beforeUse(): ?bool
    {
        /*
         * If false is returned when taking out the connection pool, the current object will be discarded and recycled
         */
        return true;
    }

    public function who()
    {
        return spl_object_id($this);
    }
}
```
### Define pool
```php

class StdPool extends \EasySwoole\Pool\AbstractPool{
    
    protected function createObject()
    {
        return new Std();
    }
}

```
> You don't have to create a return ```EasySwoole\Pool\ObjectInterface``` Object, any type of object

After pool component version '> = 1.0.2', magic pool support is provided to quickly define pool

```php
use \EasySwoole\Pool\MagicPool;
$magic = new MagicPool(function (){
    return new \stdClass(); // Example, you can return the object that implements the objectinterface
});

// Get after registration
$test = $magic->getObj();
// return
$magic->recycleObj($test);
```

The second parameter of magic pool construction method can receive a config (easysoole / pool / Config class) to define the number of pools and other configurations.


### Simple example
```php

$config = new \EasySwoole\Pool\Config();
$pool = new StdPool($config);

go(function ()use($pool){
    $obj = $pool->getObj();
    $obj2 = $pool->getObj();
    var_dump($obj->who());
    var_dump($obj2->who());
});
```

## Advanced use

[Redis connection pool based on pool](../Redis/pool.html)

[MySQL connection pool based on pool]()

## Related warehouse

[easyswoole/redis-pool](https://github.com/easy-swoole/redis-pool)
