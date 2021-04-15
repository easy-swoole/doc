---
title: easyswoole 路由
meta:
  - name: description
    content: easyswoole URL解析规则 自定义路由
  - name: keywords
    content:  easyswoole URL解析规则| easyswoole 自定义路由 |swoole web框架
---

# 动态路由
动态路由就是把 `url` 的请求优雅地对应到你想要执行的操作方法。
`EasySwoole` 的动态路由是基于 [FastRoute](https://github.com/nikic/FastRoute) 实现，与其路由规则保持一致。 

## 示例代码:  
新建文件 `App\HttpController\Router.php`，(从框架 `3.4.x` 版本开始，用户可能不需要新建此文件。如果用户在安装时选择了释放 `Router.php` 则不必新建，如果没有，请自行新建):  

```php
<?php
namespace App\HttpController;

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // http://localhost:9501/user 将匹配执行 App\HttpController\Index 类的 user 方法
        $routeCollector->get('/user', '/user');


        // http://localhost:9501/user1 将匹配执行 App\HttpController\User 类的 user1 方法
        $routeCollector->get('/user1', '/User/user1');


        // http://localhost:9501/rpc 将匹配执行 App\HttpController\Rpc 类的 index 方法
        $routeCollector->get('/rpc', '/Rpc/index');


        // http://localhost:9501/ 将直接执行下面的回调
        $routeCollector->get('/', function (Request $request, Response $response) {
            $response->write('this router index');
        });
        
        # http://localhost:9501/ 将匹配执行 App\HttpController\Index 类的 index 方法
        // $routeCollector->get('/', '/index');


        // http://localhost:9501/test 将直接执行下面的回调，然后重新定位匹配执行 App\HttpController\Index 类的 child 方法
        $routeCollector->get('/test', function (Request $request, Response $response) {
            $response->write('this router test.');
            return '/child';// 重新定位匹配执行 App\HttpController\Index 类的 child 方法
        });


        // 以下 2 个路由将匹配一样处理方法
        // http://localhost:9501/mtest1 和 http://localhost:9501/mtest2 路由都将匹配执行 App\HttpController\A\B\C\D\Index 类的 index 方法 (EasySwoole 默认支持 5 个层级的控制器深度)
        $routeCollector->get('/mtest1', '/a/b/c/d/index/index');
        $routeCollector->get('/mtest2', '/A/B/C/D/Index/index');
        
        // 从 `easyswoole/http 2.x 版本开始，绑定的参数将由框架内部进行组装到框架的 `Context(上下文)` 数据之中，具体使用请看下文。
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write(json_encode([
                'get' => $request->getQueryParams(),
                'post' => $request->getParsedBody(),
                // 在这里可以获取 id 参数
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)
            ]));
            return false;// 不再往下请求,结束此次响应
        });
        
        /** `easyswoole/http 2.x` 之前版本请使用如下方式，获取绑定的 id 参数 ( $request->getQueryParam('id') */
        /* 
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            // 获取到路由匹配的 id
            $response->write("this is router user ,your id is {$request->getQueryParam('id')}");
            return false; // 不再往下请求,结束此次响应
        });
        */
    }
}
```

访问 `http://127.0.0.1:9501/rpc`，对应执行方法为 `App\HttpController\Rpc.php` 的 `index()` 方法  

::: warning 
  用户在新建控制器类和文件夹时，请使用大驼峰法命名。这样更加规范。
  如果使用回调函数方式处理路由，`return false;` 代表不继续往下请求。
:::

针对 `$routeCollector->get('route', 'handler');`，特别说明下：
- 当 `handler` 为 `/xxx` 时，则对应执行 `App\HttpController\Index.php` 类的 `xxx()` 方法。
- 当 `handler` 为 `/xxx/xxx/xxx/xxx` 或者 `/Xxx/Xxx/Xxx/xxx` 时，二者其实等价，都对应执行 `App\HttpController\Xxx\Xxx\Xxx.php` 类的 `xxx()` 方法。
- 当 `handler` 为 `/xxx/xxx/xxx/Xxx` 或者 `/Xxx/Xxx/Xxx/Xxx` 时，二者也等价，都对应执行 `App\HttpController\Xxx\Xxx\Xxx.php` 类的 `Xxx()` 方法。

