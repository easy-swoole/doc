---
title: easyswoole自定义事件
meta:
  - name: description
    content: easyswoole自定义事件
  - name: keywords
    content: easyswoole自定义事件
---

# 自定义事件

在 `EasySwoole` 中，可以通过 `\EasySwoole\Component\Container` 容器实现自定义事件功能。

## 使用示例

### 定义事件容器

新增 `App\Event\Event.php` 文件，内容如下：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\Event;

use EasySwoole\Component\Container;
use EasySwoole\Component\Singleton;

class Event extends Container
{
    use Singleton;

    public function set($key, $item)
    {
        if (is_callable($item)) {
            return parent::set($key, $item);
        } else {
            return false;
        }
    }

    public function hook($event, ...$args)
    {
        $call = $this->get($event);
        if (is_callable($call)) {
            return call_user_func($call, ...$args);
        } else {
            return null;
        }
    }
}
```

### 注册事件

在框架的 `initialize` 事件（即项目根目录的 `EasySwooleEvent.php` 的 `initialize` 函数）中进行注册事件：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // 注册事件
        \App\Event\Event::getInstance()->set('test', function () {
            echo 'this is test event!' . PHP_EOL;
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

### 触发事件

注册事件之后，就可以在框架的任意位置触发事件来进行调用，调用形式如下：

```php
<?php
\App\Event\Event::getInstance()->hook('test');
```

#### 在控制器中触发事件进行调用

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    public function index()
    {
        // 触发事件
        \App\Event\Event::getInstance()->hook('test');
    }
}
```

> 访问 `http://127.0.0.1:9501/` （示例请求地址）就可以看到终端显示如下结果：`this is test event!`。
