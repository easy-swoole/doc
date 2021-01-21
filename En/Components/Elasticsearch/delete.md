---
title: easyswoole Coroutine elasticsearch component
meta:
  - name: description
    content: Elasticsearch client，Porting of official client by coroutine
  - name: keywords
    content:  swoole coroutine elasticsearch
---

# delete

##Delete by ID

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Delete();
    $time = time();
    $bean->setIndex('my-index-' . $time);
    $bean->setId('my-id-' . $time);
    $response = $elasticsearch->client()->delete($bean)->getBody();
    $response = json_decode($response,true);
    var_dump($response);
})
```


##Delete according to query

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\DeleteByQuery();
    $time = time();
    $bean->setIndex('my-index-' . $time);
    $bean->setBody([
        'query' => [
            'match'=>['name'=>'Test delete']
        ]
    ]);
    $response = $elasticsearch->client()->deleteByQuery($bean)->getBody();
    $response = json_decode($response,true);
    var_dump($response);
})
```
