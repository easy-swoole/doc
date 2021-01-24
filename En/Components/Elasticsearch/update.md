---
title: easyswoole Coroutine elasticsearch component
meta:
  - name: description
    content: Elasticsearch client，Porting of official client by coroutine
  - name: keywords
    content:  swoole coroutine elasticsearch
---

# Update

##Update according to ID

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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
    $response = json_decode($response,true);
    var_dump($response);
})
```

##Update according to query

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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
    $response = json_decode($response,true);
    var_dump($response);
})
```

##Reindex

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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
    $response = json_decode($response,true);
    var_dump($response);
})
```
