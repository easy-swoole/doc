---
title: easyswoole开发者必读
meta:
  - name: description
    content: easyswoole开发者必读
  - name: keywords
    content: easyswoole开发注意事项|swoole开发注意事项
---
# 开发者必读

- [GitHub](https://github.com/easy-swoole/easyswoole)  喜欢记得点个 ***star***

## 社区答疑

- QQ 交流群 
    - VIP群 579434607 （本群需要付费599元）
    - EasySwoole 官方一群 633921431(已满)
    - EasySwoole 官方二群 709134628(已满)
    - EasySwoole 官方三群 932625047(已满)
    - EasySwoole 官方四群 779897753(已满)
    - EasySwoole 官方五群 853946743

- 商业支持：
    - QQ 291323003
    - EMAIL admin@fosuss.com
      
## 注意事项
- 不要在代码中执行 `sleep` 以及其他睡眠函数，这样会导致整个进程阻塞；协程中可以使用 `Co::sleep()`；
- `exit/die` 是危险的，会导致 `Worker` 进程退出；
- 可通过 `register_shutdown_function` 来捕获致命错误，在进程异常退出时做一些清理工作；
- `PHP` 代码中如果有异常抛出，必须在回调函数中进行 `try/catch` 捕获异常，否则会导致工作进程退出；
- `EasySwoole` 不支持 `set_exception_handler`，必须使用 `try/catch` 方式处理异常；
- 在控制器中不能写共享 `Redis` 或 `MySQL` 等网络服务客户端连接的逻辑，每次访问控制器都必须 `new` 一个连接

## 类/函数重复定义

- 新手非常容易犯这个错误，由于 `EasySwoole` 是常驻内存的，所以加载类/函数定义的文件后不会释放。因此引入类/函数的 php 文件时必须要使用 `include_once` 或 `require_once`，否则会发生 `cannot redeclare function/class` 的致命错误。

::: warning
建议使用 `composer` 做自动加载
:::


## 进程隔离与内存管理

进程隔离也是很多新手经常遇到的问题。修改了全局变量的值，为什么不生效？原因就是全局变量在不同的进程，内存空间是隔离的，所以无效。

所以使用 `EasySwoole` 开发 `Server` 程序需要了解 `进程隔离` 问题。

- 不同的进程中 `PHP` 变量不是共享，即使是全局变量，在 `A` 进程内修改了它的值，在 `B` 进程内是无效的，如果需要在不同的`Worker` 进程内共享数据，可以用 `Redis`、`MySQL`、`文件`、`Swoole\Table`、`APCu`、`shmget` 等工具实现 `Worker` 进程内共享数据

- 不同进程的文件句柄是隔离的，所以在 `A` 进程创建的 `Socket` 连接或打开的文件，在 `B` 进程内是无效，即使是将它的 `fd` 发送到 `B` 进程也是不可用的。(句柄不能进程共享)

- 进程克隆。在 `Server` 启动时，主进程会克隆当前进程状态，此后开始进程内数据相互独立，互不影响。有疑问的新手可以先弄懂`PHP` 的 `pcntl` 扩展

### `EasySwoole` 中对象的4层生命周期

开发 `Swoole` 程序与普通 `LAMP` 下编程有本质区别。在传统的 Web 编程中，PHP 程序员只需要关注 request 到达，request 结束即可。而在 `Swoole` 程序中程序员可以操控更大范围，变量/对象可以有四种生存周期。

::: warning  
变量、对象、资源、require/include 的文件等下面统称为对象
:::

#### 程序全局期

在 `EasySwoole` 框架根目录的 `bootstrap.php` 文件和 `EasySwooleEvent.php` 文件中的 `initialize` 事件函数中创建好的对象，我们称之为程序全局生命周期对象。这些变量只要没有被作用域销毁，在程序启动后就会一直存在，直到整个程序结束运行才会销毁。

有一些服务器程序可能会连续运行数月甚至数年才会关闭/重启，那么程序全局期的对象在这段时间内会持续驻留在内存中的。程序全局期对象所占用的内存是 `Worker` 进程间共享的，不会额外占用内存。  

例如:

- 在 `EasySwooleEvent.php` 文件中的 `initialize` 事件函数中使用 `Di` 注入一个对象，那么在程序开始之后，在`EasySwoole` 的控制器中，或者其他地方都可以通过 `Di` 直接调用这个对象
- 在 `bootstrap.php` 中引入一个文件 `test.php`，该文件定义了一个静态变量，那么在 `EasySwoole` 的控制器，或者其他地方都可以调用这个静态变量

这部分内存会在写时分离（`COW`），在 `Worker` 进程内对这些对象进行写操作时，会自动从共享内存中分离，变为进程全局对象。

例如:

- 在 `EasySwooleEvent.php` 文件中的 `initialize` 事件函数中使用 `Di` 注入一个对象，并在用户 `A` 访问控制器时修改了这个对象的属性，那么其他用户访问控制器的时候，获取这个对象属性时，可能是未改变的状态(因为不同用户访问的控制器所在的进程不同，其他进程不会修改到这个变量，所以需要注意这个问题)；
- 在 `bootstrap.php` 中引入一个文件 `test.php`，该文件定义了一个静态变量 `$a = 1`，用户 `A` 访问控制器时修改了变量 `$a = 2`，可能在其他用户访问时，依然还是 `$a = 1` 的状态。

::: warning 
程序全局期 `include/require` 的代码，必须在整个程序 `shutdown` 时才会释放，`reload` 无效
:::

#### 进程全局期
`Swoole` 拥有进程生命周期控制的机制，`Worker` 进程启动后创建的对象（`onWorkerStart` 中创建的对象或者在控制器中创建的对象），在这个子进程存活周期之内，是常驻内存的。  

例如:

- 程序全局生命周期对象被控制器修改之后，该对象会复制一份出来到控制器所属的进程，这个对象只能被这个进程访问，其他进程访问的依旧是全局对象。
- 给服务注册 `onWorkerStart` 事件(在 `EasySwooleEvent.php` 中的 `mainServerCreate` 事件中进行注册 `onWorkerStart` 事件)时创建的对象，只会在该 `Worker` 进程才能获取到。


::: warning 
进程全局对象所占用的内存是在当前子进程内存堆的，并非共享内存。对此对象的修改仅在当前 `Worker` 进程中有效，进程全局期 `include/require` 的文件，在 `reload` 后就会重新加载
:::

#### 会话期

会话期是在 `onConnect` 后创建，或者在第一次 `onReceive` 时创建，`onClose` 时销毁。一个客户端连接进入后，创建的对象会常驻内存，直到此客户端断开连接才会销毁。  


在 `LAMP` 中，一个客户端浏览器访问多次网站，就可以理解为会话期。但传统 `PHP` 程序，并不能感知到。只有单次访问时使用 ` session_start`，访问 `$_SESSION` 全局变量才能得到会话期的一些信息。

`Swoole` 中会话期的对象直接是常驻内存的，不需要 `session_start` 之类操作。可以直接访问对象，并执行对象的方法。

#### 请求期

请求期是指一个完整的请求发来，也就是 `onReceive` 收到请求开始处理，直到返回结果发送 `response`。这个周期所创建的对象，会在请求完成后销毁。

`Swoole` 中请求期对象与普通 `PHP` 程序中的对象就是一样的。请求到来时创建，请求结束后销毁。


#### swoole_server 中内存管理机制

`swoole_server` 启动后内存管理的底层原理与普通 `php-cli` 程序一致。具体请参考 `Zend VM` 内存管理方面的文章。

#### 局部变量

在事件回调函数返回后，所有局部对象和变量会全部回收，不需要 `unset` 。如果变量是一个资源类型，那么对应的资源也会被 `PHP` 底层释放。

```php
function test()
{
    $a = new Object;
    $b = fopen('/data/t.log', 'r+');
    $c = new swoole_client(SWOOLE_SYNC);
    $d = new swoole_client(SWOOLE_SYNC);
    global $e;
    $e['client'] = $d;
}
```
`$a, $b, $c` 都是局部变量，当此函数 `return` 时，这3个变量会立即释放，对应的内存会立即释放，打开的 `IO` 资源文件句柄会立即关闭。
`$d` 也是局部变量，但是 `return` 前将它保存到了全局变量 `$e`，所以不会释放。当执行 `unset($e['client'])` 时，并且没有任何其他 `PHP` 变量仍然在引用 `$d` 变量，那么 `$d` 就会被释放。

#### 全局变量

在 `PHP` 中，有3类全局变量。

- 使用 `global` 关键词声明的变量
- 使用 `static` 关键词声明的类静态变量、函数静态变量
- `PHP` 的超全局变量，包括 `$_GET、$_POST、$GLOBALS` 等

全局变量和对象，类静态变量，保存在 `swoole_server` 对象上的变量不会被释放。需要程序员自行处理这些变量和对象的销毁工作。

```php
class Test
{
    static $array = array();
    static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
    Test::$array[] = $fd;
    Test::$string .= $data;
}
```

- 在事件回调函数中需要特别注意非局部变量的 `array` 类型值，某些操作如 `TestClass::$array[] = "string"` 可能会造成内存泄漏，严重时可能发生爆内存，必要时应当注意清理大数组。
- 在事件回调函数中，非局部变量的字符串进行拼接操作是必须小心内存泄漏，如 `TestClass::$string .= $data`，可能会有内存泄漏，严重时可能发生爆内存。

解决方法
- 同步阻塞并且请求响应式无状态的 `Server` 程序可以设置 `max_request`，当 `Worker进程/Task进程` 结束运行时或达到任务上限后进程自动退出。该进程的所有变量/对象/资源均会被释放回收。
- 程序内在 `onClose` 或设置定时器及时使用 `unset` 清理变量，回收资源


::: warning 
内存管理部分参照了 `Swoole` 官方文档。
:::

## 约定规范

- 项目中类名称与类文件(文件夹)命名，均为大驼峰，变量与类方法为小驼峰。
- 在 `HTTP` 服务响应中，业务逻辑代码中 `echo $var` 并不会将 `$var` 内容输出至浏览器页面相应内容中，请调用 `Response` 实例中的 `wirte()` 方法实现。
