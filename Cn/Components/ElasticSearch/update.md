---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch|elasticsearch 修改文档|协程版 elasticsearch
---

# ElasticSearch 协程客户端 - 修改文档

## 根据 id 修改文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Update();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $bean->setBody([
        'doc' => [
            'test-field' => 'abd'
        ]
    ]);
    $response = $elasticsearch->client()->update($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```

## 根据 query 修改文档用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\UpdateByQuery();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody([
        'query' => [
            'match' => ['test-field' => 'abd']
        ],
        'script' => [
            'source' => 'ctx._source["test-field"]="testing"'
        ]
    ]);
    $response = $elasticsearch->client()->updateByQuery($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```

## Reindex 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Reindex();
    $bean->setBody([
        'source' => [
            'index' => 'my-index'
        ],
        'dest' => [
            'index' => 'my-index-new'
        ]
    ]);
    $response = $elasticsearch->client()->reindex($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```