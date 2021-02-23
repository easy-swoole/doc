---
title: easyswoole swoole-协程连接池
meta:
  - name: description
    content: easyswoole swoole-协程连接池
  - name: keywords
    content: easyswoole swoole-协程连接池|easyswoole|swoole|coroutine|waitGroup
---

# 连接池

## ConnectionPool

原始连接池，基于 `Channel` 自动调度，支持传入任意构造器 (`callable`)，构造器需返回一个连接对象

- `get` 获取连接（连接池未满时会自动创建新的连接）
- `put` 回收连接
- `fill` 填充连接池（提前创建好连接）
- `close` 关闭连接池

## Database

数据库连接池和对象代理的封装，自动断线重连。目前包含 PDO，Mysqli，Redis 三种类型：

- `PDOConfig`、`PDOProxy`、`PDOPool`
- `MysqliConfig`、`MysqliProxy`、`MysqliPool`
- `RedisConfig`、`RedisProxy`、`RedisPool`

> mysql 断线重连可自动恢复大部分连接上下文，但事务等上下文无法恢复。  
> 将处于事务中的连接还给连接池是未定义行为，开发者需保证归还的连接是可重用的；    
> 出现连接对象出现异常不可重用，开发者需要调用 $pool->put(null); 归还一个空连接来保证连接池的数量平衡。

### PDO

```php
<?php

$start = microtime(true);

$scheduler = new \Swoole\Coroutine\Scheduler();

$scheduler->add(function () {
    $pool = new \Swoole\Database\PDOPool((new \Swoole\Database\PDOConfig())
        ->withHost('127.0.0.1')
        ->withPort(3306)
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('gaobinzhan')
    );
    for ($n = 1024; $n--;) {
        \Swoole\Coroutine::create(function () use ($pool) {
            $pdo = $pool->get();
            $statement = $pdo->prepare('SHOW TABLES');

            if (!$statement) {
                throw new RuntimeException('Prepare failed');
            }

            $result = $statement->execute();
            if (!$result) {
                throw new RuntimeException('Execute failed');
            }

            $statement->fetchAll();
            $pool->put($pdo);
        });
    }
});
$scheduler->start();

$start = microtime(true) - $start;
echo 'Use ' . $start . 's for ' . 1024 . ' queries' . PHP_EOL;

```


### Mysqli

```php
<?php

$start = microtime(true);

$scheduler = new \Swoole\Coroutine\Scheduler();

$scheduler->add(function () {
    $pool = new \Swoole\Database\MysqliPool((new \Swoole\Database\MysqliConfig())
        ->withHost('127.0.0.1')
        ->withPort(3306)
        ->withDbName('test')
        ->withCharset('utf8mb4')
        ->withUsername('root')
        ->withPassword('gaobinzhan')
    );
    for ($n = 1024; $n--;) {
        \Swoole\Coroutine::create(function () use ($pool) {
            $mysqli = $pool->get();
            $query = $mysqli->query('SHOW TABLES');
            $query->fetch_all();
            $pool->put($mysqli);
        });
    }
});
$scheduler->start();

$start = microtime(true) - $start;
echo 'Use ' . $start . 's for ' . 1024 . ' queries' . PHP_EOL;

```

### Redis

```php
<?php

$start = microtime(true);

$scheduler = new \Swoole\Coroutine\Scheduler();

$scheduler->add(function () {
    $pool = new \Swoole\Database\RedisPool((new \Swoole\Database\RedisConfig())
        ->withHost('127.0.0.1')
        ->withPort(6379)
        ->withDbIndex(0)
    );
    for ($n = 1024; $n--;) {
        \Swoole\Coroutine::create(function () use ($pool) {
            $redis = $pool->get();
            $result = $redis->set('key', 'easyswoole');
            if (!$result) {
                throw new RuntimeException('Set failed');
            }
            $result = $redis->get('key');
            if ($result !== 'easyswoole') {
                throw new RuntimeException('Get failed');
            }
            $pool->put($redis);
        });
    }
});
$scheduler->start();

$start = microtime(true) - $start;
echo 'Use ' . $start . 's for ' . 2048 . ' queries' . PHP_EOL;

```