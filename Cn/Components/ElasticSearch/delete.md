---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch|elasticsearch 删除文档|协程版 elasticsearch
---

# ElasticSearch 协程客户端 - 删除文档

## 根据 id 删除文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Delete();
    $time = time();
    $bean->setIndex('my-index-' . $time);
    $bean->setId('my-id-' . $time);
    $response = $elasticsearch->client()->delete($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```


## 根据 query 删除文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\DeleteByQuery();
    $time = time();
    $bean->setIndex('my-index-' . $time);
    $bean->setBody([
        'query' => [
            'match' => ['name' => '测试删除']
        ]
    ]);
    $response = $elasticsearch->client()->deleteByQuery($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```