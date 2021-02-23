---
title: Why easywoole uses connection pooling
meta:
  - name: description
    content: Why easywoole uses connection pooling
  - name: keywords
    content: Why easywoole uses connection pooling|Why swoole uses connection pooling|Why php uses connection pooling
---
# What is connection pooling

> Connection pooling is a technique for creating and managing a buffer pool of connections that are ready to be used by any thread that needs them.

In short, it is to create a container and prepare resources in advance, such as redis connection and MySQL connection.

## Advantages of connection pooling

A computer is made up of many parts, such as CPU, memory, hard disk and so on.
When we make network connection and request, we need to transfer and return various signals and data in different components
For example, in the CPU, memory, network card, data transfer, request, access.
If you connect to MySQL 10000 times in a short time, you need to cycle in this round-trip process, which wastes a lot of time and performance consumption on the road.
If we connect the connection well and put it in the connection pool, we can get it from the pool and perform the operation.
This saves the need to repeatedly create and disconnect connections.
It can reduce I / O operations and improve resource utilization.

## How to set the number of connection pools

How many pools should be set? Is the more the better?
The number of connections needs to be determined according to the concurrency number and the processing of the database,
For example, your database can only handle 500 connections at most. If you set 700 connections, the database still can't handle them. Setting too many connections is useless. On the contrary, it may cause database downtime
Therefore, in general, setting the total number of connection pools to about 100-200 is enough (equivalent to 200 concurrency)

::: warning
The number of connection pools here refers to the total number. In easywoole, according to the process, the configured number of connection pools for each process = the total number. For example, if the worker process in easywoole is 8, you can set 20, that is the total number of 20 \ * 8 = 160
::: 

## Why is pool empty in easywoole

There are several possibilities for this problem

-Connection information error, resulting in no resources
-There is a problem with the program. If the resource is taken out and not returned to the pool, it will be empty later
-The concurrency is high and the number of pools is small. You need to check the resource occupancy rate. If the occupancy rate is OK, increase the number of pools

### Connection error

If our MySQL configuration information is wrong, the connection pool will be initialized after the easywoole framework is started.
At this time, if the connection fails all the time, no resources are generated or put into the pool
When you get the resources in the pool in the follow-up program. Naturally, an empty pool error is reported.

### Procedural issues

Let's start with the pseudo code of the connection pool  
```php
<?php
	
class Pool{
	public static function getIn(){
		// Singleton mode
	}
	/**
	 * initialization
	 */
	public function init()
	{
		// When the pool is ready, fill in the specified resources, such as 10 connections
		$this->pool = $array;
	}
	
	public function get(){
		return array_pop($this->pool);
	}	
	public function push($obj)
	{
		$this->pool[] = $obj;
	}
}
```

If our program has such a scenario
```php
<?php
	
	$db = Pool::getIn()->get();
	$res = $db->query('sql');
```

Then, if the push return operation is not carried out, there will be no resources available once the resources in the pool are taken out.

In easywoole framework, the following methods are provided to obtain resources (take mysql-pool as an example)
```php
$db = MysqlPool::defer();
$db->rawQuery('select version()');
```
```php
$data = MysqlPool::invoker(function (MysqlConnection $db){
    return $db->rawQuery('select version()');
});
```
```php
$db = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj();
$data = $db->get('test');
//It needs to be recycled after use
PoolManager::getInstance()->getPool(MysqlPool::class)->recycleObj($db);
```

The defer method will be recycled automatically when the request process exits
Invoker is a closure function, which can be automatically recovered after one run
Get method is our way of pseudo code, you need to recycle by yourself, this method needs special attention ~!!!

> How to choose the two automatic recycling methods, please continue to see!

### Concurrent high resource utilization

As mentioned above, there are two ways to automatically recycle resources: defer and invoker
First of all, let's take a look at a point. Defer is automatically recycled when the coroutine exits. Normally, when a request arrives, spool will automatically create a coroutine for it. For example, when we request an HTTP API, we need the entire API to run before the coroutine exits
(it's equivalent to the complete execution of a script in our traditional FPM PHP)
At this time, the problem arises, if our business is like this

```php
<?php
	
	$db = MysqlPool::defer();
	$db->rawQuery('select version()');

	// Execute MySQL well and do other tasks
	
	// It takes 1.5s to complete other tasks


```

In fact, MySQL resources can only be used for less than 0.1s, but other operations take up a lot of script execution time. Resources will not be recycled until all operations are completed and the coroutine exits. This is a waste of resource utilization. The occupancy rate is relatively low.
！
If possible, we recommend using the invoker to perform an immediate resource recovery
At this time, we should pay attention to one point. If the program has more execution statements, it can either be executed in an invoker or reasonably use the invoker
Otherwise, the performance consumption will be transferred to get recycle


If there is no problem in the above troubleshooting, and it is confirmed that you have a large number of users and high concurrency, you can appropriately increase the number of the pool
