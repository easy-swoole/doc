---
title: easyswoole ORM更新记录
meta:
  - name: description
    content: easyswoole ORM更新记录
  - name: keywords
    content: easyswoole ORM更新记录
---


# ORM更新记录

## 1.4.6-1.4.22

- 新增特性：`DbManager` 链接配置自动回收，加大连接池利用效率，支持事务嵌套使用。
- 修改特性：链接配置可开启返回结果集，用于快速all -> toArray，并且支持筛选字段等操作`append/visible/hidden`

## 1.4.6 [#133](https://github.com/easy-swoole/orm/pull/133 "easyswoole orm更新记录")

- 新增特性：Db/Config类的setHost方法，支持解析带端口号的地址，无需setPort，例如：47.111.25.68:8080
```
$config = new Config();
$config->setHost('47.111.25.68:8080');
// 如果接下来使用setPort方法，会更改端口号
```       

## 1.4.5 [#115](https://github.com/easy-swoole/orm/pull/115 "easyswoole orm更新记录")

- 新增特性：hidden 可配合field 在查询出Model后toArray时 筛选、隐藏字段
- 修复特性：在invoke时 关联内部子类继承client

## 1.4.1 [#109](https://github.com/easy-swoole/orm/pull/109 "easyswoole orm更新记录")


- 修改特性：toArray 内部改为循环处理，旧版本关联查询后toArray会得到一个包含关联Model对象的数组，现在会统一调用关联Model对象的toArray()方法

::: tip
User模型关联Role模型 <br/>
旧版 $user->roles() 后toArray   $array['roles] 包含的是Role模型的对象 <br/>
新版 $user->roles() 后toArray   $array['roles] 是一个数组 <br/>
::: 

- 修改特性：为了更灵活地使用关联查询（对目标表进行where、order、limit等筛选操作），内部实现逻辑重写，由join查询改为普通查询，传参调整
- 新增特性：多对多关联`belongsToMany`
- 新增特性：增加`page`方法
- 新增方法：toRawArray($notNull = false, $strict = true) 返回不经过获取器的数据

## 1.4.0 [2020-2-27] [#90](http://github.com/easy-swoole/orm/pull/90/files "easyswoole orm更新记录")

- 移除方法：findAll、select、findOne三个方法
- 传参变动：save方法取消传参 `$notNul = false, $strict = true` 统一为默认 不过滤空字段，严格模式（插入时自动忽略不存在的字段）
- 传参变动：get、all方法取消第二个传参`$returnAsArray = false`

变动原因：统一ORM组件的职责，ORM的设计思想是把数据映射为对象，所以不再返回数组类型，需要获取数组可以使用`$model->toArray()`

两种升级方法：

- 1.统一使用规范，废除数组查询方法
- 2.在基础Model类中自己重写几个数组查询方法，使用toArray()后返回

> 这个是一个特性迁移版本，已经上线的项目建议依赖1.3.x版本的orm，不要升级。 不兼容更新：移除三个方法、toArray统一经过获取器