综上所述，其实 `handler` 中最后一个 `/` 后的一定为操作方法 (且不会转换大小写)，前面的则为对应控制器所在命名空间及路径，控制器名称及文件夹名称请务必以 `大写字母` 开头，否则路由将不能匹配到对应的执行方法（因为框架内部默认对每一级控制器名进行首字母转大写处理）。而对于 `route` 则没有特殊要求。

## 路由分组

```php
<?php

class Router extends \EasySwoole\Http\AbstractInterface\AbstractRouter
{
    function initialize(\FastRoute\RouteCollector $routeCollector)
    {
        $routeCollector->addGroup('/admin', function (\FastRoute\RouteCollector $collector) {
            // 访问 http://localhost:9501/admin/test?version=x 将匹配如下路由，并且进行再次匹配执行
            $collector->addRoute('GET', '/test', function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
                $version = $request->getQueryParam('version');
                // 这里可以根据 version 参数判断返回新路径
                if ($version == 1) {
                    // http://localhost:9501/admin/test?version=1 将匹配路由 "/V1/admin/test"
                    // 即执行对应的 App\HttpController\V1\Admin.php 类的 test() 方法
                    $path = '/V1' . $request->getUri()->getPath(); // "/V1/admin/test"
                } else {
                    // http://localhost:9501/admin/test?version=2 将匹配路由 "/V2/admin/test"
                    // 即执行对应的 App\HttpController\V2\Admin.php 类的 test() 方法
                    $path = '/V2' . $request->getUri()->getPath(); // "/V2/admin/test"
                }
                // 返回新的构造的path
                return $path;
            });
        });
        
        
        // 注意：http://localhost:9501/admins/index?version=x 不能匹配到下面这个 action 路由配置参数
        // 需要单独配置路由，如下所示：即执行对应的 App\HttpController\V1\Admins.php 类的 index() 方法
        // $collector->addRoute('GET', '/admins/index', '/V1/Admin/index');
        $routeCollector->addGroup('/admins', function (\FastRoute\RouteCollector $collector) {
            // 访问 http://localhost:9501/admins/test?version=x 将匹配如下路由，并且进行再次匹配执行
            $collector->addRoute('GET', '/{action}', function (\EasySwoole\Http\Request $request, Response $response) {
                $version = $request->getQueryParam('version');
                // 这里可以根据 version 参数判断返回新路径
                if ($version == 1) {
                    // http://localhost:9501/admins/test?version=1 将匹配路由 "/V1/admins/test"
                    // 即执行对应的 App\HttpController\V1\Admins.php 类的 test() 方法
                    $path = '/V1' . $request->getUri()->getPath(); // "/V1/admins/test"
                } else {
                    // http://localhost:9501/admins/test?version=2 将匹配路由 "/V2/admins/test"
                    // 即执行对应的 App\HttpController\V2\Admins.php 类的 test() 方法
                    $path = '/V2' . $request->getUri()->getPath(); // "/V2/admins/test"
                }
                // 返回新的构造的path
                return $path;
            });
        });
    }
}
```

## 全局模式拦截

在 `Router.php` 加入以下代码，即可开启全局模式拦截

```php
$this->setGlobalMode(true);
```

全局模式拦截下，路由将只匹配 `Router.php` 中的控制器方法进行响应，将不会执行框架的默认解析


## 异常错误处理  

通过以下 2 个方法，可设置 `路由匹配错误` 以及 `未找到方法的回调`：

在 `Router.php` 加入以下代码：

```php
<?php
$this->setMethodNotAllowCallBack(function (\EasySwoole\Http\Request $request,\EasySwoole\Http\Response $response){
    $response->write('未找到处理方法');
    return false; // 结束此次响应
});
$this->setRouterNotFoundCallBack(function (\EasySwoole\Http\Request $request,\EasySwoole\Http\Response $response){
    $response->write('未找到路由匹配');
    return 'index'; // 重定向到 index 路由
});
```

