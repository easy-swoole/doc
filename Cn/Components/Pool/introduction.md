---
title: EasySwoole通用连接池
meta:
  - name: description
    content: EasySwoole协程连接池
  - name: keywords
    content: easyswoole协程连接池|swoole连接池
---
# 通用连接池

`EasySwoole` 实现的通用的协程连接池管理。

## 组件要求

- php: >=7.1.0
- ext-json: *
- easyswoole/component: ^2.2.1
- easyswoole/spl: ^1.3
- easyswoole/utility: ^1.1

## 安装方法

> composer require easyswoole/pool

## 仓库地址

[easyswoole/pool](https://github.com/easy-swoole/pool)

## 池配置

在实例化一个连接池对象时，需要传入一个连接池配置对象 `EasySwoole\Pool\Config`，该对象的属性如下:

| 配置项             | 默认值  | 说明                    | 备注                                                                                  |
|:-------------------|:--------|:------------------------|:--------------------------------------------------------------------------------------|
| $intervalCheckTime | 15 * 1000 | 定时器执行频率（毫秒），默认值为 `15` s          | 用于定时执行连接池对象回收，创建操作                                                       |
| $maxIdleTime       | 10      | 连接池对象最大闲置时间（秒） | 超过这个时间未使用的对象将会被定时器回收                                                  |
| $maxObjectNum      | 20      | 连接池最大数量           | 每个进程最多会创建 `$maxObjectNum` 个连接池对象，如果对象都在使用，则会返回空，或者等待连接空闲       |
| $minObjectNum      | 5       | 连接池最小数量（热启动）   | 当连接池对象总数低于 `$minObjectNum` 时，会自动创建连接，保持连接的活跃性，让控制器能够尽快地获取连接 |
| $getObjectTimeout  | 3.0     | 获取连接池中连接对象的超时时间     | 当连接池为空时，会等待 `$getObjectTimeout` 秒，如果期间有连接空闲，则会返回连接对象，否则返回 `null`    |
| $extraConf         |         | 额外配置信息             | 在实例化连接池前，可以把一些额外配置放到这里，例如数据库配置信息、`redis` 配置等等                   |
| $loadAverageTime   | 0.001   | 负载阈值                | 并发来临时，连接池内对象达到 `maxObjectNum`，此时并未达到 `intervalCheckTime` 周期检测，因此设定了一个 `5s` 负载检测，当 `5s` 内，取出总时间/取出连接总次数，会得到一个平均取出时间，如果小于此阈值，说明此次并发峰值非持续性，将回收 `5%` 的连接                   |

## 池管理器

池管理器可以做全局的连接池管理，例如在 `EasySwooleEvent.php` 中的 `initialize` 事件中注册，然后可以在控制器中获取连接池然后进行获取连接：

下面以使用实现 `easyswoole/redis` 组件实现 `Redis` 连接池为例：

前提：先使用 `composer` 安装 `easyswoole/redis` 组件：

```bash
composer require easyswoole/redis
```

### 定义 RedisPool 管理器

#### 基于 AbstractPool 实现：

新增文件 `\App\Pool\RedisPool.php`，内容如下：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\Pool;

use EasySwoole\Pool\AbstractPool;
use EasySwoole\Pool\Config;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;

class RedisPool extends AbstractPool
{
    protected $redisConfig;

    /**
     * 重写构造函数，为了传入 redis 配置
     * RedisPool constructor.
     * @param Config      $conf
     * @param RedisConfig $redisConfig
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public function __construct(Config $conf, RedisConfig $redisConfig)
    {
        parent::__construct($conf);
        $this->redisConfig = $redisConfig;
    }

    protected function createObject()
    {
        // 根据传入的 redis 配置进行 new 一个 redis 连接
        $redis = new Redis($this->redisConfig);
        return $redis;
    }
}
```

#### 或者基于 MagicPool 实现：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\Pool;

use EasySwoole\Pool\Config;
use EasySwoole\Pool\MagicPool;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;

class RedisPool1 extends MagicPool
{
    /**
     * 重写构造函数，为了传入 redis 配置
     * RedisPool constructor.
     * @param Config $config 连接池配置
     * @param RedisConfig $redisConfig
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public function __construct(Config $config, RedisConfig $redisConfig)
    {
        parent::__construct(function () use ($redisConfig) {
            $redis = new Redis($redisConfig);
            return $redis;
        }, $config);
    }
}
```

::: tips
 不管是基于 `AbstractPool` 实现还是基于 `MagicPool` 实现效果是一致的。
:::

### 注册连接池管理对象

在 `EasySwooleEvent.php` 中的 `initialize`/`mainServerCreate` 事件中注册，然后可以在控制器中获取连接池然后进行获取连接：

```php
<?php

public static function initialize()
{
    // TODO: Implement initialize() method.
    date_default_timezone_set('Asia/Shanghai');

    $config = new \EasySwoole\Pool\Config();

    $redisConfig1 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS1'));
    $redisConfig2 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS2'));
    
    // 注册连接池管理对象
    \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config,$redisConfig1), 'redis1');
    \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config,$redisConfig2), 'redis2');
}
```

在控制器中获取连接池中连接对象，进行调用：

```php
<?php

public function index()
{
    // 取出连接池管理对象，然后获取连接对象（getObject）
    $redis1 = \EasySwoole\Pool\Manager::getInstance()->get('redis1')->getObj();
    $redis2 = \EasySwoole\Pool\Manager::getInstance()->get('redis2')->getObj();

    $redis1->set('name', '仙士可');
    var_dump($redis1->get('name'));

    $redis2->set('name', '仙士可2号');
    var_dump($redis2->get('name'));

    // 回收连接对象（将连接对象重新归还到连接池，方便后续使用）
        \EasySwoole\Pool\Manager::getInstance()->get('redis1')->recycleObj($redis1);
        \EasySwoole\Pool\Manager::getInstance()->get('redis2')->recycleObj($redis2);
        
    // 释放连接对象（将连接对象直接彻底释放，后续不再使用）
    // \EasySwoole\Pool\Manager::getInstance()->get('redis1')->unsetObj($redis1);
    // \EasySwoole\Pool\Manager::getInstance()->get('redis2')->unsetObj($redis2);
}
```

## 池对象方法

| 方法名称      | 参数                                     | 说明                                                        | 备注                                         |
|:--------------|:-----------------------------------------|:-----------------------------------------------------------|:--------------------------------------------|
| createObject  |                                          | 抽象方法,创建连接对象                                        |                                             |
| recycleObj    | $obj                                     | 回收一个连接                                                |                                             |
| getObj        | float $timeout = null, int $tryTimes = 3 | 在指定的超时时间 `$timeout` （秒）内获取一个连接，会重复尝试获取 `$tryTimes` 次直到获取到，获取失败则返回 `null`             |                                             |
| unsetObj      | $obj                                     | 直接释放一个连接                                             |                                             |
| idleCheck     | int $idleTime                            | 回收超过 `$idleTime` 未出队使用的连接                             |                                             |
| itemIntervalCheck | ObjectInterface $item                            | 判断当前客户端是否还可用                             |                                             |
| intervalCheck |                                          | 回收连接，以及热启动方法，允许外部调用热启动                      |                                             |
| keepMin       | ?int $num = null                         | 保持最小连接（热启动）                                         |                                             |
| getConfig     |                                          | 获取连接池的配置信息                                         |                                             |
| status        |                                          | 获取连接池状态信息                                           | 获取当前连接池已创建、已使用、最大创建、最小创建数据 |
| isPoolObject  | $obj                                     | 查看 `$obj` 对象是否由该连接池创建                                |                                             |
| isInPool      | $obj                                     | 获取当前连接是否在连接池内未使用                               |                                             |
| destroy       |                                          | 销毁该连接池                                                |                                             |
| reset         |                                          | 重置该连接池                                                |                                             |
| invoke        | callable $call,float $timeout = null     | 获取一个连接，传入到 `$call` 回调函数中进行处理，回调结束后自动回收连接 |                                             |
| defer         | float $timeout = null                    | 获取一个连接，协程结束后自动回收                               |                                             |


### getObj

获取一个连接池的对象：

```php
<?php

go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redis = $redisPool->getObj();
    var_dump($redis->echo('仙士可'));
    $redisPool->recycleObj($redis);
});
```
::: warning
 通过 `getObj` 方法获取的对象，都必须调用 `recycleObj` 或者 `unsetObj` 方法进行回收，否则连接池对象会越来越少。
:::

### unsetObj

直接释放一个连接池的连接对象，其他协程不能再获取到这个连接对象，而是会重新创建一个连接对象

::: warning
 释放之后，并不会立即销毁该对象，而是会在作用域结束之后销毁
:::

### recycleObj

回收一个连接对象，回收之后，其他协程可以正常获取这个连接对象。

::: warning
 回收之后，其他协程可以正常获取这个连接，但在此时，该连接还处于当前协程中，如果再次调用该连接进行数据操作，将会造成协程混乱，所以需要开发人员自行约束，当对这个连接对象进行 `recycleObj` 操作后不能再操作这个对象
:::

### invoke

获取一个连接，传入到 `$call` 回调函数中进行处理，回调结束后自动回收连接：

```php
<?php

go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redisPool->invoke(function (\EasySwoole\Redis\Redis $redis) {
        var_dump($redis->echo('仙士可'));
    });
});
```
::: warning
 通过该方法无需手动回收连接，在回调函数结束后，则自动回收
:::

### defer

获取一个连接，协程结束后自动回收

```php
<?php

go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    $redis = $redisPool->defer();
    var_dump($redis->echo('仙士可'));
});
```
::: warning
 通过该方法无需手动回收连接，在协程结束后，则自动回收
:::

::: warning
 需要注意的事，`defer` 方法是协程结束后才回收，如果你当前协程运行时间过长，则会一直无法回收，直到协程结束
:::

### keepMin

保持最小连接(热启动)。

由于 `easyswoole/pool` 当刚启动服务，出现过大的并发时，可能会突然需要几十个甚至上百个连接，这时为了让创建连接的时间分散，可以通过调用 `keepMin` 方法进行预热启动连接。

调用此方法后，将会预先创建 `N` 个连接，用于服务启动之后的控制器直接获取连接：

预热使用示例如下：

在 `EasySwooleEvent.php` 中的 `mainServerCreate` 中，当 `Worker` 进程启动后，热启动连接：

```php
<?php

public static function mainServerCreate(EventRegister $register)
{
    $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId) {
        if ($server->taskworker == false) {
            //每个worker进程都预创建连接
            \EasySwoole\Pool\Manager::getInstance()->get('redis')->keepMin(10);
            var_dump(\EasySwoole\Pool\Manager::getInstance()->get('redis')->status());
        }
    });
}
```

将会输出:

```
array(4) {
  ["created"]=>
  int(10)
  ["inuse"]=>
  int(0)
  ["max"]=>
  int(20)
  ["min"]=>
  int(5)
}
```

::: warning
 `keepMin` 是根据不同进程，创建不同的连接的，比如你有 `10` 个 `Worker` 进程，将会输出 `10` 次，总共创建 `10 * 10 = 100` 个连接
:::

### getConfig

获取连接池的配置：

```php
<?php

$redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
var_dump($redisPool->getConfig());    
```

### destroy

销毁连接池。
 
调用之后，连接池剩余的所有链接都会被执行 `unsetObj`，并且将关闭连接队列，调用之后 `getObj` 等方法都将失效：

```php
<?php

go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->getObj());
    $redisPool->destroy();
    var_dump($redisPool->getObj());
});
```

### reset

重置连接池。

调用 `reset` 之后，会自动调用 `destroy` 销毁连接池，并在下一次 `getObj` 时重新初始化该连接池：

```php
<?php

go(function (){
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->getObj());
    $redisPool->reset();
    var_dump($redisPool->getObj());
});
```

### status

获取连接池当前状态，调用之后将输出：

```php
<?php

go(function () {
    $redisPool = new \App\Pool\RedisPool(new \EasySwoole\Pool\Config(), new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS')));
    var_dump($redisPool->status());
});
```

```
array(4) {
  ["created"]=>
  int(10)
  ["inuse"]=>
  int(0)
  ["max"]=>
  int(20)
  ["min"]=>
  int(5)
}
```

### idleCheck

回收空闲超时的连接

### intervalCheck

调用此方法后，将调用 `idleCheck` 和 `keepMin` 方法，用于手动回收空闲连接和手动热启动连接

```php
<?php

public function intervalCheck()
{
    $this->idleCheck($this->getConfig()->getMaxIdleTime());
    $this->keepMin($this->getConfig()->getMinObjectNum());
}
```

### itemIntervalCheck

在内部定时器丢弃超时客户端（闲置了超过指定时间，就先断开）时，会触发 `itemIntervalCheck` 函数，并将客户端传入，用户通过这个函数可以实现判断客户端是否可用的逻辑。

该函数如果返回 `true` 代表可用（默认情况），返回`false` 将会导致该客户端直接被丢弃。

可用于：维持客户端心跳等。如 `orm` 中对其使用场景如下：维持 `mysql` 连接，减少 `mysql` 掉线 `gone away` 的几率

```php
<?php
/**
 * @param MysqliClient $item
 * @return bool
 */
public function itemIntervalCheck($item): bool
{
    /*
     * 如果最后一次使用时间超过 autoPing 间隔
     */
    /** @var Config $config */
    $config = $this->getConfig();
    if ($config->getAutoPing() > 0 && (time() - $item->__lastUseTime > $config->getAutoPing())) {
        try {
            // 执行一个sql触发活跃信息
            $item->rawQuery('select 1');
            // 标记使用时间，避免被再次 gc
            $item->__lastUseTime = time();
            return true;
        } catch (\Throwable $throwable) {
            // 异常说明该链接出错了，return 进行回收
            return false;
        }
    } else {
        return true;
    }
}
```

## 基本使用

### 定义池对象

```php
<?php

class Std implements \EasySwoole\Pool\ObjectInterface
{
    function gc()
    {
        /*
         * 本对象被 pool 执行 unset 的时候
         */
    }

    function objectRestore()
    {
        /*
         * 回归到连接池的时候
         */
    }

    function beforeUse(): ?bool
    {
        /*
         * 取出连接池的时候，若返回false，则当前对象被弃用回收
         */
        return true;
    }

    public function who()
    {
        return spl_object_id($this);
    }
}
```
### 定义池
```php
<?php

class StdPool extends \EasySwoole\Pool\AbstractPool
{
    protected function createObject()
    {
        return new Std();
    }
}
```

> 不一定非要在创建对象方法 `createObject()` 中返回 `EasySwoole\Pool\ObjectInterface` 对象，任意类型对象均可

在 `pool` 组件版本 `>= 1.0.2 ` 后，提供了 `魔术池` 支持，可以快速进行定义池。

```php
<?php

use \EasySwoole\Pool\MagicPool;

$magic = new MagicPool(function () {
    return new \stdClass(); // 示例，可以返回实现了 ObjectInterface 的对象
});

// 注册后获取
$test = $magic->getObj();
// 归还
$magic->recycleObj($test);
```

魔术池构造方法的第二个参数，可以接收一个 `config`（`EasySwoole\Pool\Config` 类），用于定义池数量等配置。


### 简单示例

```php
<?php

$config = new \EasySwoole\Pool\Config();
$pool = new StdPool($config);

go(function () use ($pool) {
    $obj = $pool->getObj();
    $obj2 = $pool->getObj();
    var_dump($obj->who());
    var_dump($obj2->who());
});
```

## 进阶使用

[基于 `pool` 实现的 `Redis` 连接池](../Redis/pool.html)

[基于 `pool` 实现的 `MySql` 连接池]()

## 相关仓库

[easyswoole/redis-pool](https://github.com/easy-swoole/redis-pool)







