---
title: easyswoole redis连接池:集群迁移教程
meta:
  - name: description
    content: easyswoole redis连接池
  - name: keywords
    content: easyswoole redis连接池|swoole redis连接池|php redis连接池|swoole redis集群|easyswoole redis集群
---

## 场景

在业务量小的情况下，我们使用Redis单机连接池就可以满足业务需求。因此，redis单机连接池就可以满足我们的业务。因此我们会这样写：

### 示例

#### 注册连接池
```
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\Redis;
Redis::getInstance()->register('redis',new RedisConfig());
```

#### 使用
```
use EasySwoole\RedisPool\Redis;
use EasySwoole\Redis\Redis as RedisClient
Redis::invoke('redis', function (RedisClient $redis) {
    var_dump($redis->set('a', 1));
});
```

当业务量上来后，我们需要切换成集群模式的时候怎么办。因此我们做的应该是：
#### 注册集群连接池
```
use EasySwoole\RedisPool\Redis;
use EasySwoole\Redis\Config\RedisClusterConfig;
Redis::getInstance()->register('redis',new RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ]
));
```
#### 老业务代码
```
use EasySwoole\RedisPool\Redis;
use EasySwoole\Redis\Redis as RedisClient
Redis::invoke('redis', function (RedisClient $redis) {
    var_dump($redis->set('a', 1));
});
```

对于之前的老业务代码，就会出现、、、、此事，我的invoker回调参数是一个```EasySwoole\Redis\RedisCluster```实例，而非```EasySwoole\Redis\Redis```,
因此就会导致业务代码报错。

## 解决方案
Easyswoole Redis Pool组件，在2.1.1版本开始，基于easyswoole的magic pool 引入一个cask机制。可以指定实例化的class。

### 定义一个class
```php
namespace App\Utility;

use EasySwoole\Redis\Redis;

class RedisClient extends Redis
{
    function fuck()
    {
        var_dump('waf');
    }
}
```

> 该class继承自```EasySwoole\Redis\Redis```

### 注册redis
```
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\Redis;
use App\Utility\RedisClient
Redis::getInstance()->register('redis',$config,RedisClient::class);
```
### 使用redis
```
use EasySwoole\RedisPool\Redis;
use App\Utility\RedisClient
Redis::invoke('redis', function (RedisClient $redis) {
    var_dump($redis->set('a', 1));
});
```

### 迁移集群

#### 修改注册配置
```
use EasySwoole\RedisPool\Redis;
use EasySwoole\Redis\Config\RedisClusterConfig;
Redis::getInstance()->register('redis',new RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ]
),RedisClient::class);
```

#### 继承修改
修改```App\Utility\RedisClient```，让它继承```EasySwoole\Redis\RedisCluster```即可

而由于redis单机客户端与集群客户端的方法几乎一致，因此可以不修改任何业务代码，就是实现单机到集群的迁移