## channel Lock
命名空间:`\EasySwoole\Component\ChannelLock`.  
ChannelLock 通过`协程channel`特性实现了关于协程级的锁机制.  

```php
go(function (){
    //加锁
    $result = \EasySwoole\Component\ChannelLock::getInstance()->lock('a');
    var_dump($result);
    co::sleep(1);
    //解锁
    $result = \EasySwoole\Component\ChannelLock::getInstance()->unlock('a');
    var_dump($result);
});
```

### lock
尝试锁住`$lockName`.
方法原型:  function lock(string $lockName,float $timeout = -1):bool 
参数介绍:  
- $lockName 锁名
- $timeout 超时时间,-1为永久不超时
当调用此函数后,会尝试锁住`$lockName`,成功将返回true,如果之前已经有其他协程锁住了此`$lockName`,将会阻塞,直到超时返回false(-1用不超时,代表永远阻塞)  

### unlock
解锁  
方法原型:  function unlock(string $lockName,float $timeout = -1):bool
参数介绍:  
- $lockName 锁名
- $timeout 超时时间,-1为永久不超时
解锁`$lockName`. 成功后将返回true.  

### deferLock
尝试锁住`$lockName`,并在协程结束后自动解锁.  
方法原型:  deferLock(string $lockName,float $timeout = -1):bool

参数介绍:  
- $lockName 锁名
- $timeout 超时时间,-1为永久不超时