::: warning 
  该回调函数只针对于 `fastRoute` 未匹配状况，如果回调里面不结束该请求响应，则该次请求将会继续进行`Dispatch` 并尝试寻找对应的控制器进行响应处理。  
:::

## FastRoute 使用

### addRoute 方法

定义路由的 `addRoute` 方法原型如下，该方法需要三个参数，下面围绕这三个参数我们对路由组件进行更深一步的了解

```php
$routeCollector->addRoute($httpMethod, $routePattern, $handler)
```

#### httpMethod

该参数需要传入一个 `大写` 的 `HTTP 方法字符串`，指定路由可以拦截的方法，单个方法直接传入字符串，需要 `拦截多个方法` 可以传入一个 `一维数组`，如下面的例子：

```php
// 拦截GET方法
$routeCollector->addRoute('GET', '/router', '/Index');

// 拦截POST方法
$routeCollector->addRoute('POST', '/router', '/Index');

// 拦截多个方法
$routeCollector->addRoute(['GET', 'POST'], '/router', '/Index');

```

#### routePattern

传入一个路由匹配表达式，符合该表达式要求的路由才会被拦截并进行处理，表达式支持 `{参数名称:匹配规则}` 这样的占位符匹配，用于限定路由参数。

#### handler

指定路由匹配成功后需要处理的方法，可以传入一个闭包，当传入闭包时一定要 **注意处理完成之后要处理结束响应**，否则请求会继续 `Dispatch` 寻找对应的控制器来处理，当然如果利用这一点，也可以对某些请求进行处理后再交给控制器执行逻辑。

```php
// 传入闭包的情况
$routeCollector->addRoute('GET', '/router/{id:\d+}', function (Request $request, Response $response) {
    $id = $request->getQueryParam('id');
	$response->write('Userid : ' . $id);
	return false;
});

```

也可以直接传入控制器路径

```php
$routeCollector->addRoute('GET', '/router2/{id:\d+}', '/Index');
```

### 基本匹配

下面的定义将会匹配 `http://localhost:9501/users/info`

```php
$routeCollector->addRoute('GET', '/users/info', 'handler');
```

### 绑定参数

下面的定义将 `/users/` 后面的部分作为参数，并且限定参数只能是数字 `[0-9]`

```php
// 可以匹配: http://localhost:9501/users/12667
// 不能匹配: http://localhost:9501/users/abcde

$routeCollector->addRoute('GET', '/users/{id:\d+}', 'handler');

```

下面的定义不做任何限定，仅将匹配到的URL部分获取为参数

```php
// 可以匹配: http://localhost:9501/users/12667
// 可以匹配: http://localhost:9501/users/abcde

$routeCollector->addRoute('GET', '/users/{name}', 'handler');
```

有时候路由的部分位置是可选的，可以像下面这样定义

```php
// 可以匹配: http://localhost:9501/users/to
// 可以匹配: http://localhost:9501/users/to/username

$routeCollector->addRoute('GET', '/users/to[/{name}]', 'handler');
```

::: tip 
  从 `easyswoole/http 2.x` 版本开始，绑定的参数将由框架内部进行组装到框架的 `Context(上下文)` 数据之中，具体调用方法请看下文，若想要在 `get` 数据中获得绑定的参数，请看下文进行设置。
:::


以下操作均在`Router.php`中`initialize`方法中操作.

#### GET获取路由参数

从 `$this->request()->getQueryParams()` 即在 `get` 数据中获取 路由匹配的参数，需进行如下设置：

```php
$this->parseParams(\EasySwoole\Http\AbstractInterface\AbstractRouter::PARSE_PARAMS_IN_GET);
```

#### POST获取路由参数

从 `$this->request()->getParsedBody()` 中获取路由匹配的参数，需进行如下设置：

```php
$this->parseParams(\EasySwoole\Http\AbstractInterface\AbstractRouter::PARSE_PARAMS_IN_POST);
```

#### Context获取路由参数

从 `\EasySwoole\Component\Context\ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)` 中获取 路由匹配的参数，需进行如下设置：

