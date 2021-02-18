---
title: easyswoole Http服务-模版引擎
meta:
  - name: description
    content: easyswoole Http服务-模版引擎
  - name: keywords
    content: easyswoole Http服务-模版引擎
---

# 模板引擎

## 渲染驱动
`EasySwoole` 引入模板渲染驱动的形式，把需要渲染的数据，通过协程客户端投递到自定义的同步进程中进行渲染并返回结果。为何要如此处理，原因在于，市面上的一些模板引擎在 `Swoole` 协程下存在变量安全问题。例如以下流程：
   
 - request A reached, static A assign requestA-data
 - compiled template 
 - write compiled template (yield current coroutine)
 - request B reached，static A assign requestB-data
 - render static A data into complied template file
   
以上流程我们可以发现，`A` 请求的数据，被 `B` 请求给污染了。为了解决该问题，`EasySwoole` 引入模板渲染驱动模式。

## 组件要求
- easyswoole/spl: ^1.0
- easyswoole/component: ^2.0

## 安装方法
> composer require easyswoole/template

## 仓库地址
[easyswoole/template](https://github.com/easy-swoole/template)

## 基础实现原理讲解

### 实现渲染引擎
```php
<?php
class R implements \EasySwoole\Template\RenderInterface
{
    public function render(string $template, ?array $data = null, ?array $options = null): ?string
    {
        return 'todo some thing';
    }

    public function onException(\Throwable $throwable, $arg): string
    {
        return $throwable->getMessage();
    }
}
```  

::: tip
旧版本 Template (1.1.0 之前版本) 实现渲染引擎如下：
:::

```php
<?php
class R implements \EasySwoole\Template\RenderInterface
{
    public function render(string $template, ?array $data = [], ?array $options = []):?string
    {
        return 'todo some thing';
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {
        // TODO: Implement afterRender() method.
    }

    public function onException(Throwable $throwable, $arg):string
    {
        return $throwable->getMessage();
    }
}
```

### 在自定义 HTTP 服务中调用渲染引擎
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class MyRender implements \EasySwoole\Template\RenderInterface
{

    public function render(string $template, ?array $data = null, ?array $options = null): ?string
    {
        return "your template is {$template} and data is " . json_encode($data);
    }

    public function onException(\Throwable $throwable, $arg): string
    {
        return $throwable->getTraceAsString();
    }
}

$renderConfig = \EasySwoole\Template\Render::getInstance()->getConfig();

/*
 * 可选配置
$renderConfig->setTempDir(getcwd()); // 设置 渲染引擎驱动 Socket 存放目录，默认为 getcwd()
$renderConfig->setTimeout(3); // 设置 超时时间，默认为 3s，不建议修改
$renderConfig->setServerName('EasySwoole'); // 设置 渲染引擎驱动服务名称，不建议修改
$renderConfig->setWorkerNum(3); // 设置 渲染引擎服务工作进程数，默认为 3，不建议修改
 */

$renderConfig->setRender(new MyRender()); // 设置 渲染引擎

$http = new swoole_http_server("0.0.0.0", 9501);
$http->on("request", function ($request, $response) {
    $ret = \EasySwoole\Template\Render::getInstance()->render('index.html', ['easyswoole' => 'hello']);
    $response->end($ret);
});

// 调用渲染引擎
\EasySwoole\Template\Render::getInstance()->attachServer($http);

$http->start();
```

::: tip
旧版本 Template 组件(1.1.0 之前)在自定义 `HTTP` 服务中调用渲染引擎时，实现渲染引擎接口的方法有些许不同，详细请看上文实现渲染引擎。
:::

### 重启渲染引擎
由于某些模板引擎会缓存模板文件，导致可能出现以下情况：
 - 用户 `A` 请求 `1.tpl` 返回 'a'
 - 开发者修改了 `1.tpl` 的数据，改成了 'b'
 - 用户 `B、C、D` 在之后的请求中，可能会出现 'a'、'b'两种不同的值
 
那是因为模板引擎已经缓存了 `A` 所在进程的文件，导致后面的请求如果也分配到了 `A` 的进程，就会获取到缓存的值

解决方案如下：
- 1: 重启 `EasySwoole` 服务，即可解决
- 2: 模板渲染引擎实现了重启方法 `restartWorker`，直接调用即可

```
Render::getInstance()->restartWorker();
```

用户可以根据自己的逻辑，自行调用 `restartWorker` 方法进行重启。

#### 重启渲染引擎使用示例

例如：用户可以在控制器中新增 `reload` 方法重启渲染引擎：

1、实现自定义渲染引擎，新建 `App\RenderDriver\MyRender.php` 文件
```php
<?php

namespace App\RenderDriver;

class MyRender implements \EasySwoole\Template\RenderInterface
{
    public function render(string $template, ?array $data = null, ?array $options = null): ?string
    {
        return "your template is {$template} and data is " . json_encode($data);
    }

    public function onException(\Throwable $throwable, $arg): string
    {
        return $throwable->getTraceAsString();
    }
}
```

::: tip
旧版本 Template 组件(1.1.0 之前)实现自定义渲染引擎接口的方法和最新稳定版本有些许不同，详细请看上文。
:::

2、注册渲染引擎服务
```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Template\Render;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $renderConfig = \EasySwoole\Template\Render::getInstance()->getConfig();

        /*
         * 可选配置
        $renderConfig->setTempDir(getcwd()); // 设置 渲染引擎驱动 Socket 存放目录，默认为 getcwd()
        $renderConfig->setTimeout(3); // 设置 超时时间，默认为 3s，不建议修改
        $renderConfig->setServerName('EasySwoole'); // 设置 渲染引擎驱动服务名称，不建议修改
        $renderConfig->setWorkerNum(3); // 设置 渲染引擎服务工作进程数，默认为 3，不建议修改
         */

        $renderConfig->setRender(new \App\RenderDriver\MyRender());
        Render::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

3、在控制器中新增 `reload` 方法重启渲染引擎
```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Template\Render;

class Index extends Controller
{
    public function index()
    {
        $this->response()->write(Render::getInstance()->render('index.tpl', [
            'user' => 'easyswoole',
            'time' => time()
        ]));
    }

    public function reload()
    {
        Render::getInstance()->restartWorker();
        $this->response()->write('restart worker success!');
    }
}
```

运行结果：访问 `http://127.0.0.1:9501/` (示例请求地址) 即可看到运行结果: `your template is index.tpl and data is {"user":"easyswoole","time":1613659221}`，然后访问 `http://127.0.0.1:9501/reload` (示例请求地址) 即可重启渲染引擎，看到运行结果 `restart worker success!`


## 使用示例(在 EasySwoole 中使用)

### 使用 Smarty 渲染

#### 引入Smarty
> composer require smarty/smarty

#### 实现渲染引擎
新建 `\App\RenderDriver\Smarty.php`，内容如下：
```php
<?php

namespace App\RenderDriver;

use EasySwoole\Template\RenderInterface;

class Smarty implements RenderInterface
{
    private $smarty;

    function __construct()
    {
        $temp = sys_get_temp_dir();
        $this->smarty = new \Smarty();
        $this->smarty->setTemplateDir(EASYSWOOLE_ROOT . '/View/');
        $this->smarty->setCacheDir("{$temp}/smarty/cache/");
        $this->smarty->setCompileDir("{$temp}/smarty/compile/");
    }

    public function render(string $template, ?array $data = null, ?array $options = null): ?string
    {
        foreach ($data as $key => $item) {
            $this->smarty->assign($key, $item);
        }
        return $this->smarty->fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false,
            $merge_tpl_vars = true, $no_output_filter = false);
    }

    public function onException(\Throwable $throwable, $arg): string
    {
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        trigger_error($msg);
        return $msg;
    }
}
```

::: tip
旧版本 Template 组件(1.1.0 之前)实现渲染引擎接口的方法和最新稳定版本有些许不同，详细请看上文。Template 1.1.0 之前版本实现如下:
:::

```php
<?php
namespace App\RenderDriver;

use EasySwoole\Template\RenderInterface;

class Smarty implements RenderInterface
{
    private $smarty;

    function __construct()
    {
        $temp = sys_get_temp_dir();
        $this->smarty = new \Smarty();
        $this->smarty->setTemplateDir(EASYSWOOLE_ROOT . '/App/View/');
        $this->smarty->setCacheDir("{$temp}/smarty/cache/");
        $this->smarty->setCompileDir("{$temp}/smarty/compile/");
    }

    public function render(string $template, ?array $data = [], ?array $options = []): ?string
    {
        foreach ($data as $key => $item) {
            $this->smarty->assign($key, $item);
        }
        return $this->smarty->fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false,
            $merge_tpl_vars = true, $no_output_filter = false);
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {

    }

    public function onException(\Throwable $throwable, $arg): string
    {
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        trigger_error($msg);
        return $msg;
    }
}
```

#### 在 EasySwoole 的 HTTP 服务中调用
首先在 `EasySwoole` 全局事件 `EasySwooleEvent.php` 的 `mainServerCreate` 事件中注册渲染引擎服务，注册示例代码如下：

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 获取 Render 配置
        $renderConfig = \EasySwoole\Template\Render::getInstance()->getConfig();

        // [可选配置]
        /*
        $renderConfig->setTimeout(3); // 设置 超时时间，默认为 3s，不建议修改
        $renderConfig->setServerName('EasySwoole'); // 设置 渲染引擎驱动服务名称，不建议修改
        $renderConfig->setWorkerNum(3); // 设置 渲染引擎服务工作进程数，默认为 3，不建议修改
         */

        // 设置 渲染引擎模板驱动
        $renderConfig->setRender(new \App\RenderDriver\Smarty());

        // 设置 渲染引擎进程 Socket 存放目录，默认为 getcwd()
        $renderConfig->setTempDir(EASYSWOOLE_TEMP_DIR);

        // 注册进程到 EasySwoole 主服务
        \EasySwoole\Template\Render::getInstance()->attachServer(\EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer());
    }
}
```

在控制器层响应(使用示例代码如下)：

首先新建 `App\View\custom.html`，内容如下：
```html
{$name}
```

在控制器中进行调用：
```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    public function index()
    {
        $this->response()->write(\EasySwoole\Template\Render::getInstance()->render('custom.html', ['name' => 'Welcome To Use EasySwoole ^_^!']));
    }
}
```

运行结果：启动服务，访问 `http://127.0.0.1:9501`，即可看到运行结果：`Welcome To Use EasySwoole ^_^!` 
 
