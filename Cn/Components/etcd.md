---
title: easyswoole 服务注册-etcd客户端
meta:
  - name: description
    content: easyswoole 服务注册-etcd客户端
  - name: keywords
    content: swoole consul|easyswoole consul|swoole协程etcd
---

# Etcd 协程客户端

`EasySwoole` 提供了一个协程安全的 [Etcd](https://etcd.io/) 协程版本客户端，方便用户做分布式及微服务开发。

## 组件要求
 
- php: >= 7.1.0
- ext-json: *
- easyswoole/http-client: ^1.1

## 安装方法

> composer require easyswoole/etcd-client

## 仓库地址

[easy-swoole/etcd-client](https://github.com/easy-swoole/etcd-client)

## 使用方式

使用如下方式，先给 `Etcd` 客户端注入 `Config` 连接配置，连接上 `Etcd` 服务端。

```php
<?php
require_once 'vendor/autoload.php';

// config 默认 http://127.0.0.1:2379/v3
### 数组形式注入配置
$config = new \EasySwoole\EtcdClient\Config([
    'host'    => '127.0.0.1',
    'port'    => 6379,
    'scheme'  => 'http',
    'version' => 'v3', // v3alpha v3beta v3 v2
    'pretty'  => true,
    'ssl'     => false,
]);

### setter 形式注入配置
$config->setHost('127.0.0.1');
$config->setPort(2379);
$config->setScheme('http');
$config->setVersion('v3'); // v3alpha v3beta v3 v2
$config->setPretty(true);
$config->setSsl(false);

// 数组形式 和 setter 形式两种写法，最终效果是相同的

$etcd = new \EasySwoole\EtcdClient\Etcd($config);

$client = $etcd->client();
```

下文所用到的 `$client` 都是引用上述连接上的客户端。

## KV

### set

```php
<?php
go(function () use ($client) {
    // set value
    $client->put('redis', '127.0.0.1:6379');

    // set value and return previous value
    $client->put('redis', '127.0.0.1:6579', ['prev_kv' => true]);

    // set value with lease
    $client->put('redis', '127.0.0.1:6579', ['lease' => 7587822882194199413]);
});
```

### get

```php
<?php
go(function () use ($client) {
    // get key value
    $client->get('redis');

    // get all keys
    $client->getAllKeys();

    // get keys with prefix
    $client->getKeysWithPrefix('/v3/service/user/');
});
```

### delete

```php
<?php
go(function () use ($client) {
    // delete key
    $client->del('redis');
});
```

### compaction

```php
<?php
go(function () use ($client) {
    // compaction
    $client->compaction(7);
});
```

## Lease

```php
<?php
go(function () use ($client) {
    // grant with ID 0
    $client->grant(3600);

    // grant with ID
    $client->grant(3600, 7587822882194199413);

    // revoke a lease
    $client->revoke(7587822882194199413);

    // keep the lease alive
    $client->keepAlive(7587822882194199413);

    // retrieve lease information
    $client->timeToLive(7587822882194199413);
});
```

## Auth Role User 

```php
<?php
go(function () use ($client) {
    // enable authentication
    $client->authEnable();

    // disable authentication
    $client->authDisable();

    // get auth token
    $token = $client->authenticate('user', 'password');

    // set auth token
    $client->setToken($token);

    // clear auth token
    $client->clearToken();

    // add a new role
    $client->addRole('root');

    // get detailed role information
    $client->getRole('root');

    // delete a specified role
    $client->deleteRole('root');

    // get lists of all roles
    $client->roleList();

    // add a new user
    $client->addUser('user', 'password');

    // get detailed user information
    $client->getUser('root');

    // delete a specified user
    $client->deleteUser('root');

    // get a list of all users.
    $client->userList();

    // change the password of a specified user
    $client->changeUserPassword('user', 'new password');

    // grant a role to a specified user
    $client->grantUserRole('user', 'role');

    // revoke a role of specified user
    $client->revokeUserRole('user', 'role');

    // grant a permission of a specified key or range to a specified role
    $client->grantRolePermission('admin', \EasySwoole\EtcdClient\Etcd::PERMISSION_READWRITE, 'redis');

    // revoke a key or range permission of a specified role
    $client->revokeRolePermission('admin', 'redis');
});
```

