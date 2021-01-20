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
直接在 `EasySwoole` 全局事件中进行注册
```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response): bool {
            // 拦截请求
            if (\EasySwoole\EasySwoole\Core::getInstance()->runMode() == 'dev') {
                \EasySwoole\Whoops\Run::attachRequest($request, $response);
            }
            return true;
        });

        if (\EasySwoole\EasySwoole\Core::getInstance()->runMode() == 'dev') {
            $whoops = new \EasySwoole\Whoops\Run();
            $whoops->pushHandler(new \EasySwoole\Whoops\Handler\PrettyPageHandler());  // 输出一个漂亮的页面
            $whoops->pushHandler(new \EasySwoole\Whoops\Handler\CallbackHandler(function ($exception, $inspector, $run, $handle) {
                // 可以推进多个Handle 支持回调做更多后续处理
            }));
            $whoops->register();
        }
    }

    public static function mainServerCreate(EventRegister $register)
    {
        if (\EasySwoole\EasySwoole\Core::getInstance()->runMode() == 'dev') {
            \EasySwoole\Whoops\Run::attachTemplateRender(ServerManager::getInstance()->getSwooleServer());
        }
    }
}
```

经过上面配置完成之后，就可以在框架抛出异常时，输出一个漂亮的异常页面。
