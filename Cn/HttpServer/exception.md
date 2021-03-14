---
title: easyswoole Http服务-错误与异常拦截
meta:
  - name: description
    content: easyswoole Http服务-错误与异常拦截
  - name: keywords
    content:  easyswoole Http服务-错误与异常拦截
---
# 错误与异常拦截

## http 控制器错误异常

在 `http` 控制器中出现错误，系统将使用默认异常处理进行输出至客户端，代码如下：

```php
<?php
protected function onException(\Throwable $throwable, Request $request, Response $response)
{
    if(is_callable($this->httpExceptionHandler)){
        call_user_func($this->httpExceptionHandler,$throwable,$request,$response);
    }else{
        $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
        $response->write(nl2br($throwable->getMessage()."\n".$throwable->getTraceAsString()));
    }
}
```

可直接在控制器重写 `onException` 方法：

```php
<?php
namespace App\HttpController;

use App\ViewController;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;

class Base extends ViewController
{

    function index()
    {
        // TODO: Implement index() method.
        $this->actionNotFound('index');
    }

    function onException(\Throwable $throwable): void
    {
        var_dump($throwable->getMessage());
    }

    protected function actionNotFound(?string $action): void
    {
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
        $this->response()->write('action not found');
    }
}
```

## 自定义异常处理

开发者可自定义异常处理文件：
```php
<?php
namespace App;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class ExceptionHandler
{
    public static function handle(\Throwable $exception, Request $request, Response $response)
    {
        var_dump($exception->getTraceAsString());
    }
}
```

在 `initialize` 事件中使用 `DI` 注册异常处理:

```php
<?php

public static function initialize()
{
    \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_EXCEPTION_HANDLER, [\App\ExceptionHandler::class, 'handle']);
}
```
