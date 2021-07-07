---
title: easyswoole ORM 最后执行语句
meta:
  - name: description
    content: easyswoole ORM 最后执行语句
  - name: keywords
    content:  easyswoole ORM 最后执行语句 数据库优化
---

# 最后执行语句

当model执行一个语句之后,会将该次执行的语句对象保存到`$model->lastQuery()`中:

```php
<?php
$model = new AdminModel();
//执行all查询
var_dump($model->all());
//打印最后执行的`EasySwoole\Mysqli\QueryBuilder` 对象
var_dump($model->lastQuery());
//打印最后执行的sql语句
var_dump($model->lastQuery()->getLastQuery());


// 新版本orm提供
$lastQuery = DbManager::getInstance()->getLastQuery()->getLastQuery(); // 第一个lastQuery是对象，第二次是从对象中取出语句
```

::: warning
$model->lastQuery() 返回的是query对象,具体文档可查看:[查询构造器](../Mysqli/builder.html) 文档
:::
