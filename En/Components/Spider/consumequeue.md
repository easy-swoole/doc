---
title: Spider
meta:
  - name: description
    content: EasySwoole-Spider It is convenient for users to quickly build distributed multi process Crawlers。
  - name: keywords
    content:  swoole | swoole expand | swoole framework | easyswoole | spider | crawler
---

## Custom queue

Realization EasySwoole\Queue\QueueDriverInterface;，Take the component's default fast-cache queue as an example

```php
namespace EasySwoole\Spider\Queue;

use EasySwoole\FastCache\Cache;
use EasySwoole\Queue\QueueDriverInterface;
use EasySwoole\Queue\Job;

class FastCacheQueue implements QueueDriverInterface
{

    private const FASTCACHE_JOB_QUEUE_KEY='FASTCACHE_JOB_QUEUE_KEY';

    function pop(float $timeout = 3):?Job
    {
        // TODO: Implement pop() method.
        $job =  Cache::getInstance()->deQueue(self::FASTCACHE_JOB_QUEUE_KEY);
        if (empty($job)) {
            return null;
        }
        $job = unserialize($job);
        if (empty($job)) {
            return null;
        }
        return $job;
    }

    function push(Job $job):bool
    {
        // TODO: Implement push() method.
        $res = Cache::getInstance()->enQueue(self::FASTCACHE_JOB_QUEUE_KEY, serialize($job));
        if (empty($res)) {
            return false;
        }
        return true;
    }

    public function size(): ?int
    {
        // TODO: Implement size() method.
    }
}

```


### Distributed

It can be realized by using the redis communication or custom communication mode of the component