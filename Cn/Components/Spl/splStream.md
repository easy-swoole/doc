---
title: easysooole SplStream
meta:
  - name: description
    content: EasySwoole SplStream
  - name: keywords
    content: easysooole SplStream
---

# SplStream

资源流数据操作

## 相关class位置

- SplStream
    - `namespace`: `EasySwoole\Spl\SplStream`


## SplStream相关方法

| 方法名称           | 参数                                 | 说明                                       |
|:------------------|:-------------------------------------|:-------------------------------------------|
| __construct       | $resource = '',$mode = 'r+'          | 初始化资源和读写操作                         |
| __toString        |                                      | 输出资源                                    |                    
| close             |                                      | 关闭一个打开的文件指针                       |                    
| detach            |                                      | 获取资源并重置资源对象                       |                       
| getSize           | 获取资源大小                          | 编码转换                                    |                       
| tell              |                                      | 返回文件指针读/写的位置                      |                        
| eof               |                                      | 文件指针是否到了文件结束的位置                |                       
| isSeekable        |                                      | 获取是否可以在当前流中定位                    |                       
| seek              | $offset, $whence = SEEK_SET          | 在文件指针中定位                             |                       
| rewind            |                                      | 倒回文件指针的位置                           |                      
| isWritable        |                                      | 是否可写                                    |                       
| write             | $string                              | 写入内容                                    |                       
| isReadable        |                                      | 是否可读                                    |                       
| read              | $length                              | 读取内容                                    |                       
| length            |                                      | 获取字符串长度                              |                       
| getContents       |                                      | 读取资源流到一个字符串                       |                       
| getMetadata       | $key = null                          | 从封装协议文件指针中取得报头／元数据           |                       
| getStreamResource |                                      | 获取资源                                    |                       
| truncate          | $size = 0                            | 将文件截断到给定的长度                        |                                                                               


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

//初始化资源和读写操作
$resource = fopen('./test.txt', 'ab+');
$stream = new \EasySwoole\Spl\SplStream($resource);
var_dump($stream->__toString());

/**
 * 输出结果过：
 * string(10) "Easyswoole"
 */

//输出资源
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
var_dump($stream->__toString());
/**
 * 输出结果过：
 * string(10) "Easyswoole"
 */

//关闭一个打开的文件指针
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->close();
var_dump($stream->__toString());

/**
 * 输出结果过：
 * string(0) ""
 */

//获取资源并重置资源对象
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->detach();
var_dump($stream->__toString());

/**
 * 输出结果过：
 * string(0) ""
 */

//获取资源大小
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$size = $stream->getSize();
var_dump($size);

/**
 * 输出结果过：
 * int(10)
 */

//返回文件指针读/写的位置
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$position = $stream->tell();
var_dump($position);

/**
 * 输出结果过：
 * int(10)
 */

//文件指针是否到了文件结束的位置
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$eof = $stream->eof();
var_dump($eof);
$stream->detach();
$eof = $stream->eof();
var_dump($eof);

/**
 * 输出结果过：
 * bool(false)
 * bool(true)
 */

//获取是否可以在当前流中定位
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$seekable = $stream->isSeekable();
var_dump($seekable);

/**
 * 输出结果过：
 * bool(true)
 */

//在文件指针中定位
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->seek(2);
$position = $stream->tell();
var_dump($position);

/**
 * 输出结果过：
 * int(2)
 */

//倒回文件指针的位置
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->rewind();
$position = $stream->tell();
var_dump($position);

/**
 * 输出结果过：
 * int(0)
 */

//是否可写
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$writeAble = $stream->isWritable();
var_dump($writeAble);

/**
 * 输出结果过：
 * bool(true)
 */

//写入内容
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->write(', 666');
var_dump($stream->__toString());

/**
 * 输出结果过：
 * string(15) "Easyswoole, 666"
 */

//是否可读
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$readAble = $stream->isReadable();
var_dump($readAble);

/**
 * 输出结果过：
 * bool(true)
 */

//读取内容
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->rewind();
$string = $stream->read(4);
var_dump($string);

/**
 * 输出结果过：
 * string(4) "Easy"
 */

//读取资源流到一个字符串
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->rewind();
$string = $stream->getContents();
var_dump($string);

/**
 * 输出结果过：
 * string(10) "Easyswoole"
 */

//从封装协议文件指针中取得报头／元数据
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$meta = $stream->getMetadata();
var_dump($meta['stream_type']);

/**
 * 输出结果过：
 * string(6) "MEMORY"
 */

//获取资源
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$resource = $stream->getStreamResource();
fseek($resource, 0, SEEK_SET);
var_dump(stream_get_contents($resource));

/**
 * 输出结果过：
 * string(10) "Easyswoole"
 */

//将文件截断到给定的长度
$stream = new \EasySwoole\Spl\SplStream('Easyswoole');
$stream->truncate(4);
var_dump($stream->__toString());

/**
 * 输出结果过：
 * string(4) "Easy"
 */


```

::: warning 
 ps: 资源和资源流是有区别的,这里说的资源也就是数据或是变量,资源流是一种文件流。
:::

