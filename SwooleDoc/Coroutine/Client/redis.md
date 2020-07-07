---
title: easyswoole swoole-协程redis客户端
meta:
  - name: description
    content: easyswoole swoole-协程redis客户端
  - name: keywords
    content: easyswoole swoole-协程redis客户端|easyswoole|swoole|coroutine
---

# Coroutine\Redis\Client

## 简单示例代码

```php
<?php
Swoole\Coroutine::create(function () {
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->set('key', 'EasySwoole');
    var_dump($redis->get('key'));
});
```

## 方法

### __construct
作用：构造方法     
方法原型：__construct(array $options = null);

### setOptions
作用：设置客户端配置 `key-value` 键值对      
方法原型：setOptions(array $options): void;      
可配置选项：
- `connect_timeout` 连接超时时间 默认为1s
- `timeout` 超时时间 默认 -1 永不超时
- `serialize` 自动序列化 默认关闭
- `reconnect` 自动尝试连接次数 默认为1
- `compatibility_mode` `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` 函数返回结果与 `php-redis` 不一致的兼容解决方案 默认关闭

### set
作用：存数据      
方法原型：set(string $key, mixed $value, array|int $option): bool;       
参数：
- $key 数据 key
- $value 内容 非字符串会自动序列化
- $option 选项 
 - `px` 毫秒级过期时间
 - `ex` 秒极过期时间
 - `nx` 不存在设置超时
 - `xx` 存在时设置超时

### request
作用：向redis服务器发送一个自定义命令 类似 `rawCommand`       
方法原型：request(array $args): void;        
参数：
- $args 数组 第一个元素为redis命令

示例：
```php
<?php
Swoole\Coroutine::create(function () {
    $redis = new \Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    $res = $redis->request(['set', 'key', 'EasySwoole']);
    var_dump($res);
});
```

## 属性

### errCode
错误代码：
- 1 Error in read or write
- 2 Everything else...
- 3 End of file
- 4 Protocol error
- 5 Out of memory

### errMsg
错误信息

### connected
判断当前 `redis` 客户端是否连接了服务器

## 常量
用于 `multi($mode)` 方法，默认为 `SWOOLE_REDIS_MODE_MULTI`：
- SWOOLE_REDIS_MODE_MULTI
- SWOOLE_REDIS_MODE_PIPELINE

用于判断 `type()` 返回值：
- SWOOLE_REDIS_TYPE_NOT_FOUND
- SWOOLE_REDIS_TYPE_STRING
- SWOOLE_REDIS_TYPE_SET
- SWOOLE_REDIS_TYPE_LIST
- SWOOLE_REDIS_TYPE_ZSET
- SWOOLE_REDIS_TYPE_HASH

## 事务模式

可使用 `multi` 和 `exec` 实现：
- 使用 `multi` 启动事务，之后所有指令被加入到队列等待执行
- 使用 `exec` 指令执行事务中所有操作，并一次性返回所有结果

示例：
```php
<?php
Swoole\Coroutine::create(function () {
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key', 'EasySwoole');
    $redis->get('key');
    $result = $redis->exec();
    var_dump($result);
});
```

## 订阅模式

:::warning
Swoole v4.2.13+ 以下版本存在bug
:::

### 订阅
`subscribe/psubscribe`

### 退订
`unsubscribe/punsubscribe`

### 示例代码
```php
<?php
Swoole\Coroutine::create(function () {
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channelTest']))
    {
        while ($msg = $redis->recv()) {
            // msg是一个数组, 包含以下信息
            // $type 返回值类型
            // $name 频道名字
            // $info 已订阅的频道数量 或 信息内容
            list($type, $name, $info) = $msg;
            var_dump($msg);
        }
    }
});
```