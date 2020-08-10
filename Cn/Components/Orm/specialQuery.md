---
title: easyswoole ORM 特殊查询
meta:
  - name: description
    content: easyswoole ORM 特殊查询
  - name: keywords
    content:  easyswoole ORM 特殊查询
---

# 特殊条件查询

## find_in_set

生成条件  find_in_set(1, name)

? 代表参数绑定，可以直接写明条件，第二个参数不传递即可，但需要注意防范注入风险

```php
$data = Model::create()->where("find_in_set(?, name)", [1])->get();
```

## 复杂where or

```php
// 生成大概语句：where status = 1 AND (id > 10 or id < 2)
Model::create()->where('status', 1)->where(' (id > 10 or id <2) ')->get();
```


## lock table


```php
Model::create()->func(function ($builder){
  // ...
  $builder->lockTable("tableName");
  // ...
});
```

释放表锁
```php
Model::create()->func(function ($builder){
  // ...
  $builder->unlockTable();
  // ...
});
```
## select for update

```php
Model::create()->get(function ($builder){
  // ...
  $builder->selectForUpdate();
  // ...
});
```