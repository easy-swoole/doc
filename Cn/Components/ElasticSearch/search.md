---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch|elasticsearch 查询文档|协程版 elasticsearch
---

# ElasticSearch 协程客户端 - 查询文档

# 查询

## 根据 文档ID 查询 document 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Get();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $response = $elasticsearch->client()->get($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## 根据 文档ID 批量查询 document 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Mget();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody(['ids' => ['my-id', '1']]);
    $response = $elasticsearch->client()->mget($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## 根据 文档ID 查询 source 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Get();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $response = $response = $elasticsearch->client()->getSource($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## query 查询用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Search();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody(['query' => ['match' => ['test-field' => 'ab']]]);
    $response = $elasticsearch->client()->search($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## 查询总数用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Count();
    $response = $elasticsearch->client()->count($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response['count']);
});
```

## scroll 分页查询用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $sBean = new \EasySwoole\ElasticSearch\RequestBean\Search();
    $sBean->setIndex('my-index');
    $sBean->setScroll('1m');
    $sBean->setBody([
        'query' => [
            'match' => [
                'test-field' => 'abd'
            ]
        ],
        'sort' => ['_doc'],
        'size' => 1
    ]);
    $sResponse = $elasticsearch->client()->search($sBean)->getBody();
    $sResponse = json_decode($sResponse, true);

    $bean = new \EasySwoole\ElasticSearch\RequestBean\Scroll();
    $bean->setScrollId($sResponse['_scroll_id']);
    $bean->setScroll('1m');
    $response = $elasticsearch->client()->scroll($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## template 查询用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\SearchTemplate();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody([
        'inline' =>
            [
                'query' =>
                    [
                        'match' => ["{{field}}" => "{{value}}"]
                    ]
            ],
        'params' =>
            [
                'field' => 'test-field',
                'value' => '博客'
            ]
    ]);
    $response = $elasticsearch->client()->searchTemplate($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## termVectors 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\TermVectors();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $bean->setPretty(true);
    $bean->setBody([
        'fields' => ['test-field'],
        'offsets' => true,
        'payloads' => true,
        'positions' => true,
        "term_statistics" => true,
        "field_statistics" => true
    ]);
    $response = $elasticsearch->client()->termvectors($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## 分片信息查询用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\SearchShards();
    $bean->setIndex('my-index');
    $response = $elasticsearch->client()->searchShards($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```

## 节点状态获取用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Info();
    $response = $elasticsearch->client()->info($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```
