## channel Lock
Namespace:`\EasySwoole\Component\ChannelLock`.  
ChannelLock adopt`Coroutine channel`Feature implements the locking mechanism of the cooperation level.  

```php
go(function (){
    //Lock up
    $result = \EasySwoole\Component\ChannelLock::getInstance()->lock('a');
    var_dump($result);
    co::sleep(1);
    //Unlock
    $result = \EasySwoole\Component\ChannelLock::getInstance()->unlock('a');
    var_dump($result);
});
```

### lock
Try locking`$lockName`.
Method prototype:  function lock(string $lockName,float $timeout = -1):bool 
Parameter introduction:  
- $lockName Lock name
- $timeout Timeout -1 means never timeout
When this function is called, it will try to lock ` $lockname '. If it succeeds, it will return true. If other processes have locked this ` $lockname', it will block until the timeout returns false (- 1 does not use timeout, which means blocking forever)  

### unlock
Unlock  
Method prototype:  function unlock(string $lockName,float $timeout = -1):bool
Parameter introduction:  
- $lockName Lock name
- $timeout Timeout - 1 means never timeout
Unlock ` $lockname '. True will be returned after success

### deferLock
Try to lock ` $lockname 'and unlock it automatically after the cooperation 
Method prototype:  deferLock(string $lockName,float $timeout = -1):bool

Parameter introduction:  
- $lockName Lock name
- $timeout Timeout,-1 is never timeout
