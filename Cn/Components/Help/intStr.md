---
title: easyswoole辅助类
meta:
  - name: description
    content: easyswoole IntStr
  - name: keywords
    content: easyswoole IntStr
---

# IntStr

## 功能介绍

用于 `整数`(需要转换的整数必须在 `0 ~ 9223372036854775668` 范围之内) 和 `字符串` 的相互转换，即：可以把一个字符串转换成一个数字，反之，通过这个数字，我们可以得到之前的字符串。

可用于生成 `url 短链接`。

## 相关class位置

- IntStr
    - `namespace`: `EasySwoole\Utility\IntStr` 

## 核心对象方法

#### toAlpha

生成基于 `整数` 对应的 `字符串`

- int $number 要生成字符串的数字

```php
public static function toAlpha(int $number): string
```

#### toNum

反向解析 `字符串` 对应的 `数字`

- string $string 待解析的字符串

```php
public static function toNum(string $string): int
```


## 基本使用

```php
<?php
require __DIR__ . '/vendor/autoload.php';

// 传入小于 9223372036854775668 的整数，得到一个字符串，通过此字符串可以反向解析成对应的数组
$str = \EasySwoole\Utility\IntStr::toAlpha(122407155078249761);
var_dump($str);

// 传入字符串得到对应的数字
$num = \EasySwoole\Utility\IntStr::toNum('EasySwoole');
var_dump($num);

// 用于生成短链接
$domain_prefix = 'https://easyswoole.com';
$path1 = \EasySwoole\Utility\IntStr::toNum('Preface');
$path2 = \EasySwoole\Utility\IntStr::toNum('intro');
$new_short_url = "{$domain_prefix}/{$path1}/{$path2}";
var_dump($new_short_url); // 生成的短链接

$real_path1 = \EasySwoole\Utility\IntStr::toAlpha($path1);
$real_path2 = \EasySwoole\Utility\IntStr::toAlpha($path2);
$real_url = "{$domain_prefix}/{$real_path1}/{$real_path2}";
var_dump($real_url); // 真实的请求地址

/**
 * 输出结果:
 * string(10) "EasySwoole"
 * int(122407155078249761) 
 * string(46) "https://easyswoole.com/1793938716421/272803253"
 * string(36) "https://easyswoole.com/Preface/intro"
 */
```

