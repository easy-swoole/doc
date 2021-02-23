---
title: easyswoole SyncInvoker
meta:
  - name: description
    content: easyswoole SyncInvoker
  - name: keywords
    content: easyswoole SyncInvoker|swoole SyncInvoker|swoole同步转协程
---
# SyncInvoker

## 场景

Swoole4.x后，提供了非常强大的协程能力，让我们可以更好的压榨服务器性能，提高并发。然而，目前PHP在swoole协程生态上，并不是很完善，比如没有协程版本的monogodb客户端，而为了避免在worker中调用了同步阻塞的Api，例如在Http回调中使用了同步的芒果客户端，导致worker退化为同步阻塞，导致没办法完全的发挥协程的优势，
EasySwoole 提供了一个同步程序协程调用转化驱动。

## 原理

启动自定义进程监听UnixSocket，然后worker端调用协程客户端发送命令到自定义进程并处理，然后吧处理结果返回给worker的协程客户端。

## 安装

```
composer require easyswoole/sync-invoker
```

## 使用

定义一个驱动工作实例（可以定义多个）

```php
namespace App;
use EasySwoole\SyncInvoker\AbstractInvoker;
use EasySwoole\SyncInvoker\SyncInvoker;
use EasySwoole\Component\Singleton;

class MyInvokerDriver extends AbstractInvoker{

    private $stdclass;

    function __construct()
    {
        $this->stdclass = new \stdClass();
        parent::__construct();
    }

    public function test($a,$b)
    {
        return $a+$b;
    }

    public function a()
    {
        return 'this is a';
    }

    public function getStdClass()
    {
        return $this->stdclass;
    }
}

//注册一个对应的调用器

class MyInvoker extends SyncInvoker
{
    use Singleton;
}
```

EasySwoole 全局事件中的mainServerCreate 进行注册
```
 MyInvoker::getInstance(new MyInvokerDriver())->attachServer(ServerManager::getInstance()->getSwooleServer());
```

服务启动后，即可在任意位置调用
```php
$ret = MyInvoker::getInstance()->client()->test(1,2);
var_dump($ret);
var_dump(MyInvoker::getInstance()->client()->a());
var_dump(MyInvoker::getInstance()->client()->a(1));
var_dump(MyInvoker::getInstance()->client()->fuck());
$ret = MyInvoker::getInstance()->client()->callback(function (MyInvokerDriver $driver){
    $std = $driver->getStdClass();
    if(isset($std->time)){
        return $std->time;
    }else{
        $std->time = time();
        return 'new set time';
    }
});
```

## 注意事项

- 尽量使用函数名调用方式，闭包方式调用会存在部分闭包函数序列化失败问题
- 传递参数，返回结果尽量用数组或者字符串传递，资源对象无法序列化



## MongoDb客户端案例

目前，MongoDB并没有提供协程版本的php客户端，只有同步阻塞版本。

::: tip 提示
EasySwoole 的协程版客户端已经在排期内。
:::

在实际生产中，直接 创建原生的mongoDB客户端来进行数据交互，也不是不可。

若希望将同步调用转为协程调用，可以用Easyswoole 提供的sync-invoker组件。

## 定义驱动

```php
namespace App\Mongodb;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\SyncInvoker\AbstractInvoker;
use MongoDB\Client;

class Driver extends AbstractInvoker
{
    private $db;

    function getDb():Client
    {
        if($this->db == null){
            $mongoUrl = "mongodb://127.0.0.1:27017";
            $this->db = new Client($mongoUrl);
        }
        return $this->db;
    }

    protected function onException(\Throwable $throwable)
    {
        Trigger::getInstance()->throwable($throwable);
        return null;
    }
}
```

## 客户端
```php
namespace App\Mongodb;

use EasySwoole\Component\Singleton;
use EasySwoole\SyncInvoker\SyncInvoker;

class MongoClient extends SyncInvoker
{
    use Singleton;
}
```

## 服务注册

在Easyswoole全局事件mainServerCreate中进行服务注册

```php
MongoClient::getInstance(new Driver())->attachServer(ServerManager::getInstance()->getSwooleServer());
```

## 开始使用

```php
$ret = MongoClient::getInstance()->client()->callback(function (Driver $driver){
    $ret = $driver->getDb()->user->list->insertOne([
        'name' =>Random::character(8),
        'sex'=>'man',
    ]);
    if(!$ret){
        return false;
    }
    return $ret->getInsertedId();
});

$ret = MongoClient::getInstance()->client()->callback(function (Driver $driver){
    $ret = [];
    $collections = $driver->getDb()->user->listCollections();
    foreach ($collections as $collection) {
        $ret[] = (array)$collection;
    }
    return $ret;
});
```
