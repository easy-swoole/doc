
## 自定义队列

实现EasySwoole\Queue\QueueDriverInterface;接口，以组件默认的fast-cache queue为例

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


### 分布式

使用组件自带的redis通信或自定义通信方式，即可实现