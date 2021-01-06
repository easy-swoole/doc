---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch|elasticsearch 分析文档|协程版 elasticsearch
---

# ElasticSearch 协程客户端 - 分析文档

# 分析

## field 分析用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\FieldCaps();
    $bean->setIndex('my-index');
    $bean->setFields('test-field');

    $response = $elasticsearch->client()->fieldCaps($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```

## query 分析用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Explain();
    $bean->setIndex('my-index');
    $bean->setId('my-id');
    $bean->setBody([
        'query' => [
            'bool' => [
                'must' => [
                    ['match' =>
                        [
                            'test-field' => 'abd'
                        ]
                    ]
                ]

            ]
        ]
    ]);
    $response = $elasticsearch->client()->explain($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```