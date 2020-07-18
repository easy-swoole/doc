---
title: EasySwoole SplString
meta:
  - name: description
    content: EasySwoole SplString
  - name: keywords
    content: EasySwoole SplString
---
# SplString

用于处理字符串。

## 相关class位置

- SplString
    - `namespace`: `EasySwoole\Spl\SplString`


## SplString相关方法

| 方法名称         | 参数                                                                   | 说明                                        |
|:----------------|:----------------------------------------------------------------------|:-------------------------------------------|
| setString       | string $string                                                        | 设置字符串                                  |
| split           | int $length = 1                                                       | 按长度分割字符串                             |                    
| explode         | string $delimiter                                                     | 按分隔符分割字符串                           |                    
| subString       | int $start, int $length                                               | 截取字符串                                  |                       
| encodingConvert | string $desEncoding, $detectList = ['UTF-8', 'ASCII', 'GBK',...]      | 编码转换                                    |                       
| utf8            |                                                                       | 转成utf                                    |                        
| unicodeToUtf8   |                                                                       | 将unicode编码转成utf-8                      |                       
| toUnicode       |                                                                       | 转成unicode编码(秒)                         |                       
| compare         | string $str, int $ignoreCase = 0                                      | 二进制字符串比较                             |                       
| lTrim           | string $charList = " \t\n\r\0\x0B"                                    | 删除字符串开头的空白字符（或其他字符）         |                      
| rTrim           | string $charList = " \t\n\r\0\x0B"                                    | 删除字符串末端的空白字符（或者其他字符）       |                       
| trim            | string $charList = " \t\n\r\0\x0B"                                    | 去除字符串首尾处的空白字符（或者其他字符）      |                       
| pad             | int $length, string $padString = null, int $pad_type = STR_PAD_RIGHT  | 使用另一个字符串填充字符串为指定长度           |                       
| repeat          | int $times                                                            | 重复一个字符串                              |                       
| length          |                                                                       | 获取字符串长度                              |                       
| upper           |                                                                       | 将字符串转化为大写                           |                       
| lower           |                                                                       | 将字符串转化为小写                           |                       
| stripTags       | string $allowable_tags = null                                         | 从字符串中去除 HTML 和 PHP 标记1             |                       
| replace         | string $find, string $replaceTo                                       | 子字符串替换                                |                       
| between         | string $startStr, string $endStr                                      | 获取指定目标的中间字符串                     |                       
| regex           | $regex, bool $rawReturn = false                                       | 按照正则规则查找字符串                       |                       
| exist           | string $find, bool $ignoreCase = true                                 | 是否存在指定字符串                           |                       
| kebab           |                                                                       | 转换为烤串                                  |                       
| snake           | string $delimiter = '_'                                               | 转为蛇的样子                                |                       
| studly          |                                                                       | 驼峰                                       |                       
| camel           |                                                                       | 小驼峰                                     |                       
| replaceArray    | string $search, array $replace                                        | 依次替换字符串                              |                       
| replaceFirst    | string $search, string $replace                                       | 替换字符串中给定值的第一次出现                |                       
| replaceLast     | string $search, string $replace                                       | 替换字符串中给定值的最后一次出现              |                       
| start           | string $prefix                                                        | 以一个给定值的单一实例开始一个字符串          |                       
| after           | string $search                                                        | 在给定的值之后返回字符串的其余部分            |                       
| before          | string $search                                                        | 在给定的值之前获取字符串的一部分              |                       
| endsWith        | $needles                                                              | 确定给定的字符串是否以给定的子字符串结束       |                       
| startsWith      | $needles                                                              | 确定给定的字符串是否从给定的子字符串开始                                                                 

## 基础使用

