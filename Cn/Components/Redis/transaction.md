---
title: easyswoole redis事务操作方法
meta:
  - name: description
    content: easyswoole redis事务操作方法
  - name: keywords
    content: easyswoole redis事务操作方法|swoole redis事务操作方法
---
# Redis 事务
Redis 事务可以一次执行多个命令， 并且带有以下三个重要的保证：
  
 - 批量操作在发送 EXEC 命令前被放入队列缓存。
 - 收到 EXEC 命令后进入事务执行，事务中任意命令执行失败，其余的命令依然被执行。
 - 在事务执行过程，其他客户端提交的命令请求不会插入到事务执行命令序列中。
 
一个事务从开始到执行会经历以下三个阶段：
  
 - 开始事务。
 - 命令入队。
 - 执行事务。
## 操作方法

| 方法名称 | 参数           | 说明                           | 备注                         |
|:--------|:---------------|:------------------------------|:----------------------------|
| discard |                | 取消事务(回滚)                  |                             |
| exec    |                | 执行事务(获取事务结果)           |                             |
| multi   |                | 开始事务                       |  |
| unWatch |                | 取消 WATCH 命令对所有 key 的监视 |                             |
| watch   | $key, ...$keys | 监视key                        |                             |

::: warning
开始事务之后,操作命令都将返回"QUEUED",直到取消事务或者执行事务,执行exec之后,将返回所有命令结果
:::

::: warning
在集群中并不支持事务.
:::

## 基本使用
```php
go(function () {
    $redis = new \EasySwoole\Redis\Redis(new \EasySwoole\Redis\Config\RedisConfig([
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'auth'      => 'easyswoole',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
    ]));
    $data = $redis->multi();
    var_dump($data);
    $redis->del('ha');
    $data = $redis->hset('ha', 'a', 1);
    var_dump($data);
    $data = $redis->hset('ha', 'b', '2');
    var_dump($data);
    $data = $redis->hset('ha', 'c', '3');
    var_dump($data);
    $data = $redis->hGetAll('ha');
    var_dump($data);
    $data = $redis->exec();
    var_dump($data);

    $redis->multi();
    $data = $redis->discard();
    var_dump($data);
    $data = $redis->watch('a', 'b', 'c');
    var_dump($data);
    $data = $redis->unwatch();
    var_dump($data);

});
```
