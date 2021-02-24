---
title: easyswoole ORM 常见问题
meta:
  - name: description
    content: easyswoole ORM 常见问题
  - name: keywords
    content:  easyswoole ORM 常见问题
---

# ORM 使用中常见问题

## 在 MySQL 中调用存储过程时报错
- 报错原因：在使用 `ORM` 连接接池或者 `MySQLi` 连接池时, 在 `invoke` 里用 `queryBuilder()->raw()` 方式执行 `call 存储过程`，并发时会报错
- 解决方法示例代码如下：

请把 MySQL 连接配置中配置修改成 `fetch_mode=true`，控制器代码需编写如下调用存储过程:

```php
$db->rawQuery("call add_account_proc_v1('002c3d76f952f5bf1271be1bf33771d8', '102', 'huawei', 'tmp', '002c3d76f952f5bf1271be1bf33771d8', 1, '2020-08-14 17:29:36')");
var_dump($db->mysqlClient()->fetchAll());
$db->mysqlClient()->nextResult();
```