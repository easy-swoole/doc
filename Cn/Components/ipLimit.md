---
title: swoole如何对ip限制访问频率
meta:
  - name: description
    content: swoole|swoole学习笔记|swoole Ip访问限制
  - name: keywords
    content: swoole|swoole学习笔记|easyswoole Ip访问限制|swoole Ip访问限制
---


# `Swoole` 如何对 `IP` 限制访问频率

在我们开发 `API` 的过程中，有的时候我们还需要考虑单个用户(`IP`)访问频率控制，避免被恶意调用。

归根到底也就只有两个步骤：

- 用户访问要统计次数
- 执行操作逻辑之前要判断次数频率是否过高，过高则不执行

## `EasySwoole` 中实现 `IP` 访问频率限制

本文举例的是在 `EasySwoole` 框架中实现的代码，在`Swoole` 原生中实现方式是一样的。

只要在对应的回调事件做判断拦截处理即可。

- 使用 `Swoole\Table`，存储用户访问情况（也可以使用其他组件、方式存储）
- 使用定时器，将前一周期的访问情况清空，统计下一周期

### 实现 `IP 访问统计类`

如以下 `IpList` 类，实现了 `初始化 Table`、`统计 IP访问次数`、`获取一个周期内次数超过一定值的记录`
```php
<?php
/**
 * Ip访问次数统计
 * User: Siam
 * Date: 2019/7/8 0008
 * Time: 下午 9:53
 */

namespace App;


use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use Swoole\Table;

class IpList
{
    use Singleton;

    /** @var Table */
    protected $table;

    public function __construct()
    {
        TableManager::getInstance()->add('ipList', [
            'ip' => [
                'type' => Table::TYPE_STRING,
                'size' => 16
            ],
            'count' => [
                'type' => Table::TYPE_INT,
                'size' => 8
            ],
            'lastAccessTime' => [
                'type' => Table::TYPE_INT,
                'size' => 8
            ]
        ], 1024 * 128);
        $this->table = TableManager::getInstance()->get('ipList');
    }

    public function access(string $ip): int
    {
        $key = substr(md5($ip), 8, 16);
        $info = $this->table->get($key);

        if ($info) {
            $this->table->set($key, [
                'lastAccessTime' => time(),
                'count' => $info['count'] + 1,
            ]);
            return $info['count'] + 1;
        } else {
            $this->table->set($key, [
                'ip' => $ip,
                'lastAccessTime' => time(),
                'count' => 1,
            ]);
            return 1;
        }
    }

    public function clear()
    {
        foreach ($this->table as $key => $item) {
            $this->table->del($key);
        }
    }

    public function accessList($count = 10): array
    {
        $ret = [];
        foreach ($this->table as $key => $item) {
            if ($item['count'] >= $count) {
                $ret[] = $item;
            }
        }
        return $ret;
    }
}
```

### 初始化 `IP 统计类` 和访问统计定时器

封装完 `IP统计` 的操作之后，我们就可以在 `EasySwooleEvent.php` 中的 `mainServerCreate` 回调事件中初始化 `IpList` 类和定时器，注册 `IP` 统计自定义进程

```php
<?php
use App\IpList;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Manager;

public static function mainServerCreate(EventRegister $register)
{
    // 开启 IP 限流
    IpList::getInstance();
    $class = new class('IpAccessCount') extends AbstractProcess
    {
        protected function run($arg)
        {
            $this->addTick(10 * 1000, function () {
                /**
                 * 正常用户不会有一秒超过 6 次的api请求
                 * 做列表记录并清空
                 */
                $list = IpList::getInstance()->accessList(30);
                // var_dump($list);
                IpList::getInstance()->clear();
            });
        }
    };

    // 注册 IP 限流自定义进程
    $processConfig = new \EasySwoole\Component\Process\Config();
    $processConfig->setProcessName('IP_LIST');// 设置进程名称
    $processConfig->setProcessGroup('IP_LIST');// 设置进程组名称
    $processConfig->setArg([]);// 传参
    $processConfig->setRedirectStdinStdout(false);// 是否重定向标准io
    $processConfig->setPipeType(\EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM);// 设置管道类型
    $processConfig->setEnableCoroutine(true);// 是否自动开启协程
    $processConfig->setMaxExitWaitTime(3);// 最大退出等待时间
    Manager::getInstance()->addProcess(new $class($processConfig));
}
```

### 实现对 `IP` 访问的限制
在 `EasySwooleEvent.php` 中的 `mainServerCreate` 回调事件中
接着我们在 `EasySwooleEvent.php` 中的 `initialize` 回调事件中注入 `HTTP_GLOBAL_ON_REQUEST` 全局事件，判断和统计 `IP` 的访问

```php
<?php

use EasySwoole\Component\Di;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use App\IpList;

public static function initialize()
{
    date_default_timezone_set('Asia/Shanghai');

    Di::getInstance()->set('HTTP_GLOBAL_ON_REQUEST', function (Request $request, Response $response) {
        $fd = $request->getSwooleRequest()->fd;
        $ip = ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd)['remote_ip'];

        // 如果当前周期的访问频率已经超过设置的值，则拦截
        // 测试的时候可以将 30 改小，比如 3
        if (IpList::getInstance()->access($ip) > 3) {
            /**
             * 直接强制关闭连接
             */
            ServerManager::getInstance()->getSwooleServer()->close($fd);
            // 调试输出 可以做逻辑处理
            echo '被拦截' . PHP_EOL;
            return false;
        }
        // 调试输出 可以做逻辑处理
        echo '正常访问' . PHP_EOL;
        return true;
    });
}
```

以上就实现了对同一 `IP` 访问频率的限制操作。具体还可以根据自身需求进行扩展，如对具体的某个接口再进行限流。

::: warning 
`EasySwoole` 提供了一个基于 `Atomic` 计数器的限流器组件。可以直接使用，使用教程请移步查看[限流器文档](Components/atomicLimit.md)。
:::
