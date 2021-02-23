---
title: easyswoole swoole-属性
meta:
  - name: description
    content: easyswoole swoole-属性
  - name: keywords
    content: easyswoole swoole-属性|easyswoole|swoole
---

## 属性

### $setting:array
属性说明:调用`$server->set()`方法设置的参数都将保存到`$setting`属性中,可以访问运行参数的值.  
示例:  
```php
<?php

$server = new Swoole\Server('0.0.0.0', 9501);
$server->set([
    'worker_num' => 4,
    'task_tmpdir' => '/tmp',
]);
var_dump($server->setting);
//array(2) {
//  ["worker_num"]=>
//  int(4)
//  ["task_tmpdir"]=>
//  string(4) "/tmp"
//}
```

### $master_pid:int
属性说明:返回当前服务器主进程的进程id(pid).  
示例:  
```php
<?php
$server = new Swoole\Server("0.0.0.0", 9501);
$server->on('start', function ($server){
    echo "master进程id为:{$server->master_pid}";
});
$server->on('receive', function ($server, $fd, $reactorId, $data) {
});
$server->start();
```

:::warning
只有在`onStart/onWorkerStart` 之后才可以获取到.  
:::

### $manager_pid:int
属性说明:当前服务器管理进程的进程id(pid).   
示例:  
```php
<?php
$server = new Swoole\Server("0.0.0.0", 9501);
$server->on('start', function ($server){
    echo "manage进程id为:{$server->manager_pid}";
});
$server->on('receive', function ($server, $fd, $reactorId, $data) {
});
$server->start();
```


### $worker_id:int
属性说明:获得当前`worker/task`进程的编号.
示例:  
```php
<?php
$server = new Swoole\Server("0.0.0.0", 9501);
$server->on('WorkerStart', function ($server, int $workerId){
    echo "workerId(回调函数):" . $workerId . PHP_EOL;
    echo "workerId(server属性):" . $server->worker_id . PHP_EOL;
});
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
});
$server->start();
```

:::warning
`Worker` 进程编号范围是 \[0, worker_num-1\]
`Task` 进程编号范围是 \[worker_num, worker_num + task_worker_num\]
:::

### $worker_pid:int
属性说明:获得`worker/task`进程的进程号(pid),等同于php函数`getmypid()`


### $taskworker:bool
属性说明:判断该进程是否为`task`进程

### $connections:iterator
属性说明:客户端连接迭代器,可以使用`foreach`遍历该属性,将获得所有tcp连接,功能等同于`$server->getClientList()`,但是使用`$connections`属性消耗更低,推荐实现
示例:  
```php
<?php
foreach ($server->connections as $fd) {
  var_dump($fd);
}
echo "当前服务器一共有 " . count($server->connections) . " 个客户端连接\n";
```
:::warning
`$connections`是一个迭代器对象,不能使用`var_dump`,数组下标形式访问
`SWOOLE_BASE` 模式下不支持跨进程操作 `TCP` 连接,所以只能在当前进程内使用 `$connections`.  
:::

### $ports:array
属性说明:监听端口的数组,保存了服务器设置监听的所有端口,数组保存的是`Swoole\Server\Port`对象,可通过该对象重新调用`set/on`方法.  
示例:  
```php
<?php
$ports = $server->ports;
$ports[0]->set($settings);
$ports[1]->on("Receive", function () {
    //callback
});
$ports[2]->on("Receive", function () {
    //callback
});
```
:::warning
`$port[0]`为当前主服务端口
:::
