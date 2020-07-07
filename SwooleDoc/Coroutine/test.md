---
title: easyswoole swoole-协程测试
meta:
  - name: description
    content: easyswoole swoole-协程测试
  - name: keywords
    content: easyswoole swoole-协程测试|easyswoole|swoole|coroutine
---

## 协程测试
在[什么是协程](/Cn/Swoole/Coroutine/introduction.md) 我们已经讲到了协程需要手动切换,或者通过io函数实现切换,  在这章,我们可以开始对协程做出相应的测试.   

### cpu密集运算/获取数据阻塞时  
```php
<?php
$server = new Swoole\Http\Server("0.0.0.0", 9501);
$server->set([
   'worker_num'=>1//设置1个进程进行处理
]);
$server->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    //模拟当前任务处理特别繁忙的情况,sleep不会自动切换协程,3秒后代表处理完毕.给浏览器返回数据
    //此例子也可以代表处理io阻塞时,例如mysql查询耗时3秒,但用的不是swoole的 mysql协程客户端
    $time = time();
    var_dump($time);
    while(time()-$time<=3) {

    }
    $response->end("test1");//相当于此次请求必须3秒内返回,又因为协程没有做切换,所以第二个请求需要3秒后才能开始处理....
});
$server->start();
```
通过`ab -c2 -n4 http://127.0.0.1:9501/` 测试,发现需要12秒才能测试完毕.   
::: warning
当协程下有着大量cpu运算时,会造成单一协程阻塞,其他协程只能等待其完成cpu.   
通过这个例子我们可以得出,协程在cpu密集运算时并不能提高并发.  
同时,这个例子也可以作为当使用非协程io阻塞函数时,会导致整个进程阻塞,不能提高并发.  
:::

### 使用协程io/正常网站curd时.   
```php
<?php
$server = new Swoole\Http\Server("0.0.0.0", 9501);
$server->set([
    'worker_num' => 1//设置1个进程进行处理
]);
$server->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $user = 'tioncico';
    var_dump(time());
    co::sleep(3);//假设查询$user需要3秒,在使用协程mysql客户端时,会自动切换,查询完自动恢复协程.
    $response->end($user);//相当于此次请求必须3秒内返回,又因为协程没有做切换,所以第二个请求需要3秒后才能开始处理....
});
$server->start();
```

通过`ab -c2 -n4 http://127.0.0.1:9501/` 测试,发现需要6秒即可测试完毕.   
加大并发数`ab -c200 -n400 http://127.0.0.1:9501/`测试,发现还是只需要6秒即可测试完毕.   
::: warning
可以看出,当cpu不密集,并且使用协程客户端时,协程的并发可以非常大,可以同时处理多个请求,但并不能缩短请求时间(查询需要3秒,那链接其实都等待了3秒).    
协程不能增加性能,但可以充分利用cpu资源,在cpu不密集,io阻塞的情况,可通过协程,在同一时间接入更多的任务,并发更多,cpu充分利用.  
可以自行测试并发.  
:::  