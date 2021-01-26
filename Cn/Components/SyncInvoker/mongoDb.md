---
title: easyswoole MongoDb使用教程
meta:
  - name: description
    content: easyswoole MongoDb使用教程
  - name: keywords
    content: easyswoole MongoDb使用教程|swoole MongoDb使用教程|swoole协程MongoDb
---

# MongoDB

目前，`MongoDB` 并没有提供协程版本的 `php` 客户端，只有同步阻塞版本。

::: tip 提示
EasySwoole 的协程版客户端已经在排期内。
:::

在实际生产中，直接创建原生的 `MongoDB客户端` 来进行数据交互，也不是不可。

若希望将同步调用转为协程调用，可以用 `Easyswoole` 提供的 `sync-invoker` 组件。

将 `MongoDB客户端` 的同步调用转为协程调用具体使用如下：

## 定义驱动

```php
<?php

namespace App\MongoDb;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\SyncInvoker\AbstractDriver;
use MongoDB\Client;

class Driver extends AbstractDriver
{
    private $db;

    // 【建议使用】
    // 使用 mongodb/mongodb composer组件包封装的 MongoDB 客户端调用类，作为客户端调用驱动
    // 【前提：需要先使用 `composer require mongodb/mongodb` 安装 mongodb/mongodb composer组件包】
    function getDb(): Client
    {
        if ($this->db == null) {
            // 这里为要连接的 mongodb 的服务端地址【前提是必须先有服务端，且安装 php-mongodb 扩展才可使用】
            $mongoUrl = "mongodb://127.0.0.1:27017";
            $this->db = new Client($mongoUrl);
        }
        return $this->db;
    }
    
    // 仅使用 php-mongodb 扩展内置类(不使用composer组件包的)，作为客户端调用驱动
    /*
    function getDb(): \MongoDB\Driver\Manager
    {
        if ($this->db == null) {
            // 这里为要连接的 mongodb 的服务端地址【前提是必须先有服务端，且安装 php-mongodb 扩展才可使用】
            $mongoUrl = "mongodb://127.0.0.1:27017";
            $this->db = new \MongoDB\Driver\Manager($mongoUrl);

        }
        return $this->db;
    }
    */

    protected function onException(\Throwable $throwable)
    {
        Trigger::getInstance()->throwable($throwable);
        return null;
    }
}
```

## 客户端调用类定义

```php
<?php

namespace App\MongoDb;

use EasySwoole\Component\Singleton;
use EasySwoole\SyncInvoker\SyncInvoker;

class MongoClient extends SyncInvoker
{
    use Singleton;
}
```

## 注册 Invoker 服务

在 `EasySwoole 全局事件` 的 `mainServerCreate 事件` 中进行服务注册

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 配置 Invoker
        $invokerConfig = \App\MongoDb\MongoClient::getInstance()->getConfig();
        $invokerConfig->setDriver(new \App\MongoDb\Driver()); // 配置 MongoDB 客户端协程调用驱动

        // 以下这些配置都是可选的，可以使用组件默认的配置
        /*
        $invokerConfig->setMaxPackageSize(2 * 1024 * 1024); // 设置最大允许发送数据大小，默认为 2M【注意：当使用 MongoDB 客户端查询大于 2M 的数据时，可以修改此参数】
        $invokerConfig->setTimeout(3.0); // 设置 MongoDB 客户端操作超时时间，默认为 3.0 秒;
        */

        // 注册 Invoker
        \App\MongoDb\MongoClient::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

## 在框架中使用 MongoDB 客户端(协程调用)

