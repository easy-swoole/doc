---
title: easyswoole redis自定义命令
meta:
  - name: description
    content: easyswoole redis自定义命令
  - name: keywords
    content: easyswoole redis自定义命令|swoole redis自定义命令|php redis自定义命令
---

# 自定义命令
redis客户端提供了`rawCommand`方法以供使用自定义命令:  

## 基本使用

```php
$data = $redis->rawCommand(['set','a','1']);
var_dump($data);
$data = $redis->rawCommand(['get','a']);
var_dump($data);
$redis->del('a');
```
rawCommand将返回一个`EasySwoole\Redis\Response`对象

```php
object(EasySwoole\Redis\Response)#8 (4) {
  ["status":protected]=>
  int(0)
  ["data":protected]=>
  string(2) "OK"
  ["msg":protected]=>
  NULL
  ["errorType":protected]=>
  NULL
}
object(EasySwoole\Redis\Response)#9 (4) {
  ["status":protected]=>
  int(0)
  ["data":protected]=>
  string(1) "1"
  ["msg":protected]=>
  NULL
  ["errorType":protected]=>
  NULL
}
```
