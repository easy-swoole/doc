---
title: easyswoole redis自定义命令
meta:
  - name: description
    content: easyswoole redis自定义命令
  - name: keywords
    content: easyswoole redis自定义命令|swoole redis自定义命令|php redis自定义命令
---

# 自定义命令
`Redis` 客户端提供了 `rawCommand` 方法以供使用自定义命令，可以实现 `eval` 等脚本命令执行的支持

## 脚本命令

可用于执行 `Redis` 脚本等。

**1. EVAL 执行 `Lua` 脚本**

调用形式：

```php
$res = $redis->rawCommand([
    'EVAL', 
    "lua script", # lua 脚本
    'keyNums',    # 指定脚本中键名参数的个数
    // 在脚本中所用到的那些 Redis 键(key) 值
    'key1',       # 第 1 个 key 对应的值
    'key2',       # 第 2 个 key 对应的值
    'key3',       # 第 3 个 key 对应的值
    ...           # 多个 key 依次添加即可
    // 在 Lua 中通过全局变量 ARGV 数组访问，
    'arg1',       # 第 1 个附加 arg 参数对应的值
    'arg2',       # 第 2 个附加 arg 参数对应的值
    'arg3',       # 第 3 个附加 arg 参数对应的值
    ...           # 多个 arg 参数依次添加即可
]);

// 获取执行 `Lua` 脚本的调用结果
var_dump($res->getData());
```

使用示例：

```php
$res = $redis->rawCommand([
    'EVAL', 
    "return {KEYS[1],KEYS[2],ARGV[1],ARGV[2]}",
    '2',
    'key1',
    'key2',
    'first',
    'second'
]);

// 获取执行 `Lua` 脚本的调用结果
var_dump($res->getData());
```

**2. EVALSHA 执行 `Lua` 脚本**

```php
$res = $redis->rawCommand([
    'SCRIPT',
    'LOAD',
    "return 'hello moto'" # lua 脚本
]);

$sha1 = $res->getData(); # SHA1 校验和
var_dump($sha1);

$res = $redis->rawCommand([
    'EVALSHA',
    $sha1,
    '0'
]);
var_dump($res->getData());
```

**3. SCRIPT 查看脚本是否被缓存**

```php
// 载入一个脚本
$res = $redis->rawCommand([
    'SCRIPT',
    'LOAD',
    "return 'hello moto'" # lua 脚本
]);
$sha1 = $res->getData(); # SHA1 校验和
var_dump($sha1);

// 查看脚本是否被缓存
$res = $redis->rawCommand([
    'SCRIPT',
    'EXISTS',
    $sha1,
    '0'
]);
var_dump($res->getData()[0]);

// 清空缓存
$res = $redis->rawCommand([
    'SCRIPT',
    'FLUSH'
]);
var_dump($res->getData());

// 再次查看脚本是否被缓存
$res = $redis->rawCommand([
    'SCRIPT',
    'EXISTS',
    $sha1
]);
var_dump($res->getData()[0]);
```

**4. EVAL 从缓存中移除所有脚本**

```php
// 清空缓存
$res = $redis->rawCommand([
    'SCRIPT',
    'FLUSH'
]);
var_dump($res->getData());
```

**5. EVAL 杀死当前正在运行的 `Lua` 脚本**

```php
// 杀死当前正在运行的 `Lua` 脚本
$res = $redis->rawCommand([
    'SCRIPT',
    'KILL'
]);
var_dump($res->getData());
```

**6. EVAL 将脚本 `script` 添加到脚本缓存中，但并不立即执行这个脚本。**

```php
// 将脚本 `script` 添加到脚本缓存中，但并不立即执行这个脚本
$res = $redis->rawCommand([
    'SCRIPT',
    'LOAD',
    "return 'hello moto'" # lua 脚本
]);
$sha1 = $res->getData(); # SHA1 校验和
var_dump($sha1);
```

## 基本使用

```php
$data = $redis->rawCommand(['set', 'a', '1']);
var_dump($data);

$data = $redis->rawCommand(['get', 'a']);
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
