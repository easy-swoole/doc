---
title: xlsWrite
meta:
  - name: description
    content: xlsWriter-excel
  - name: keywords
    content: easyswoole xlsWriter | easyswoole excel analysis
---

# xlsWriter-excel

![logo](https://github.com/viest/php-ext-xlswriter/raw/master/resource/logo_now.png)  


## Why use xlswriter

Please refer to the comparison chart below. Due to memory reasons, phpexcel can't work normally when the amount of data is relatively large, although you can modify the memory_ Limit 'is configured to solve the memory problem, but it may take longer to complete the work;

![php-excel](https://github.com/viest/php-ext-xlswriter/raw/master/resource/performance_comparison.png)

Xlswriter is a PHP C extension, which can be used to read data in Excel 2007 + xlsx files, insert multiple worksheets, and write text, numbers, formulas, dates, charts, pictures, and hyperlinks.

It has the following characteristics：

###### 一、Write

* 100% compatible excel xlsx file
* Complete excel format
* Merge cells
* Define sheet name
* Filter
* Chart
* Data validation and drop down list
* Worksheet PNG / jpeg image
* Memory optimization mode for writing large files
* For Linux, FreeBSD, OpenBSD, OS X, windows
* Compile to 32-bit and 64 bit
* FreeBSD license
* The only dependency is zlib

###### 二、 Read

* Read data completely
* The cursor reads the data
* Read by data type

## Start here

[Documents](https://xlswriter-docs.viest.me/)

## PECL warehouse

[![pecl](https://github.com/viest/php-ext-xlswriter/raw/master/resource/pecl.png)](https://pecl.php.net/package/xlswriter)

## IDE Helper

```bash
composer require viest/php-ext-xlswriter-ide-helper:dev-master
```


#### Testing environment

Testing environment: Macbook Pro 13 inch, Intel Core i5, 16GB 2133MHz LPDDR3 Memory, 128GB SSD Storage.

##### Export 

> Two memory modes export 1 million rows of data (27 columns in a single row, data type is string, single string length is 19)

* Normal mode: time consuming '29s', memory only' 2083mb ';
* Fixed memory mode: only need '52s', memory only need' < 1MB ';

##### Import 

> One million rows of data (single row and one column, data type is int)

* Full mode: time consuming '3S', memory only' 558mb ';
* Cursor mode: time consuming is 2.8s, memory is less than 1MB;
