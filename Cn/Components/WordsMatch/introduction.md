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
php※是世界上※最好的语言
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

use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\WordsMatch\WMServer;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response): bool {
            // TODO: Implement onRequest() method.
            return true;
        });

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response): void {
            // TODO: Implement onRequest() method.
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 配置 words-match
        $wdConfig = new \EasySwoole\WordsMatch\Config();
        $wdConfig->setDict(__DIR__ . '/dictionary.txt'); // 配置 词库地址
        $wdConfig->setMaxMEM(1024); // 配置 每个进程最大占用内存(M)，默认为 512 M
        $wdConfig->setTimeout(3.0); // 配置 内容检测超时时间。默认为 3.0 s
        $wdConfig->setWorkerNum(3); // 配置 进程数
        // $wdConfig->setSockDIR(sys_get_temp_dir()); // (不建议修改)配置 socket 存放地址，默认为 sys_get_temp_dir()，即 '/tmp'

        // 注册服务
        WMServer::getInstance($wdConfig)->attachServer(ServerManager::getInstance()->getSwooleServer());
    }
}
```

### 客户端使用

````php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\WordsMatch\WMServer;

class Index extends Controller
{
    function detect()
    {
        // 需要检测的内容敏感词
        $content = 'php是世界上最好的语言';
        // 检测结果（返回 -1 表示检测超时，匹配检测到时返回检测到的敏感词内容）
        $result = WMServer::getInstance()->detect($content, 3);
        var_dump($result);
        /**
         * 输出结果：
         * array(1) {
            [0]=>
            object(EasySwoole\WordsMatch\Dictionary\DetectResult)#96 (5) {
            ["word"]=>
            string(30) "php是世界上最好的语言"
            ["location"]=>
            array(1) {
              [0]=>
              array(3) {
                ["word"]=>
                string(30) "php是世界上最好的语言"
                ["length"]=>
                int(12)
                ["location"]=>
                array(1) {
                  [0]=>
                  int(0)
                }
              }
            }
            ["count"]=>
            int(1)
            ["remark"]=>
            string(0) ""
            ["type"]=>
            int(1)
            }
         * }
         */
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
