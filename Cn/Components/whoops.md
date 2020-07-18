---
title: easyoole Whoops
meta:
  - name: description
    content: Easyswoole 提供了Whoops驱动，用于开发阶段，友好的排除HTTP业务的错误与异常。
---

# Whoops

Easyswoole 提供了Whoops驱动，用于开发阶段，友好的排除HTTP业务的错误与异常。

![](/Images/Passage/easyWhoops.png)


::: warning 
 切勿用于生产阶段，否则造成代码泄露EasySwoole不负任何责任！！！
:::

## 组件要求

- easyswoole/component: ^2.0
- easyswoole/spl: ^1.1
- easyswoole/template: ^1.0
- easyswoole/utility: ^1.0
- psr/log: ^1.0.1

## 安装方法

> composer require easyswoole/easy-whoops=3.x

## 仓库地址

[easyswoole/easy-whoops=3.x](https://github.com/easy-swoole/easy-whoops)

## 基本使用
直接在EasySwoole 全局的事件中进行注册
```php
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Whoops\Handler\CallbackHandler;
use EasySwoole\Whoops\Handler\PrettyPageHandler;
use EasySwoole\Whoops\Run;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        if(\EasySwoole\EasySwoole\Core::getInstance()->isDev()){
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler);  // 输出一个漂亮的页面
            $whoops->pushHandler(new CallbackHandler(function ($exception, $inspector, $run, $handle) {
                // 可以推进多个Handle 支持回调做更多后续处理
            }));
            $whoops->register();
        }
    }

    public static function mainServerCreate(EventRegister $register)
    {

       if(\EasySwoole\EasySwoole\Core::getInstance()->isDev()){
           Run::attachTemplateRender(ServerManager::getInstance()->getSwooleServer());
       }
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        //拦截请求
        if(\EasySwoole\EasySwoole\Core::getInstance()->isDev()){
            Run::attachRequest($request, $response);
        }
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
```
