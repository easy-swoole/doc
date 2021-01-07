---
title: easyswoole Coroutine elasticsearch component
meta:
  - name: description
    content: Elasticsearch client，对官方客户端的协程化移植
  - name: keywords
    content:  swoole协程elasticsearch
---

# Elasticsearch

## Install

```php
composer require easyswoole/elasticsearch
```

## Client

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Search();
    $bean->setIndex('my_index');
    $bean->setType('my_type');
    $bean->setBody(['query' => ['matchAll' => []]]);
    $response = $elasticsearch->client()->search($bean)->getBody();
    var_dump(json_decode($response, true));
})
```

## x-pack validation

When elastic search turns on x-pack login verification, just pass in the username and password in config

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200,
    'username'      => 'elastic',
    'password'      => '123456'
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
```

## Modify HTTP to HTTPS

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200,
    'username'      => 'elastic',
    'password'      => '123456',
    'scheme'        => 'https'
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
```
