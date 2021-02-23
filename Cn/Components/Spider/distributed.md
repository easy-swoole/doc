
## 如何实现分布式

使用setMainHost设置某台机器为主机器,使用setQueueType设置为QUEUE_TYPE_REDIS 或自己实现队列

```php
public static function mainServerCreate(EventRegister $register)
{
    // TODO: Implement mainServerCreate() method.
    $config = Config::getInstance()
        ->setProduct(new ProductTest()) // 设置生产端
        ->setConsume(new ConsumeTest()) // 设置消费端

        // ---------------------------分布式需要关心的两个配置
        ->setMainHost('xxxx.xxxx.xxxx.xxxx') // 分布式时指定某台机器为主机器
        ->setQueueType(Config::QUEUE_TYPE_REDIS); // 分布式时使用queuetype为QUEUE_TYPE_REDIS，或者自己实现队列

    Spider::getInstance()
        ->setConfig($config)
        ->attachProcess(ServerManager::getInstance()->getSwooleServer());
}
```
