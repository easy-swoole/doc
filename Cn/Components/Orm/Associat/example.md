# ORM关联查询示例

简单介绍一下关联查询的使用场景和方法

## 表结构和数据准备


users 用户表
```
| user_id | user_name  |
| ------- | ---------- |
| 1       | Siam(宣言) |
```


phones 手机信息表

```
| phone_id | can_use | user_id | phone_name |
| -------- | ------- | ------- | ---------- |
| 1        | 1       | 1       | 诺基亚A1   |
| 2        | 0       | 1       | 苹果11     |
| 3        | 1       | 2       | 三星ZD     |
```



定义模型的步骤就不说了，这是ORM的基础使用。

## 应用场景

我们现在需要提供一个接口，根据user_id来查询用户的信息，以及这个用户名下的所有手机信息

正常我们需要new两个模型、手动传参来获取手机信息，但是使用关联查询，可以简化这个过程

### 定义关联关系

首先我们需要在模型文件中定义两者的关系（用于查询时自动构建条件语句）

```php

class Users extends AbstractModel
{
    protected $tableName = 'users';
    
	public function phones(){
        // users的phones方法，指向Phones模型，也就是phones表
        // 第二个参数用于更灵活的筛选目标数据，不修改则传递null
        // 第三个参数代表：根据Users模型的user_id字段的值去获取目标数据
        // 第四个参数代表：Phones表上当做条件语句的字段名
        // 构建的语句大概为  select * from phones where user_id(第四个参数) = $Users.user_id (第三个参数)
    	return $this->hasMany(Phones::class, function ($builder){
    	}, 'user_id', 'user_id');
    }
}
```


使用，返回的数据就是拥有手机信息列表的

```php
$user   = Users::create()->get(1);
$phones = $user->phones(); // 这一步可以用预查询完成 简化语句

$this->response()->write(json_encode($user));
```

## 更灵活的筛选数据方式

第二个参数是一个闭包，可以很灵活地调用`mysqli中的QueryBuilder方法`

```php
//状态正常的手机信息
public function phones(){
    return $this->hasMany(Phones::class, function ($builder){
        $builder->where('can_use', 1);
    }, 'user_id', 'user_id'); // 这里填写主表字段的值储存在附表的哪个字段上
}
// 排序
public function phones_desc(){
    return $this->hasMany(Phones::class, function ($builder){
        $builder->orderBy('phone_id', "DESC");
    }, 'user_id', 'user_id'); // 这里填写主表字段的值储存在附表的哪个字段上
}
// 返回部分数据
public function phones_limit(){
    return $this->hasMany(Phones::class, function ($builder){
        $builder->limit(1);
    }, 'user_id', 'user_id'); // 这里填写主表字段的值储存在附表的哪个字段上
}

// where筛选
public function iphone(){
    return $this->hasOne(Phones::class, function($builder){
        $builder->where('phone_name', "苹果11");
    }, 'user_id', 'user_id');
}

// 返回指定字段
public function iphone_field(){
    return $this->hasOne(Phones::class, function($builder){
        $builder->where('phone_name', "苹果11");
        $builder->fields("phone_name, user_id");
    }, 'user_id', 'user_id');
}
```