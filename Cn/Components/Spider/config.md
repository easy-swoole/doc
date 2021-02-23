
## Config

设置生产端
```php
    public function setProduct(ProductInterface $product): Config
```

设置消费端
```php
    public function setConsume(ConsumeInterface $consume): Config
```

设置队列类型,Config::QUEUE_TYPE_FAST_CACHE、Config::QUEUE_TYPE_REDIS
```php
    public function setQueueType($queueType): Config
```

设置自定义队列,当组件中的队列方式不能满足您的需求时，可以自己实现队列
```php
    public function setQueue($queue): Config
```

分布式时指定某台机器为开始机
```php
    public function setMainHost($mainHost): Config
```

设置自定义队列配置(现在只有redis-pool需要这个方法)

```php
    public function setJobQueueKey($jobQueueKey): Config
```

最大可运行任务数
```php
    public function setMaxCurrency($maxCurrency): Config
```