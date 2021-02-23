---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: Elasticsearch client，对官方客户端的协程化移植
  - name: keywords
    content:  swoole协程elasticsearch
---

# Elasticsearch

## 安装

> composer require easyswoole/elasticsearch

## Client用法

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
});
```

## x-pack验证

当elasticsearch开启x-pack登录验证时，只需在config中再传入用户名密码即可

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200,
    'username'      => 'elastic',
    'password'      => '123456'
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
```

## 修改http为https

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

## 插入

### 单条插入

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
});
```

### 批量插入

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
});
```

## 删除

### 根据id删除

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
});
```


### 根据query删除

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
            'match'=>['name'=>'测试删除']
        ]
    ]);
    $response = $elasticsearch->client()->deleteByQuery($bean)->getBody();
    $response = json_decode($response,true);
    var_dump($response);
});
```

## 查询

### 根据文档ID查询document

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Get();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $response = $elasticsearch->client()->get($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

### 根据ID批量查询document

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Mget();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody(['ids' => ['my-id', '1']]);
    $response = $elasticsearch->client()->mget($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

### 根据文档ID查询source

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Get();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setId('my-id');
    $response = $response = $elasticsearch->client()->getSource($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

### query查询

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Search();
    $bean->setIndex('my-index');
    $bean->setType('my-type');
    $bean->setBody(['query' => ['match' => ['test-field' => 'ab']]]);
    $response = $elasticsearch->client()->search($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

### 查询总数

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Count();
    $response = $elasticsearch->client()->count($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response['count']);
});
```

### scroll分页查询

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Search();
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
    var_dump(json_decode($response,true));
});
```

### template查询

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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

### termVectors

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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

### 分片信息查询

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\SearchShards();
    $bean->setIndex('my-index');
    $response = $elasticsearch->client()->searchShards($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```

### 节点状态

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Info();
    $response = $elasticsearch->client()->info($bean)->getBody();
    $response = json_decode($response, true);
    var_dump($response);
});
```
## 修改

### 根据id修改

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
});
```

### 根据query修改

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
});
```

### Reindex

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
});
```

## 分析

### field分析

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
    $bean = new \EasySwoole\ElasticSearch\RequestBean\FieldCaps();
    $bean->setIndex('my-index');
    $bean->setFields('test-field');

    $response = $elasticsearch->client()->fieldCaps($bean)->getBody();
    $response = json_decode($response,true);
    var_dump($response);
});
```

### query分析

```php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'          => '127.0.0.1',
    'port'          => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function()use($elasticsearch){
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
    $response = json_decode($response,true);
    var_dump($response);
});
```