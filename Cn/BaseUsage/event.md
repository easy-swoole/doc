---
title: easyswoole基本使用-event
meta:
  - name: description
    content: easyswoole基本使用-event
  - name: keywords
    content: easyswoole基本使用-event
---

# Event

通过`Container`容器可实现自定义事件功能。

## 使用

### 自定义事件

开发者需要继承`Container`：
```php
<?php
namespace App\Event;

use EasySwoole\Component\Container;
use EasySwoole\Component\Singleton;

class Event extends Container
{
    use Singleton;
    function set($key, $item)
    {
        if (is_callable($item)){
            return parent::set($key, $item);
        }else{
            return false;
        }
    }

    function hook($event,...$arg){
        $call = $this->get($event);
        if (is_callable($call)){
            return call_user_func($call,...$arg);
        }else{
            return null;
        }
    }
}
```

### 注册

`initialize`事件中进行注册：

```php
public static function initialize()
{
    // TODO: Implement initialize() method.
    date_default_timezone_set('Asia/Shanghai');
    \App\Event\Event::getInstance()->set('test', function () {
        echo 'test event';
    });
}
```

### 调用

任意位置即可调用：
```php
Event::getInstance()->hook('test');
```