## 支持常用的模板引擎
 
下面列举一些常用的模板引擎包方便引入使用:
 
### [smarty/smarty](https://github.com/smarty-php/smarty)
 
`Smarty` 是一个使用 `PHP` 写出来的模板引擎，是目前业界最著名的 `PHP` 模板引擎之一。
 
#### 引入方法

::: warning 
composer require smarty/smarty=~3.1
:::


### [league/plates](https://github.com/thephpleague/plates)
 
使用原生 `PHP` 语法的非编译型模板引擎，更低的学习成本和更高的自由度。

#### 引入方法

::: warning 
composer require league/plates=3.*
:::

 
### [duncan3dc/blade](https://github.com/duncan3dc/blade)
 
`Laravel` 框架使用的模板引擎

#### 引入方法

::: warning 
composer require duncan3dc/blade=^4.5
:::

### [topthink/think-template](https://github.com/top-think/think-template)
 
`ThinkPHP` 框架使用的模板引擎

#### 引入方法

::: warning 
composer require topthink/think-template
:::

::: tip
如果用户想要在 `EasySwoole` 框架中使用以上模板引擎，具体使用示例可以查看[Template 使用示例](https://github.com/easy-swoole/demo/tree/3.x-template) 或者 [Template 组件单元测试用例](https://github.com/easy-swoole/template/tree/master/tests)。上文中讲述了使用 Smarty 模板引擎的使用示例，其他模板引擎的使用方法大致类似。
:::

## 常见问题

### 注册渲染引擎失败，出现 UnixSocket bind 失败
- 报错结果类似如下：

```bash
PHP Fatal error:  Uncaught EasySwoole\Component\Process\Exception: EasySwoole\Template\RenderWorker bind /work/EasySwoole.Render.Worker.0.sock fail case Operation not permitted in /work/vendor/easyswoole/component/src/Process/Socket/AbstractUnixProcess.php:32
```

- 失败原因：部分 `vargrant` 服务器或 `Docker` 服务器没有权限创建 UnixSocket，导致注册渲染引擎失败。
- 解决方案：注册渲染引擎时，设置渲染引擎驱动进程 `Socket` 存放目录为 `'/Tmp'`。示例代码如下:

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 获取 Render 配置
        $renderConfig = \EasySwoole\Template\Render::getInstance()->getConfig();
        // 设置 渲染引擎模板驱动
        $renderConfig->setRender(new \App\RenderDriver\Smarty());


        ###  设置 渲染引擎进程 Socket 存放目录为 '/Tmp'  ###
        $renderConfig->setTempDir('/Tmp');


        // 注册进程到 EasySwoole 主服务
        \EasySwoole\Template\Render::getInstance()->attachServer(\EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer());
    }
}
``` 