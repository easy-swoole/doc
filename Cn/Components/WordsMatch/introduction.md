---
title: easyswoole 内容检测
meta:
  - name: description
    content: easyswoole 内容检测
  - name: keywords
    content: swoole|easyswoole|内容检测|敏感词|检测
---

# words-match

words-match组件是基于字典树(DFA)并利用UnixSock通讯和自定义进程实现，开发本组件的目的是帮小伙伴们快速部署内容检测服务。

使用场景

- 跟文字内容相关的产品都有应用场景。

- 博客类的文章，评论的检测

- 聊天内容的检测

- 对垃圾内容的屏蔽

## 组件要求

None

## 安装方法


> composer require easyswoole/words-match

## 仓库地址

[easyswoole/words-match](https://github.com/easy-swoole/words-match)


## 基本使用

### 准备词库

服务启动的时候会一行一行将数据读出来，每一行的第一列为敏感词，其它列为附属信息

```
php,是世界上,最好的语言
java
golang
程序员
代码
逻辑
```

### 服务注册
```php
<?php
namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\WordsMatch\WordsMatchClient;
use EasySwoole\WordsMatch\WordsMatchServer;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $config = [
            'wordBank' => '/Users/xxx/sites/easyswoole/WordsMatch/comment.txt', // 词库地址
            'processNum' => 3, // 进程数
            'maxMem' => 1024, // 每个进程最大占用内存(M)
            'separator' => ',', // 词和其它信息的间隔符
        ];
        WordsMatchServer::getInstance()
            ->setConfig($config)
            ->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
```

### 客户端使用

````php
<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WordsMatch\WordsMatchClient;

class Index extends Controller
{

    function append()
    {
        WordsMatchClient::getInstance()->append('easyswoole', [3,4,5]);
    }

    function detect()
    {
        $content = 'php是世界上最好的语言';
        WordsMatchClient::getInstance()->detect($content);
    }

    function remove()
    {
        WordsMatchClient::getInstance()->remove('easyswoole');
    }

}
````

## 压测结果

对此组件分别进行1.5万、13万等级的词库测试，服务默认开启3个进程。
::: warning 
仅做参考，具体还以线上验证
:::

### 电脑配置
```
MacBook Air (13-inch, 2017)
处理器 1.8 GHz Intel Core i5
内存 8 GB 1600 MHz DDR3
```

### 1.5万词

##### 并发10总请求数100

```
10 100
Concurrency Level:      10
Time taken for tests:   0.067 seconds
Complete requests:      100
Failed requests:        0
Non-2xx responses:      100
Total transferred:      17300 bytes
HTML transferred:       2600 bytes
Requests per second:    1492.49 [#/sec] (mean)
Time per request:       6.700 [ms] (mean)
Time per request:       0.670 [ms] (mean, across all concurrent requests)
Transfer rate:          252.15 [Kbytes/sec] received
```

##### 并发100总请求数1000

```
Concurrency Level:      100
Time taken for tests:   0.239 seconds
Complete requests:      1000
Failed requests:        0
Non-2xx responses:      1000
Total transferred:      173000 bytes
HTML transferred:       26000 bytes
Requests per second:    4189.17 [#/sec] (mean)
Time per request:       23.871 [ms] (mean)
Time per request:       0.239 [ms] (mean, across all concurrent requests)
Transfer rate:          707.74 [Kbytes/sec] received
```

### 13万词

##### 并发10总请求数100

```
Concurrency Level:      10
Time taken for tests:   0.057 seconds
Complete requests:      100
Failed requests:        0
Non-2xx responses:      100
Total transferred:      17300 bytes
HTML transferred:       2600 bytes
Requests per second:    1751.71 [#/sec] (mean)
Time per request:       5.709 [ms] (mean)
Time per request:       0.571 [ms] (mean, across all concurrent requests)
Transfer rate:          295.94 [Kbytes/sec] received
```

##### 并发100总请求数1000

```
Concurrency Level:      100
Time taken for tests:   0.225 seconds
Complete requests:      1000
Failed requests:        0
Non-2xx responses:      1000
Total transferred:      173000 bytes
HTML transferred:       26000 bytes
Requests per second:    4444.84 [#/sec] (mean)
Time per request:       22.498 [ms] (mean)
Time per request:       0.225 [ms] (mean, across all concurrent requests)
Transfer rate:          750.93 [Kbytes/sec] received
```
