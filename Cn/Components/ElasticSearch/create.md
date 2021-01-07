---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch|elasticsearch 单条插入文档|elasticsearch 批量插入文档|协程版 elasticsearch
---

# ElasticSearch 协程客户端 - 插入文档

## 单条插入文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Create();
    $bean->setIndex('my_index');
    $bean->setType('my_type');
    $bean->setId('my_id');
    $bean->setBody(['test_field' => 'test_data']);
    $response = $elasticsearch->client()->create($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response['result']);
});
```

## 批量插入文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Bulk();
    $bean->setIndex('my_index');
    $bean->setType('my_type');

    $body = [];
    for ($i = 1; $i <= 5; $i++) {
        $body[] = [
            'create' => [
                '_index' => 'my-index',
                '_type' => 'my-type',
                '_id' => $i * 1000
            ]
        ];
        $body[] = [
            'test-field' => 'test-data',
        ];
    }

    $bean->setBody($body);
    $response = $elasticsearch->client()->bulk($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```