```php
$this->parseParams(\EasySwoole\Http\AbstractInterface\AbstractRouter::PARSE_PARAMS_IN_CONTEXT);
```

此配置项是`easyswoole/http 2.x`版本默认配置.

#### NONE
 
不获取路由匹配的参数，需进行如下设置:

```php
$this->parseParams(\EasySwoole\Http\AbstractInterface\AbstractRouter::PARSE_PARAMS_NONE);
```

> 注意：以上 4 种设置，用户只能设置 1 种。`Router` 默认使用的设置是第 3 种。

综合使用示例如下：

```php
<?php

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        ######   针对从 easyswoole/http 2.x 开始的   ######
        ### 获取路由中匹配的参数
        // 可采取如下形式来获取 路由匹配的参数
        # 1. 在 $this->request()->getQueryParams() 中获取 路由匹配的参数，需进行如下设置
        // $this->parseParams(Router::PARSE_PARAMS_IN_GET);
        /*
        ## 使用示例
        $this->parseParams(Router::PARSE_PARAMS_IN_GET);
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write(json_encode([
                'get' => $request->getQueryParams(), // 在这里可以获取 id 参数
                'post' => $request->getParsedBody(),
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)
            ]));
            return false;// 不再往下请求,结束此次响应
        });
        ## 访问: http://localhost:9501/user/100
        ## 响应结果: {"get":{"id":"100"},"post":[],"context":null}
        */
        
        
        # 2. 在 $this->request()->getParsedBody() 中获取 路由匹配的参数，需进行如下设置
        // $this->parseParams(Router::PARSE_PARAMS_IN_POST);
        /*
        ## 使用示例
        $this->parseParams(Router::PARSE_PARAMS_IN_POST);
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write(json_encode([
                'get' => $request->getQueryParams(),
                'post' => $request->getParsedBody(), // 在这里可以获取 id 参数
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)
            ]));
            return false;// 不再往下请求,结束此次响应
        });
        ## 访问: http://localhost:9501/user/100
        ## 响应结果: {"get":[],"post":{"id":"100"},"context":null}
        */
        
        
        # 3. (Router 默认采用的设置) 在 \EasySwoole\Component\Context\ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY) 中获取 路由匹配的参数，需进行如下设置
        // $this->parseParams(Router::PARSE_PARAMS_IN_CONTEXT);
        
        ## 使用示例
        # Router 默认使用此设置，可以不用进行设置，默认就可以 CONTEXT 中获取 路由匹配的参数
        # (可选操作) $this->parseParams(Router::PARSE_PARAMS_IN_CONTEXT);
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write(json_encode([
                'get' => $request->getQueryParams(),
                'post' => $request->getParsedBody(),
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)   // 在这里可以获取 id 参数
            ]));
            return false;// 不再往下请求,结束此次响应
        });
        ## 访问: http://localhost:9501/user/100
        ## 响应结果: {"get":[],"post":[],"context":{"id":"100"}}
        
        
        # 4. 不获取 路由匹配的参数，需进行如下设置
        // $this->parseParams(Router::PARSE_PARAMS_NONE);
        /*
        ## 使用示例
        $this->parseParams(Router::PARSE_PARAMS_NONE);
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write(json_encode([
                'get' => $request->getQueryParams(),
                'post' => $request->getParsedBody(),
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)
            ]));
            return false;// 不再往下请求,结束此次响应
        });
        ## 访问: http://localhost:9501/user/100
        ## 响应结果: {"get":[],"post":[],"context":null}
        */
    }
}
```

::: tip
  `easyswoole/http 2.x` 之前版本绑定的参数将由框架内部进行组装到框架的 `get` 数据之中，调用方式如下：
:::

```php
<?php

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // easyswoole/http 2.x 版本之前的路由匹配采用如下方式即可获取 ( $request->getQueryParam('id') )
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            // 获取到路由匹配的 id
            $response->write("this is router user ,your id is {$request->getQueryParam('id')}");
            return false; // 不再往下请求,结束此次响应
        });
    }
}
```
