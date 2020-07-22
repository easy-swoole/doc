---
title: easyswoole redis HyperLog操作方法
meta:
  - name: description
    content: easyswoole redis HyperLog操作方法
  - name: keywords
    content: easyswoole redis HyperLog操作方法|swoole redis HyperLog操作方法
---

# Redis HyperLogLog

Redis 在 2.8.9 版本添加了 HyperLogLog 结构。
  
  Redis HyperLogLog 是用来做基数统计的算法，HyperLogLog 的优点是，在输入元素的数量或者体积非常非常大时，计算基数所需的空间总是固定 的、并且是很小的。
  
  在 Redis 里面，每个 HyperLogLog 键只需要花费 12 KB 内存，就可以计算接近 2^64 个不同元素的基 数。这和计算基数时，元素越多耗费内存就越多的集合形成鲜明对比。
  
  但是，因为 HyperLogLog 只会根据输入元素来计算基数，而不会储存输入元素本身，所以 HyperLogLog 不能像集合那样，返回输入的各个元素。

## 操作方法


| 方法名称 | 参数                        | 说明                                    | 备注           |
|:--------|:----------------------------|:----------------------------------------|:---------------|
| pfAdd   | $key, $elements             | 添加指定元素到 HyperLogLog 中。           | 传入一个索引数组 |
| pfCount | $key                        | 返回给定 HyperLogLog 的基数估算值。       |                |
| pfMerge | $deStKey, array $sourceKeys | 将多个 HyperLogLog 合并为一个 HyperLogLog | 传入一个索引数组 |


## 基本使用
```php

go(function () {
    $redis = new \EasySwoole\Redis\Redis(new \EasySwoole\Redis\Config\RedisConfig([
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'auth'      => 'easyswoole',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
    ]));;


    $key = [
        'hp1',
        'hp2',
        'hp3',
        'hp4',
        'hp5',
    ];
    $redis->del($key[0]);
    $redis->del($key[1]);
    $data = $redis->pfAdd($key[0], [1, 2, 2, 3, 3]);
    var_dump($data);

    $redis->pfAdd($key[1], [1, 2, 2, 3, 3]);
    $data = $redis->pfCount([$key[0], $key[1]]);
    var_dump($data);

    $data = $redis->pfMerge($key[2], [$key[0], $key[1]]);
    var_dump($data);
});

```
