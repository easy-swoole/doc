---
title: EasySwoole SplFileStream
meta:
  - name: description
    content: EasySwoole SplFileStream
  - name: keywords
    content: EasySwoole SplFileStream
---

# SplFileStream

文件资源流数据操作

## 相关class位置

- SplFileStream
    - `namespace`: `EasySwoole\Spl\SplFileStream`


## SplFileStream相关方法

| 方法名称           | 参数                          | 说明                              |
|:------------------|:------------------------------|:---------------------------------|
| __construct       | $file,$mode = 'c+'            | 初始化资源和读写操作               |
| lock              | $mode = LOCK_EX               | 文件锁定                          |                    
| unlock            | $mode = LOCK_UN               | 释放锁定                          |                                                                                                   

::: warning 
SplFileStream类继承SplStream，其他相关方法参考[SplStream](splStream.html)。
:::


## 基本使用


```php
<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 19-7-2
 * Time: 上午10:25
 */

require_once 'vendor/autoload.php';

$fileStream = new \EasySwoole\Spl\SplFileStream('./test.txt');
$type = $fileStream->getMetadata('stream_type');
var_dump($type);

/**
 * 输出结果过：
 * string(5) "STDIO"
 */


$fileStream = new \EasySwoole\Spl\SplFileStream('./test.txt');
$lock = $fileStream->lock();
var_dump($lock);

/**
 * 输出结果过：
 * bool(true)
 */

$fileStream = new \EasySwoole\Spl\SplFileStream('./test.txt');
$unlock = $fileStream->unlock();
var_dump($unlock);

/**
 * 输出结果过：
 * bool(true)
 */

```






