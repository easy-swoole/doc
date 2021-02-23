---
title: easyswoole swoole-协程注意事项
meta:
  - name: description
    content: easyswoole swoole-协程注意事项
  - name: keywords
    content: easyswoole swoole-协程注意事项|easyswoole|swoole|coroutine|channel
---

## 注意事项  
### 变量使用
- 在协程中,需要特别注意,不要使用$_GET,$_POST,$GLOBALS等超全局变量,尤其是需要修改变量值并读取时,将造成协程间变量污染.  
- 协程中访问外部变量必须使用use关键字,或者传形参方式,不能引用变量.  
- 如果需要做多协程之间的通信,可使用[channel](/Cn/Swoole/Coroutine/channel.md)方式通信.  


### 退出协程
在>=v4.1.0之后,使用`exit`退出将只退出当前协程,并在当前协程抛出一个`Swoole\ExitException`.  
在<v4.1.0时,如果使用`exit`,将会造成整个进程退出,禁止使用.  

通过拦截`Swoole\ExitException`异常,可获得这次退出的信息.  
```php
<?php
go(function (){
    try{
        test();
    }catch (\Swoole\ExitException $exception){
        var_dump($exception);
    }
});

function test(){
    test2();
}
function test2(){
    exit(2);
}
```

