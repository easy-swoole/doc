---
title: easyswoole Content detection
meta:
  - name: description
    content: easyswoole Content detection
  - name: keywords
    content: swoole|easyswoole|Content detection|Sensitive words|detect
---

# words-match

words-matchThe component is based on the dictionary tree (DFA) and is realized by using the unixsock communication and custom process. The purpose of developing this component is to help the young people quickly deploy the content detection service

## Use scenario

Products related to text content have application scenarios.

such as：

Blog articles, comments detection

Detection of chat content

Blocking junk content

## install

```
composer require easyswoole/words-match
```

## Prepare Thesaurus

When the service is started, the data will be read out row by row. The first column of each row is sensitive words and the other columns are subsidiary information

```
php,Is the best language in the world
java
golang
```

## Code example

#### Service registration
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

#### Client use

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
        $content = 'PHP is the best language in the world';
        WordsMatchClient::getInstance()->detect($content);
    }

    function remove()
    {
        WordsMatchClient::getInstance()->remove('easyswoole');
    }

}
````