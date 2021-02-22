---
title: EasySwoole框架设计原理 - bootstrap事件解析
meta:
  - name: keywords
    content: easyswoole bootstrap事件|easyswoole bootstrap
---

# bootstrap 事件

`bootstrap` 事件允许在框架未初始化之前，先进行初始化其他需要的业务代码。该事件是在 `EasySwoole 3.2.5版本之后` 新增的。

在框架安装之后产生的 `easyswoole` 启动脚本文件中，将会自动判断框架根目录下是否有 `bootstrap.php` 文件，如果有则加载此文件。

目前框架最新版本的 `bootstrap.php`(即 `bootstrap` 事件)会在框架安装时在项目根目录中自动生成。所以如果用户想要执行自己需要的初始化业务代码：如 `注册命令行支持`、`全局通用函数`、`启动前调用协程 API `等功能，就可以在 `bootstrap.php` 中进行编写实现。

> 注：`EasySwoole 3.4.x` 版本之前 `bootstrap.php` 文件需要用户在项目根目录下自行创建该文件 `bootstrap.php`。

> 注：如果你是框架旧版升级到框架新版，需要删除框架根目录的 `easyswoole` 文件，然后重新运行 `php ./vendor/easyswoole/easyswoole/bin/easyswoole install` 进行重新安装(报错或者其他原因请重新看 [框架安装章节-执行安装步骤](/QuickStart/install))，重新安装完成之后，即可正常使用 `bootstrap` 事件

## 在框架启用前(在 bootstrap 事件中)调用协程 API
开发者在 `EasySwoole` 主服务启动前调用协程 `api`，必须使用如下操作：
```php
$scheduler = new \Swoole\Coroutine\Scheduler();
$scheduler->add(function() {
    /* 调用协程API */
});
$scheduler->start();
// 清除全部定时器
\Swoole\Timer::clearAll();
```

具体使用示例如下：
```
<?php
// 全局 bootstrap 事件
date_default_timezone_set('Asia/Shanghai');

use Swoole\Coroutine\Scheduler;
$scheduler = new Scheduler();
$scheduler->add(function() {
    /* 调用协程 API */
});
$scheduler->start();
// 清除全部定时器
\Swoole\Timer::clearAll();
```