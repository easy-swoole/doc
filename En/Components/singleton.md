---
title: easyswoole singleton
meta:
  - name: description
    content: easyswoole singleton
---
# Singleton
Singleton mode ensures that a class can only have one instance globally, because its instance is saved by itself, and the class cannot be instantiated outside the class.

## Effect
The singleton mode of PHP is to avoid the resource consumption caused by repeatedly creating objects.

## Purpose
In the actual project, such as database query, log output, global callback, unified verification and other modules. These modules have a single function, but need to be accessed many times. If they can be globally unique, multiple reuse will greatly improve the performance.

## Example 

```php

namespace EasySwoole\Component;

class MySingleton
{
    use Singleton;
}

$mySingleton = Mysingleton::getInstance();

``` 


## Core object method

Core class：EasySwoole\Component\Singleton。

Get object

* mixed     $args     parameter

```php
static function getInstance(...$args)
```    
