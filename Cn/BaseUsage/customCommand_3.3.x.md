---
title: easyswooe框架自定义命令
meta:
  - name: description
    content: easyswooe框架自定义命令
  - name: keywords
    content: easyswooe框架自定义命令
---

# 自定义命令
`EasySwoole 3.3.x` 默认自带有 5 个命令，如下所示:  
```
php easyswoole help  命令帮助
php easyswoole install 安装(需要在./vendor/easyswoole/easyswoole/bin/easyswoole 文件中调用)
php easyswoole start  启动
php easyswoole stop   停止(需要守护进程)
php easyswoole reload  热重启(需要守护进程)
```

::: warning 
  默认命令详细内容可查看 [服务管理](https://github.com/easy-swoole/doc-3.3.x/tree/master/Cn/QuickStart/server.md)
:::

## 定义命令

通过实现 `\EasySwoole\EasySwoole\Command\CommandInterface` 接口，用户可自定义命令:  

需要实现的接口如下：
```php
<?php

namespace EasySwoole\EasySwoole\Command;

interface CommandInterface
{
    public function commandName():string;
    public function exec(array $args):?string ;
    public function help(array $args):?string ;
}
```

新建文件 `App/Command/Test.php`:

```php
<?php
namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Test implements CommandInterface
{
    public function commandName(): string
    {
        return 'test';
    }

    public function exec(array $args): ?string
    {
        //打印参数,打印测试值
        var_dump($args);
        echo 'test'.PHP_EOL;
        return null;
    }

    public function help(array $args): ?string
    {
        //输出logo
        $logo = Utility::easySwooleLog();
        return $logo."this is test";
    }
}
```

## 注册自定义命令

查看 [boostrap事件](https://github.com/easy-swoole/doc-3.3.x/tree/master/Cn/Core/event/bootstrap.md)

在项目根目录中新增 `bootstrap.php` 文件，添加内容如下，实现注册自定义命令:

```php
<?php

\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->set(new \App\Command\Test());
```

::: warning 
  `bootstrap` 是 `3.2.5` 新增的事件，它允许用户在框架初始化之前执行自定义事件。
:::

## 执行命令及执行结果
```bash
$ php easyswoole test
array(0) {
}
test


$ php easyswoole test 123 456
array(2) {
  [0]=>
  string(3) "123"
  [1]=>
  string(3) "456"
}
test


$ php easyswoole help test
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/
this is test
```
