---
title: easyswoole swoole-table
meta:
  - name: description
    content: easyswoole swoole-table
  - name: keywords
    content: easyswoole swoole-table|easyswoole|swoole|table
---

# 高性能内存共享Table
## 介绍
`Table`基于共享内存和锁实现的数据结构。用于解决进程间数据共享和加锁问题。
- 单线程每秒读写200w次，性能非常强悍。
- 用户无需考虑数据同步问题，因为`Table`内置行锁自旋锁。
- 因为`Table`支持多进程,所以可用于多进程数据共享。
- `Table`没有使用全局锁，而是使用的行锁。

:::warning
千万不要使用数组方式读写`Table`。   
:::

`Table`实现了迭代器和`Countable`接口,可通过`foreach`,可以将`Table`里面的数据迭代出来。

## 属性

### memorySize:int

获取占用内存的大小，单位字节。

`Swoole\Table->memorySize;`

## 方法

### __construct()
作用：构造方法.   
方法原型：__construct(int $size, float $conflictProportion = 0.2);    
参数说明：   
- $size  表占用的内存大小
- $conflictProportion 哈希冲突的最大比例

代码:
```php
$table = new Swoole\Table(1024);
```

### column()
作用：内存表增加1个字段.    
方法原型：column(string $name, int $type, int $size = 0);    
参数说明：
- $name 字段名称
- $type 字段类型(Table::TYPE_INT,Table::TYPE_FLOAT,Table::TYPE_STRING)
- $size 字段最大长度(字符串类型时必须指定)

代码:
```php
$table->column('id',\Swoole\Table::TYPE_INT);
$table->column('name',\Swoole\Table::TYPE_STRING,255);
```

### create()
作用：创建内存表.在设置完column之后调用此方法  
:::tip
申请成功返回true 失败返回false    
必须在Server->start()前执行
:::

### set()
作用：设置一条数据.   
方法原型：set(string $key, array $value): bool;  
参数说明：
- $key 数据的键
- $value 数据的值(数组形式,数组内的key必须对应$name)

代码:
```php
$table->set('1',['id' => 1,'name' => 'easyswoole牛逼']);
```

:::tip
set相同的一个key 会覆盖value    
key必须为字符串 长度不能超63字节
:::

### get()
作用：获取一条数据.    
方法原型：get(string $key, string $filed=null): array|false;  
参数说明：
- $key 数据的键 不存在返回false
- string 指定的字段的值 未指定返回整条记录

代码:
```php
$table->get('1');
$table->get('1','id');
```

### incr()
作用：原子自增.    
方法原型：incr(string $key, string $column, mixed $incrby = 1);  
参数说明：
- $key 数据的键
- $column 指定的字段(仅支持int和float)
- $incrby 每次递增大小 $incrby必须和字段类型相同

代码:
```php
$table->incr('1','id',2);
```

### decr()
作用：原子自增.    
方法原型：decr(string $key, string $column, mixed $decrby = 1);  
参数说明：
- $key 数据的键
- $column 指定的字段(仅支持int和float)
- $decrby 每次递减大小 $incrby必须和字段类型相同

代码:
```php
$table->decr('1','id',1);
```

### exist()
作用：检查key是否存在.    
方法原型：exist(string $key): bool;  
参数说明：
- $key 数据的键 不存在返回false 存在返回true

代码:
```php
$table->exist('1');
```

### count()
作用：返回当前table中的总条数.    
方法原型：count(): int;  

代码:
```php
$table->count();
```

### del()
作用：根据key删除数据.    
方法原型：del(string $key): bool;  
参数说明：
- $key 数据的键 失败返回false 成功true

代码:
```php
$table->del('1');
```

## 简单示例代码
```php
<?php

$server = new Swoole\Server('0.0.0.0', 9502);

$table = new Swoole\Table(1024);
$table->column('id', \Swoole\Table::TYPE_INT); // 指定字段 字段名 id 类型 int
$table->column('name', \Swoole\Table::TYPE_STRING, 255); // 指定字段 字段名 name 类型 string 大小 255
$table->create(); // 申请创建内存表

$server->table = $table;
$server->on('receive', function (\Swoole\Server $server, $fd) {
    // 第一次请求 fd 为 1
    $server->table->set($fd, ['id' => $fd, 'name' => 'easyswoole牛逼']); // 设置 key为1的value
    $server->table->get($fd); // 获取key为1的value
    $server->table->count(); // 获取当前table中条数
    $server->table->incr($fd,'id'); // 自增key为1中的id字段
});

$server->start();
```