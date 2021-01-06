---
title: easyswoole协程elasticsearch组件
meta:
  - name: description
    content: ElasticSearch client，对官方客户端的协程化移植
  - name: keywords
    content: swoole协程elasticsearch
---

# ElasticSearch 协程客户端组件

协程版 ElasticSearch Client，对官方客户端的协程化移植

## 组件要求
- easyswoole/spl: ^1.3
- easyswoole/http-client: ^1.3
- easyswoole/swoole-ide-helper: ^1.3


## 安装方法
> composer require easyswoole/elasticsearch

## 仓库地址
[easyswoole/elasticsearch](https://github.com/easy-swoole/elasticsearch)

## Client 用法

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host' => '127.0.0.1',
    'port' => 9200
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);

go(function () use ($elasticsearch) {
    $bean = new \EasySwoole\ElasticSearch\RequestBean\Search();
    $bean->setIndex('my_index');
    $bean->setType('my_type');
    $bean->setBody(['query' => ['matchAll' => []]]);
    $response = $elasticsearch->client()->search($bean)->getBody();
    var_dump(json_decode($response, true));
});
```

## x-pack 验证

当 `elasticsearch` 开启 `x-pack` 登录验证时，只需在 `config` 中再传入用户名密码即可

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'     => '127.0.0.1',
    'port'     => 9200,
    'username' => 'elastic',
    'password' => '123456'
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
```

## 修改 http 为 https

```php
<?php
$config = new \EasySwoole\ElasticSearch\Config([
    'host'     => '127.0.0.1',
    'port'     => 9200,
    'username' => 'elastic',
    'password' => '123456',
    'scheme'   => 'https'
]);

$elasticsearch = new \EasySwoole\ElasticSearch\ElasticSearch($config);
```