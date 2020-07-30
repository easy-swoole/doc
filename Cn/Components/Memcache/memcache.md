---
title: easyswoole memcache协程客户端
meta:
  - name: description
    content: easyswoole memcache协程客户端
  - name: keywords
    content:  easyswoole memcache协程客户端|swoole memcache协程客户端
---
# memcache协程客户端

memcache协程客户端,由swoole 协程client实现   
 
## 组件要求

- easyswoole/spl: ^1.1 
 
## 安装方法   

> composer require easyswoole/memcache

## 仓库地址

[easyswoole/memcache](https://github.com/easy-swoole/memcache)




## 客户端调用  
```php
$config = new \EasySwoole\Memcache\Config([
    'host' => '127.0.0.1',
    'port' => 11211
]);
$client = new EasySwoole\Memcache\Memcache($config);
```

### 使用示例:  
```php
$config = new \EasySwoole\Memcache\Config([
    'host' => '127.0.0.1',
    'port' => 11211
]);
$client = new EasySwoole\Memcache\Memcache($config);
$client->set('a',1);
$client->get('a');
```

## 使用方法:  
 
### touch摸一下(刷新有效期)  

```php
touch($key, $expiration, $timeout = null)
```

### increment自增KEY  

```php
increment($key, $offset = 1, $initialValue = 0, $expiration = 0, $timeout = null)
```


### decrement自减KEY  
```php
decrement($key, $offset = 1, $initialValue = 0, $expiration = 0, $timeout = null)
```

### set设置KEY(覆盖)  

```php
set($key, $value, $expiration = 0, $timeout = null)
```

### add增加KEY(非覆盖)  
```php
add($key, $value, $expiration = 0, $timeout = null)
```
### replace替换一个KEY  
```php
replace($key, $value, $expiration = 0, $timeout = null)
```
### append追加数据到末尾  
```php
append($key, $value, $timeout = null)
```
### prepend追加数据到开头  
```php
prepend($key, $value, $timeout = null)
```
### get获取KEY  
```php
get($key, $timeout = null)
```
### delete删除一个key  
```php
delete($key, $timeout = null)
```
### stats获取服务器状态 
```php 
stats($type = null, $timeout = null)
```
### version获取服务器版本  
```php
version(int $timeout = null)
```
### flush  清空缓存 
```php 
flush(int $expiration = null, int $timeout = null)
```

## 进阶使用

Memcache连接池示例

### 安装 easyswoole/pool 组件

组件要求

- php: >=7.1.0
- ext-json: *
- easyswoole/component: ^2.2.1
- easyswoole/spl: ^1.3
- easyswoole/utility: ^1.1

安装方法

> composer require easyswoole/pool
  
仓库地址

[easyswoole/pool](https://github.com/easy-swoole/pool)

### 新增MemcachePool管理器
新增文件`/App/Pool/MemcachePool.php`

```php
<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/10/15 0015
 * Time: 14:46
 */

namespace App\Pool;

use EasySwoole\Memcache\Memcache;
use EasySwoole\Pool\Config;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Memcache\Config as MemcacheConfig;

class MemcachePool extends AbstractPool
{
    protected $memcacheConfig;

    /**
     * 重写构造函数,为了传入memcache配置
     * RedisPool constructor.
     * @param Config      $conf
     * @param MemcacheConfig $memcacheConfig
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public function __construct(Config $conf,MemcacheConfig $memcacheConfig)
    {
        parent::__construct($conf);
        $this->memcacheConfig = $memcacheConfig;
    }

    protected function createObject():Memcache
    {
        //根据传入的memcache配置进行new 一个memcache客户端
        $memcache = new Memcache($this->memcacheConfig);
        return $memcache;
    }
}
```
注册到Manager中(在`initialize`事件中注册):
```php

$config = new \EasySwoole\Pool\Config();

$memcacheConfig1 = new \EasySwoole\Memcache\Config(Config::getInstance()->getConf('MEMCACHE1'));
\EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\MemcachePool($config,$memcacheConfig1),'memcache1');

$memcacheConfig2 = new \EasySwoole\Memcache\Config(Config::getInstance()->getConf('MEMCACHE2'));
\EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\MemcachePool($config,$memcacheConfig2),'memcache2');
    
```

调用(可在控制器中全局调用):
```php
go(function (){
    $memcachePool1 = Manager::getInstance()->get('memcache1');
    $memcachePool2 = Manager::getInstance()->get('memcache2');
    $memcache1 = $memcachePool1->getObj();
    $memcache2 = $memcachePool2->getObj();
    
    var_dump($memcache1->set('name', '仙士可1'));
    $this->response()->write($memcache1->get('name'));
    var_dump($memcache2->set('name', '仙士可2'));
    $this->response()->write($memcache2->get('name'));
    
    //回收对象
    $memcachePool1->recycleObj($memcache1);
    $memcachePool2->recycleObj($memcache2);
});
```

::: warning
详细用法可查看 [pool通用连接池](../Pool/introduction.md)
:::

::: warning
本文 memcache连接池 基于 [pool通用连接池](../Pool/introduction.md) 实现
:::




