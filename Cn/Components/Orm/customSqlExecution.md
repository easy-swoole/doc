---
title: easyswoole ORM 自定义sql执行
meta:
  - name: description
    content: easyswoole ORM  自定义sql执行
  - name: keywords
    content:  easyswoole orm  自定义sql执行
---

# 自定义SQL执行

有时候你可能需要在查询中使用原生表达式。你可以使用 `QueryBuilder` 构造一个原生 `SQL` 表达式

ORM 内部依赖的是 `mysqli` 组件的`QueryBuilder`

```php
use EasySwoole\Mysqli\QueryBuilder;

$queryBuild = new QueryBuilder();
// 支持参数绑定 第二个参数非必传
$queryBuild->raw("select * from test where name = ?", ['siam']);
// $queryBuild->raw("select * from test where name = 'siam'");

// 第二个参数 raw  指定true，表示执行原生sql
// 第三个参数 connectionName 指定使用的连接名，默认 default
$data = DbManager::getInstance()->query($queryBuild, true, 'default');

```

通过Model执行
```php
use EasySwoole\Mysqli\QueryBuilder;
// 需要注意的是，这里的sql语句仅仅是示例
// 正确推荐的做法应该仍然是查询Model类对应的表，得到表结构字段的数据
$data = Model::create()->func(function ($builder){
    $builder->where('userId',1)->get('user_list');
});
```

> 注意，func可以返回bool类型，当返回 true的时候，表示该builder 需要以raw 模式执行。

```php
use EasySwoole\Mysqli\QueryBuilder;
// 需要注意的是，这里的sql语句仅仅是示例
// 正确推荐的做法应该仍然是查询Model类对应的表，得到表结构字段的数据
$data = Model::create()->func(function ($builder){
    $builder->raw('select * from user_list where userId = ?',[1]);
    return true;
});
```

::: warning
原生 SQL 表达式将会被当做字符串注入到查询中，因此你应该小心使用，避免创建 SQL 注入的漏洞。
:::
