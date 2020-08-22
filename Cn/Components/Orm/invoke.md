---
title: easyswoole ORM invoke执行
meta:
  - name: description
    content: easyswoole ORM invoke执行
  - name: keywords
    content:  easyswoole ORM invoke执行 高并发优化 数据库优化
---

# orm invoke

在高并发情况下，资源浪费的占用时间越短越好，可以提高程序的服务效率。

在ORM默认情况下是使用defer方法获取pool内的连接资源，并在协程退出时自动归还，在此情况下，在带来便利的同时，会造成不必要资源的浪费。

我们可以使用invoke方式，让ORM查询结束后马上归还资源，可以提高资源的利用率。

```php
$value = DbManager::getInstance()->invoke(function ($client){
    $testUserModel = Model::invoke($client);
    $testUserModel->state = 1;
    $testUserModel->name = 'Siam';
    $testUserModel->age = 18;
    $testUserModel->addTime = date('Y-m-d H:i:s');
    $data = $testUserModel->save();
    return $data;
});
var_dump($value);
```

::: tip
旧版本的invoke没有return值，请更新orm版本。
:::

## 方法支持

在此种模式下，主要有两个方法需要讲解。

- DbManager下的invoke方法 （从连接池内获取一个连接，并在闭包完成时归还连接）
- Model的invoke方法 （注入客户端连接，不再从连接池内defer获取）

## invoke中调试sql

版本>=1.2.12提供特性

关于lastQueryResult、lastQuery返回内容，请查看章节`模型执行结果`、`最后执行语句`

```php
$client->lastQueryResult();
$client->lastQuery();
```

## 指定连接名和超时时间

DbManager的invoke方法可以有3个传参

- 第一个参数为闭包，在以上示例中已经体现
- 第二个参数为连接名，非必选，可以指定要从哪个池中拿出链接
- 第三个参数为超时时间，非必选，默认为3秒