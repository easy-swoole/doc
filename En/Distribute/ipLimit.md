---
title: How does swoole restrict access frequency to IP
meta:
  - name: description
    content: swoole|Swoole learning notes|swoole IP access restrictions
---


# How does swoole restrict access frequency to IP

In the process of API development, sometimes we need to consider single user (IP) access frequency control to avoid malicious calls.

After all, there are only two steps：

- Number of user visits to be counted
- Before executing the operation logic, judge whether the frequency is too high. If it is too high, do not execute it

## IP access frequency limitation in easyswoole

This article gives an example of the code implemented in the easyswoole framework, which is implemented in the same way in the native swoole.

Just judge and intercept the corresponding callback event

- Use swoole\table to save user access (or other components and methods can be used)
- Use timer to clear the access of the previous cycle and count the next cycle

For example, the following iplist class implements initialization of table, statistics of IP access times, and acquisition of records with more than a certain number of times in a cycle
```php
<?php
/**
 * IP visit statistics
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

    public  function __construct()
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
        ], 1024*128);
        $this->table = TableManager::getInstance()->get('ipList');
    }

    function access(string $ip):int
    {
        $key  = substr(md5($ip), 8,16);
        $info = $this->table->get($key);

        if ($info) {
            $this->table->set($key, [
                'lastAccessTime' => time(),
                'count'          => $info['count'] + 1,
            ]);
            return $info['count'] + 1;
        }else{
            $this->table->set($key, [
                'ip'             => $ip,
                'lastAccessTime' => time(),
                'count'          => $info['count'] + 1,
            ]);
            return 1;
        }
    }

    function clear()
    {
        foreach ($this->table as $key => $item){
            $this->table->del($key);
        }
    }

    function accessList($count = 10):array
    {
        $ret = [];
        foreach ($this->table as $key => $item){
            if ($item['count'] >= $count){
                $ret[] = $item;
            }
        }
        return $ret;
    }

}
```

After encapsulating the operation of IP statistics

We can initialize the iplist and timer in the mainservercreate callback event of `EasySooleEvent.php`

```php
<?php

public static function mainServerCreate(EventRegister $register)
{
    // Turn on IP current limiting
    IpList::getInstance();
    $class = new class('IpAccessCount') extends AbstractProcess{
        protected function run($arg)
        {
            $this->addTick(5*1000, function (){
                /**
                 * Normal users will not have more than six API requests in a second
                 * Make list record and clear
                 */
                $list = IpList::getInstance()->accessList(30);
                // var_dump($list);
                IpList::getInstance()->clear();
            });
        }
    };
}
```

Then we judge and count IP access in onRequest callback

```php
<?php

public static function onRequest(Request $request, Response $response): bool
{
    $fd = $request->getSwooleRequest()->fd;
    $ip = ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd)['remote_ip'];
    
    // If the access frequency of the current cycle has exceeded the set value, block
    // When testing, you can change 30 to smaller, such as 3
    if (IpList::getInstance()->access($ip) > 30) {
        /**
         * Force connection to close directly
         */
        ServerManager::getInstance()->getSwooleServer()->close($fd);
        // Debug output can be processed logically
        echo 'Being intercepted'.PHP_EOL;
        return false;
    }
    // Debug output can be processed logically
    echo '正常访问'.PHP_EOL;
}
```

The above implements the operation of limiting the same IP access frequency。

It can also be expanded according to its own needs, such as restricting a specific interface.

::: warning 
Easyswoole provides a current limiter component based on atomic counters. It can be used directly. For the tutorial, please check the document of current limiter step by step.
:::
