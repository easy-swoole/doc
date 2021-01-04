---
title: SnowFlake
meta:
  - name: description
    content: The snowflake algorithm generates a unique number.
  - name: keywords
    content: swoole|swoole extension|swoole framework|Easyswoole|Component Library|Miscellaneous Tools|SnowFlake
---

# Snowflake algorithm

## Use

Generate a unique number



## Core Object Class

To implement this component function you need to load the core class:

```php
EasySwoole\Utility\Random
```



## Core Object Method

#### Make

Generate a random number based on the snowflake algorithm

- mixed $dataCenterID data center
- mixed $workerID task process

```php
Static function make($dataCenterID = 0, $workerID = 0)
```



#### Unmake

Reverse analysis of the number generated by the snowflake algorithm

- mixed $snowFlakeId number

```php
static function unmake($snowFlakeId)
```



## How to use

```php
<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 19-1-9
 * Time: 10:10
 */

require './vendor/autoload.php';

$str = \EasySwoole\Utility\SnowFlake::make(1,1);//Incoming data center id (0-31), task process id (0-31)
var_dump($str);
var_dump(\EasySwoole\Utility\SnowFlake::unmake($str));

/**
 * Output results:
 * int(194470364728922112)
 * object(stdClass)#3 (4) {
 *   ["timestamp"]=>
 *   int(1532127766018)
 *   ["dataCenterID"]=>
 *   int(1)
 *   ["workerID"]=>
 *   int(1)
 *   ["sequence"]=>
 *   int(0)
 * }
 */
```
