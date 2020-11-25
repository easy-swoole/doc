# 框架更新记录
> 框架更新记录仅仅整理自2020年07-15后的记录，其余记录以老版本文档或github记录为准。

# 3.4.0

2020-10-24

与`3.3.x`不兼容，需进行调整.

`3.3.x -> 3.4.x`需要重新执行`php vendor/bin/easyswoole install`.

1. [command](/Cn/QuickStart/command.md)命令变更.

2. 自定义`command`需进行[调整](https://github.com/easy-swoole/command).

3. 移除`EasySwooleEvent`中`onRequest`及`afterRequest`全局事件.

变更为(`initialize`注册即可):
```php
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, callback);
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, callback);
```

`callback`为回调函数,注入参数为：
```php
function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response){

}
```

`onRequest`事件需要返回`bool`,来决定程序是否继续进行`dispatcher`.

4. 移除`Core::getInstance()->isDev();`方法.

5. 增加`Core::getInstance()->runMode();`方法. 

可通过此方法修改运行文件,默认`dev`,也可以通过`command`进行修改.

6. 移除`Core::getInstance()->globalInitialize();`,可自行调用`EasySwooleEvent::initialize()`.
