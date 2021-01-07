---
title: easyswoole Content detection
meta:
  - name: description
    content: easyswoole Content detection
  - name: keywords
    content: swoole|easyswoole|Content detection|Sensitive words|detect
---

## Prepare Thesaurus


When the service is started, the data will be read out row by row, the first column of each row is sensitive words, and the other columns are subsidiary information.

`Official does not provide thesaurus, only tools`

Sample file content：

```
php,Is the best language in the world
java
golang
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
            'wordBank' => '/Users/xxx/sites/easyswoole/WordsMatch/comment.txt', // Thesaurus address
            'processNum' => 3, // Number of processes
            'maxMem' => 1024, // Maximum memory per process(M)
            'separator' => ',', // Separators for words and other information
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

## config

#### wordBank

Thesaurus path

#### processNum 

words-match number of processes, default 3

#### maxMem 

Maximum memory occupied by each process, 512M by default

#### separator

Separator for each line of word information, default comma