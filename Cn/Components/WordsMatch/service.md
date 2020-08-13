---
title: easyswoole 内容检测
meta:
  - name: description
    content: easyswoole 内容检测
  - name: keywords
    content: swoole|easyswoole|内容检测|敏感词|检测
---

## 准备词库


服务启动的时候会一行一行将数据读出来，每一行的第一列为敏感词，其它列为附属信息。

`官方不提供词库，只提供工具`

文件内容示例：

```
php,是世界上,最好的语言
java
golang
程序员
代码
逻辑
```

## 服务注册
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
        
        // 将words-match 进程绑定到主服务
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
