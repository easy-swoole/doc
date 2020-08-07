---
title: easyswoole ORM Casts
meta:
  - name: description
    content: easyswoole ORM 字段 Casts映射类型
---

# 字段预定义属性


版本要求：orm >= `1.4.9 `


::: tip
利用cast定义可以实现：在取出时自动转换为数组、在存储时自动转换为json字符
:::

数据库储存一般是以文本格式，php擅长的是数组、对象等，达到灵活使用的目的。

也可以定义字段为int、小数、时间戳等

## 定义方式
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


## 支持类型

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


## 示例代码

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