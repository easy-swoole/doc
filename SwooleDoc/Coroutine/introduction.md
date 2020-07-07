---
title: easyswoole coroutine
meta:
  - name: description
    content: easyswoole coroutine
  - name: keywords
    content: easyswoole coroutine|easyswoole|swoole|coroutine
---

## 协程
可查看[协程](/Cn/NoobCourse/coroutine.md)文章了解详细的协程执行.本章将讲解swoole 协程.  

## swoole协程
在php swoole中,协程可以直接理解为线程,但和线程不同的是,协程需要手动切换,并且只能串行(同一时间只会执行一个协程,多个协程轮流执行).例如:
  
```php
<?php
go(function () {
    echo "1:1\n";
    \co::sleep(0.1);
    echo "1:2\n";
});
go(function () {
    echo "2:1\n";
    \co::sleep(0.1);//手动切换
    echo "2:2\n";
});
go(function () {
    echo "3:1\n";
    \co::sleep(0.1);
    echo "3:2\n";
});

//3个协程函数将会轮流执行,到sleep切换到下一个,然后再切换回来,最后输出为:
//1:1
//2:1
//3:1
//1:2
//3:2
//2:2
```
::: warning
除去\co::sleep方法手动切换,当在协程内调用协程io函数时,也会自动切换,例如协程mysql查询,协程redis等等.在后面会讲到
:::

## 协程容器
使用协程容器,才可以创建协程,否则没有协程环境,不能实现协程切换功能.
  
```php
<?php
//协程容器正常写法
\Swoole\Coroutine::create(function (){
    echo "1:1\n";
    \co::sleep(0.1);
    echo "1:2\n";
});
//没有协程环境的闭包
(function (){
    echo "2:1\n";
//    \co::sleep(0.1);//没有协程环境,不能用\co::sleep
    usleep(1000);
    echo "2:2\n";
})();

//短别名写法
go(function () {
    echo "3:1\n";
    \co::sleep(0.1);
    echo "3:2\n";
});
//输出
//1:1  这里切换出去了
//2:1  由于不是协程环境,所以没法切换出去
//2:2
//3:1  这是第二个协程
//1:2  再切换回第一个协程
//3:2

```

## 协程生命周期  
协程内的生命周期与普通php函数一致,当协程退出后,协程内变量将全部回收.   
协程中可以通过use关键字引入外部变量,也可以使用全局变量,但由于协程是串行执行,当A协程使用全局变量$a时,可能$a变量会被B协程使用,导致全局变量$a赋值污染,例如:

```php
<?php
$a=1;

go(function (){
    global $a;
    echo "1:{$a}\n";//输出1:1
    $a=2;
    $test=1;//这个变量在这个函数结束之后就会回收
    \co::sleep(0.1);
    echo "1:{$a}\n";//输出1:3
});

go(function (){
    global $a;
    echo "2:{$a}\n";//输出2:2
    $a=3;
    $test=1;//这个变量在这个函数结束之后就会回收
    \co::sleep(0.1);
    echo "2:{$a}\n";//输出2:3
});
//结果为
//1:1
//2:2
//1:3
//2:3
```
::: warning
可以看出,$a这个变量,由于被2个协程使用,在协程串行时,赋值将相互冲突.    
:::

## 协程调度
在上面的例子,我们可以看到,协程是串行的,那么协程是如何串行的呢?  
这里有这几点需要知道:  
- 在一个进程内,每次只会运行一个协程.    
- 协程需要手动切换,才能切换到下一个协程.  
- 协程可以进行多次切换,直到完成.   
- swoole已经新增了各种协程客户端(mysql,redis),在使用这些客户端处理时,会自动切换协程.
  
```php
<?php
go(function (){
    echo "协程1开始\n";
    for ($i=0;$i<=100;$i++){

    }
    //此协程没有任何手动切换协程,所以不会发生切换,会一直运行完
    echo "协程1结束\n";
});

go(function (){
    echo "协程2开始\n";
    for ($i=0;$i<=10;$i++){
        echo "协程2打印{$i}\n";
        co::sleep(0.01);//sleep的原理为 先进行yield(挂起这个协程,等待恢复),再0.01秒之后再resume(恢复这个协程,等待执行)
    }
    //此协程有手动切换,所以会切换出去
    echo "协程2结束\n";
});
echo "外部逻辑1\n";//协程2循环第一次,主动切换的时候,就会执行到这里.

$cid3 = go(function (){
    $a = 'xxxxxx';//这是一个变量,协程结束后将销毁
    echo "协程3开始\n";
    \co::yield();//协程3手动挂起,但是没有恢复,如果在其他地方没有执行resume,后面的数据将永远不会执行,并且协程内的变量永远不会销毁
    //此协程有手动切换,所以会切换出去
    echo "协程3结束\n";
});

echo "外部逻辑2\n";//协程3主动切换的时候,就会执行到这里.

go(function ()use($cid3){
    //在打印数据之前,就先执行resume,让协程3恢复执行
    co::resume($cid3);
    echo "协程4开始\n";

    echo "协程4结束\n";
});

echo "外部逻辑3";//这个逻辑会先执行完毕,然后再回去执行未执行完的协程(协程2最后结束);

```