```php
<?php

namespace App\HttpController;

use App\MongoDb\Driver;
use App\MongoDb\MongoClient;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Utility\Random;

class Index extends Controller
{
    public function index()
    {
        // 使用 mongodb/mongodb composer组件包【建议使用，需要先使用composer安装】
        $ret = MongoClient::getInstance()->invoke()->callback(function (Driver $driver) {
            $ret = $driver->getDb()->user->list->insertOne([
                'name' => Random::character(8),
                'sex' => 'man',
            ]);
            if (!$ret) {
                $driver->response(false);
            }
            $driver->response($ret->getInsertedId());
        });
        var_dump($ret);

        $ret = MongoClient::getInstance()->invoke()->callback(function (Driver $driver) {
            $ret = [];
            $collections = $driver->getDb()->user->listCollections();
            foreach ($collections as $collection) {
                $ret[] = (array)$collection;
            }
            $driver->response($ret);
        });
        var_dump($ret);
        /**
         * 输出结果：
         * object(MongoDB\BSON\ObjectId)#109 (1) {
             ["oid"]=>
             string(24) "600da377004c82305a02fb52"
           }
         * array(1) {
             [0]=>
             array(1) {
               ["MongoDB\Model\CollectionInfoinfo"]=>
               array(5) {
                 ["name"]=>
                 string(4) "list"
                 ["type"]=>
                 string(10) "collection"
                 ["options"]=>
                 array(0) {
                 }
                 ["info"]=>
                 array(2) {
                   ["readOnly"]=>
                   bool(false)
                   ["uuid"]=>
                   object(MongoDB\BSON\Binary)#110 (2) {
                     ["data"]=>
                     string(16) "EasySwoole"
                     ["type"]=>
                     int(4)
                   }
                 }
                 ["idIndex"]=>
                 array(4) {
                   ["v"]=>
                   int(2)
                   ["key"]=>
                   array(1) {
                     ["_id"]=>
                     int(1)
                   }
                   ["name"]=>
                   string(4) "_id_"
                   ["ns"]=>
                   string(9) "user.list"
                 }
               }
             }
           } 
        */
        
        
        // 使用 php-mongodb 扩展时(不使用 mongodb/mongodb composer组件包)
        /*
        // 插入数据
        $rets = MongoClient::getInstance()->invoke()->callback(function (Driver $driver) {
            $bulk = new \MongoDB\Driver\BulkWrite();

            $bulk->insert([
                'name' => Random::character(8),
                'sex' => 'man',
            ]);

            $bulk->insert(['_id' => 1, 'x' => 1]);
            $bulk->insert(['_id' => 2, 'x' => 2]);

            $bulk->update(['x' => 2], ['$set' => ['x' => 1]], ['multi' => false, 'upsert' => false]);
            $bulk->update(['x' => 3], ['$set' => ['x' => 3]], ['multi' => false, 'upsert' => true]);
            $bulk->update(['_id' => 3], ['$set' => ['x' => 3]], ['multi' => false, 'upsert' => true]);

            $bulk->insert(['_id' => 4, 'x' => 2]);

            $bulk->delete(['x' => 1], ['limit' => 1]);

            $manager = $driver->getDb();
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);
            // 查到 user 库的 list 集合中
            $ret = $manager->executeBulkWrite('user.list', $bulk, $writeConcern);

            printf("Inserted %d document(s)\n", $ret->getInsertedCount()); // 插入条数
            printf("Matched  %d document(s)\n", $ret->getMatchedCount()); // 匹配条数
            printf("Updated  %d document(s)\n", $ret->getModifiedCount()); // 修改条数
            printf("Upserted %d document(s)\n", $ret->getUpsertedCount()); // 修改插入条数
            printf("Deleted  %d document(s)\n", $ret->getDeletedCount()); // 删除条数

            foreach ($ret->getUpsertedIds() as $index => $id) {
                printf('upsertedId[%d]: ', $index);
                var_dump($id);
            }

            if (!$ret) {
                return false;
            }

            return true;
        });

        // 查询数据
        $rets = MongoClient::getInstance()->invoke()->callback(function (Driver $driver) {
            $filter = ['x' => ['$gt' => 1]];
            $options = [
                'projection' => ['_id' => 0],
                'sort' => ['x' => -1],
            ];

// 查询数据
            $query = new \MongoDB\Driver\Query($filter, $options);
            $cursor = $driver->getDb()->executeQuery('user.list', $query);
            foreach ($cursor as $document) {
                print_r($document);
            }
        });
        */

    }
}
```