```php
<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 19-1-9
 * Time: 上午10:10
 */

require './vendor/autoload.php';

//设置字符串
$string = new \EasySwoole\Spl\SplString();
var_dump($string->setString('Hello, EasySwoole')->__toString());

/**
 * 输出结果过：
 * string(17) "Hello, EasySwoole"
 */

//设置数组中某项的值
$string = new \EasySwoole\Spl\SplString('Hello, EasySwoole');
var_dump($string->split(5)->getArrayCopy());

/**
 * 输出结果过：
 * array(4) {
 *   [0]=>
 *   string(5) "Hello"
 *   [1]=>
 *   string(5) ", Eas"
 *   [2]=>
 *   string(5) "ySwoo"
 *   [3]=>
 *   string(2) "le"
 * }
 */

//分割字符串
$string = new \EasySwoole\Spl\SplString('Hello, EasySwoole');
var_dump($string->explode(',')->getArrayCopy());

/**
 * 输出结果过：
 * array(2) {
 *   [0]=>
 *   string(5) "Hello"
 *   [1]=>
 *   string(11) " EasySwoole"
 * }
 */

//截取字符串
$string = new \EasySwoole\Spl\SplString('Hello, EasySwoole');
var_dump($string->subString(0, 5)->__toString());

/**
 * 输出结果过：
 * string(5) "Hello"
 */

//编码转换
$string = new \EasySwoole\Spl\SplString('Hello, EasySwoole');
var_dump($string->encodingConvert('UTF-8')->__toString());

/**
 * 输出结果过：
 * string(17) "Hello, EasySwoole"
 }

 */

//转成utf-8
$string = new \EasySwoole\Spl\SplString('Hello, EasySwoole');
var_dump($string->utf8()->__toString());

/**
 * 输出结果过：
 * string(17) "Hello, EasySwoole"
 }

 */

//将unicode编码转成utf-8
$str = '\u4e2d';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->unicodeToUtf8()->__toString());

/**
 * 输出结果过：
 * string(3) "中"
 */

//转成unicode编码
$str = '中';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->toUnicode()->__toString());

/**
 * 输出结果过：
 * string(6) "\U4E2D"
 */

//二进制字符串比较
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->compare('apple'));

/**
 * 输出结果过：
 * int(19)
 */

//删除字符串开头的空白字符（或其他字符）
$str = '  test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->lTrim()->__toString());

/**
 * 输出结果过：
 * string(4) "test"
 */

//删除字符串末端的空白字符（或者其他字符）
$str = 'test  ';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->rTrim()->__toString());

/**
 * 输出结果过：
 * string(4) "test"
 */


//去除字符串首尾处的空白字符（或者其他字符）
$str = '  test  ';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->trim()->__toString());

/**
 * 输出结果过：
 * string(4) "test"
 */

//使用另一个字符串填充字符串为指定长度
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->pad(5, 'game')->__toString());

/**
 * 输出结果过：
 * string(5) "testg"
 */

//重复一个字符串
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->repeat(2)->__toString());

/**
 * 输出结果过：
 * string(8) "testtest"
 */

//获取字符串长度
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->length());

/**
 * 输出结果过：
 * int(4)
 */

//将字符串转化为大写
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->upper()->__toString());

/**
 * 输出结果过：
 * string(4) "TEST"
 */

//将字符串转化为小写
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->lower()->__toString());

/**
 * 输出结果过：
 * string(4) "test"
 */

//从字符串中去除 HTML 和 PHP 标记
$str = '<a>test</a>';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->stripTags()->__toString());

/**
* 输出结果过：
 * string(4) "test"
 */

//字符串替换
$str = 'test';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->replace('t', 's')->__toString());

/**
 * 输出结果过：
 * string(4) "sess"
 */

//获取指定目标的中间字符串
$str = 'easyswoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->between('easy', 'le')->__toString());

/**
 * 输出结果过：
 * string(4) "swoo"
 */

//按照正则规则查找字符串
$str = 'easyswoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->regex('/swoole/'));

/**
 * 输出结果过：
 * string(6) "swoole"
 */

//是否存在指定字符串
$str = 'easyswoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->exist('Swoole', true));

/**
 * 输出结果过：
 * bool(true)
 */

//转换为-连接的字符串
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->kebab()->__toString());

/**
 * 输出结果过：
 * string(11) "easy-swoole"
 */

//转为蛇的样子
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->snake()->__toString());

/**
 * 输出结果过：
 * string(11) "easy_swoole"
 */


//转换为驼峰
$str = 'easy_swoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->studly()->__toString());

/**
 * 输出结果过：
 * string(10) "EasySwoole"
 */

//转换为小驼峰
$str = 'easy_swoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->camel()->__toString());

/**
 * 输出结果过：
 * string(10) "easySwoole"
 */

//给数组每个元素替换字符串
$str = 'easy_swoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->replaceArray('easy', ['as', 'bs', 'cs'])->__toString());

/**
 * 输出结果过：
 * string(9) "as_swoole"
 */

//替换字符串中给定值的第一次出现
$str = 'easy_swoole_easy';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->replaceFirst('easy', 'as')->__toString());

/**
 * 输出结果过：
 * string(14) "as_swoole_easy"
 */

//替换字符串中给定值的最后一次出现
$str = 'easy_swoole_easy';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->replaceLast('easy', 'as')->__toString());

/**
 * 输出结果过：
 * string(14) "easy_swoole_as"
 */

//以一个给定值的单一实例开始一个字符串
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->start('Hello,')->__toString());

/**
 * 输出结果过：
 * string(16) "Hello,EasySwoole"
 */

//在给定的值之后返回字符串的其余部分
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->after('Easy')->__toString());

/**
 * 输出结果过：
 * string(6) "Swoole"
 */

//在给定的值之前获取字符串的一部分
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->before('Swoole')->__toString());

/**
 * 输出结果过：
 * string(4) "Easy"
 */

//确定给定的字符串是否以给定的子字符串结束
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->endsWith('Swoole'));

/**
 * 输出结果过：
 * bool(true)
 */

//确定给定的字符串是否从给定的子字符串开始
$str = 'EasySwoole';
$string = new \EasySwoole\Spl\SplString($str);
var_dump($string->startsWith('Easy'));

/**
 * 输出结果过：
 * bool(true)
 */

```



