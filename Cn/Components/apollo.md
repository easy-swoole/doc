---
title: easyswoole 配置中心-apollo客户端
meta:
  - name: description
    content: easyswoole 配置中心-apollo客户端
  - name: keywords
    content: swoole apollo|easyswoole apollo|swoole协程apollo
---

# Apollo 协程客户端

`EasySwoole` 实现了对 [apollo](https://github.com/ctripcorp/apollo) 数据中心的支持，可根据该组件，进行同步配置

## 组件要求

- php: >= 7.1.0
- easyswoole/spl: ^1.2
- easyswoole/http-client: ^1.3

## 安装方法

> composer require easyswoole/apollo

## 仓库地址

[easy-swoole/apollo](https://github.com/easy-swoole/apollo)

## 使用

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

require_once __DIR__ . '/vendor/autoload.php';

go(function () {
    // 配置 apollo 服务器信息
    $server = new \EasySwoole\Apollo\Server([
        'server' => 'http://106.12.25.204:8080',
        'appId' => 'easyswoole'
    ]);
    // 创建 apollo 客户端
    $apollo = new \EasySwoole\Apollo\Apollo($server);
    // 第一次同步
    var_dump($apollo->sync('mysql'));
    // 第二次同步，若服务端没有改变，那么返回的结果，isModify 标记为 false，并带有 lastReleaseKey
    var_dump($apollo->sync('mysql'));
});
```

::: warning 
 开发者可以在服务中启动一个定时器或者自定义进程，实现自动定时更新。关于如何使用定时器或者自定义进程，请查看 [定时器](/Components/Component/timer.md)、[自定义进程](/Components/Component/process.md)
:::

