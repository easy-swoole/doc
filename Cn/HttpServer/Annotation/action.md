---
title: easyswoole注解控制器 - 行为方法注解
meta:
  - name: description
    content: easyswoole注解控制器 - 行为方法注解
  - name: keywords
    content:  easyswoole注解控制器 - 行为方法注解
---

# Action注解

## @Api

- `name` 注解文档的`api`标题.
- `path` 路由（可注册到fast-route）
- `version` `api`版本号（暂时没用）
- `description` `api`描述（新版本建议使用`@ApiDescription`）
- `deprecated` 注解文档标注此`api`为废弃

## @ApiDescription

- `value` `api`描述

## @Method

- `allow` 验证请求方法

# 注入fast-route

```php
<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\HttpAnnotation\Utility\Scanner;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        $scanner = new Scanner();
        $scanner->mappingRouter($routeCollector, EASYSWOOLE_ROOT . '/App', 'App\\HttpController\\');
    }
}
```
> 这样就可以把@Api注解中的path注入到fast-route,具体用法查看[动态路由](HttpServer/dynamicRoute.html)

# example

```php
<?php


namespace App\HttpController;


use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;

/**
 * Class Test
 * @package App\HttpController
 */
class Test extends AnnotationController
{

    /**
     * @Api(name="test-index",path="/test/index",version="1.0")
     * @ApiDescription("test-index-action")
     */
    public function index()
    {

    }

    /**
     * @Api(name="test-deprecated",path="/test/index",version="1.0",deprecated=true)
     */
    public function deprecated()
    {

    }

    /**
     * @Api(name="test-post")
     * @Method(allow={POST,PUT})
     */
    public function post(){

    }
}
```