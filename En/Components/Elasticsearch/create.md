---
title: easyswoole Coroutine elasticsearch component
meta:
  - name: description
    content: Elasticsearch client，Porting of official client by coroutine
  - name: keywords
    content:  swoole coroutine elasticsearch
---

# Insert

##Single insertion

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Create();
    $bean->setIndex('my_index');
    $bean->setType('my_type');
    $bean->setId('my_id');
    $bean->setBody(['test_field' => 'test_data']);
    $response = $elasticsearch->client()->create($bean)->getBody();
    $response = json_decode($response,true);
    var_dump($response['result']);
})
```

##Bulk insert

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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
    $response = json_decode($response,true);
    var_dump($response);
})
```
