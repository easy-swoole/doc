---
title: EasySwoole简单Redis连接池
meta:
  - name: description
    content: EasySwoole协程连接池
  - name: keywords
    content: easyswoole协程连接池|swoole连接池|easyswoole简单Redis连接池
---

# 简单 Redis 连接池示例

## 安装 easyswoole/redis 组件：

```bash
composer require easyswoole/redis
```

## 定义 RedisPool 管理器：

### 基于 AbstractPool 实现：

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

### 或者基于 MagicPool 实现：

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

## 注册连接池管理对象

在 `EasySwooleEvent.php` 中的 `initialize`/`mainServerCreate` 事件中注册，然后可以在控制器中获取连接池然后进行获取连接：

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

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        $config = new \EasySwoole\Pool\Config();

        $redisConfig1 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS1'));
        $redisConfig2 = new \EasySwoole\Redis\Config\RedisConfig(Config::getInstance()->getConf('REDIS2'));

        // 注册连接池管理对象
        \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config, $redisConfig1), 'redis1');
        \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config, $redisConfig2), 'redis2');
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

## 调用

在控制器中获取连接池中连接对象，进行调用：

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

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
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
}
```