---
title: easyswoole ORM模型定义
meta:
  - name: description
    content: easyswoole ORM模型定义
  - name: keywords
    content: easyswoole ORM模型定义
---


# Model

## 基础定义

### 基本模型
定义一个模型基础的模型，必须继承```EasySwoole\ORM\AbstractModel```类
```php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

/**
 * 用户商品模型
 * Class UserShop
 */
class UserShop extends AbstractModel
{
    
}
```

### 数据表名称
必须在Model中定义 ```$tableName``` 属性，指定完整表名，否则将会产生错误Table name is require for model
```php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

/**
 * 用户商品模型
 * Class UserShop
 */
class UserShop extends AbstractModel
{
     /**
      * @var string 
      */
     protected $tableName = 'user_shop';
}
```

## 定义表结构

### 自动生成表结构
```php
$model = new User();
$table = $model->schemaInfo();
```
使用模型中的`schemaInfo()`方法可以获取当前模型指定数据表的结构返回一个`EasySwoole\ORM\Utility\Schema\Table`对象

::: tip 
模型本身会**自动**生成表结构,但每次启动Easyswoole,都会去重新获取一次表结构信息,并且在这次服务中缓存,直到Easyswoole服务停止或者重启
如果不希望每次重启都去请求一次数据库,可自行定义该方法,返回Table对象
:::

### 自定义表结构

在模型类中，我们实现一个`getSchemaInfo`方法，要求返回一个`EasySwoole\ORM\Utility\Schema\Table`实例化对象

```php
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\ORM\AbstractModel;

class User extends AbstractModel
{
    protected $tableName = 'user';

    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id')->setIsPrimaryKey(true);
        $table->colChar('name', 255);
        $table->colInt('age');
        return $table;
    }
}

```
#### 表字段

在Table中，有colX系列方法，用于表示表字段的类型，如以上示例的Int,Char

```php
$table->colInt('id');
$table->colChar('name', 255);
```

#### 表主键

如果需要将某个字段指定为主键 则用连贯操作方式，在后续继续指定即可。

```php
$table->colInt('id')->setIsPrimaryKey(true);
```

## 指定连接名

从 [安装](/Components/Orm/install.html) 章节，我们已经知道了，在注册配置信息的时候，可以给这份配置指定一个`连接名`


可以通过模型类自定义属性 `connectionName` 来指定使用的连接配置，默认为 `default`


假设已经通过 配置信息注册 章节注册了一个 `read` 连接名的配置

那么我们可以在Model中定义指定``` read ```连接名

```php
Class AdminModel extends \EasySwoole\ORM\AbstractModel 
{
    protected $connectionName = 'read';
}
```


可以继续查看 [读写分离](/Components/Orm/readWriteSeparation.html) 章节，进一步查看如何使用不同数据库配置。

## 时间戳

在ORM组件版本 `>= 1.0.18` 后，增加自动时间戳特性支持。

用于：自动写入创建和更新的时间字段。

- 在插入数据的时候，自动设置插入时间为当前，
- 在更新数据的时候，自动设置更新时间为当前。

### 使用方式

```php
use \EasySwoole\ORM\AbstractModel ;

Class AdminModel extends AbstractModel
{
    // 都是非必选的，默认值看文档下面说明
    protected $autoTimeStamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
}
```


### autoTimeStamp

是否开启自动时间戳，默认值 `false`

可选值： 

- true 字段默认为int类型 储存时间戳
- int  字段为int类型 储存时间戳
- datetime  字段为datetime类型  Y-m-d H:i:s

### createTime

`数据创建时间` 字段名，默认值 `create_time` 

可选值

- 任意字符串，对应为表中要储存创建时间的字段名
- false，不处理创建时间字段


### updateTime

`数据更新时间` 字段名，默认值 `update_time` 

可选值

- 任意字符串，对应为表中要储存创建时间的字段名
- false，不处理更新时间字段

## 字段预定义属性


版本要求：orm >= `1.4.9 `


::: tip
利用cast定义可以实现：在取出时自动转换为数组、在存储时自动转换为json字符
:::

数据库储存一般是以文本格式，php擅长的是数组、对象等，达到灵活使用的目的。

也可以定义字段为int、小数、时间戳等

### 定义方式
```php

class TestCastsModel extends AbstractModel
{
    protected $casts = [
        'age'           => 'int',
        'id'            => 'float',
        'addTime'       => 'timestamp',
        'state'         => 'bool',
        // 在join中自定义的
        'test_json'     => 'json',
        'test_array'    => 'array',
        'test_date'     => 'date',
        'test_datetime' => 'datetime',
        'test_string'   => 'string',
    ];
}
```


### 支持类型

| 类型             | 设置值                                  |
| ---------------- | --------------------------------------- |
| 整数             | int、 integer                           |
| 浮点             | real、float、double                     |
| 字符串           | string                                  |
| 布尔值           | bool、boolean                           |
| 数组             | array  相当于json_decode($data, true)   |
| 对象             | json、object   相当于json_decode($data) |
| 日期 Y-m-d       | date                                    |
| 日期 Y-m-d H:i:s | datetime                                |
| 时间戳           | timestamp                               |
| 自定义日期格式   | 未完成                                  |
| 自定义小数类型   | 未完成                                  |


### 示例代码

以下代码为orm组件的单元测试脚本 

```php

    public function testFloat()
    {
        $test = TestCastsModel::create([
            'id' => 1
        ]);
        $this->assertIsFloat($test->id);
    }

    public function testInt()
    {
        $test = TestCastsModel::create([
            'age' => "21"
        ]);
        $this->assertIsInt($test->age);
    }

    public function testTimestamp()
    {
        $test = TestCastsModel::create([
            'addTime' => "2020-6-4 16:45:04"
        ]);
        $this->assertIsInt($test->addTime);
    }

    public function testBool()
    {
        $test = TestCastsModel::create([
            'state' => 0
        ]);
        $this->assertIsBool($test->state);
    }

    public function testString()
    {
        $test = TestCastsModel::create();
        $test->setAttr('test_string', 1);
        $this->assertIsString($test->test_string);
    }

    public function testJson()
    {
        $test = TestCastsModel::create();
        $test->setAttr('test_json', [
            'name' => 'siam'
        ]);

        $this->assertInstanceOf(\stdClass::class, $test->test_json);
    }

    public function testArray()
    {
        $test = TestCastsModel::create();
        $test->setAttr('test_array', [
            'name' => 'siam'
        ]);

        $this->assertIsArray($test->test_array);
    }

    public function testDate()
    {
        $test = TestCastsModel::create();
        $test->setAttr('test_date', time());
        $this->assertEquals(date("Y-m-d"), $test->test_date);
    }
    public function testDateTime()
    {
        $test = TestCastsModel::create();
        $test->setAttr('test_datetime', time());
        $this->assertEquals(date("Y-m-d H:i:s"), $test->test_datetime);
    }

```