---
title: easyswoole 组件库-actor
meta:
  - name: description
    content: easyswoole 组件库-actor
  - name: keywords
    content: 组件库-actor
---

# Actor

提供`Actor`模式支持，助力游戏行业开发。`EasySwoole`的`Actor`采用自定义`Process`作为存储载体，以协程作为最小调度单位，利用协程`Channel`做`mail box`，而客户端与`Process`之间的通讯，采用`UnixSocket`实现，并且借助`TCP`实现分布式的`ActorClient`，超高并发下也能轻松应对。

## 工作流程

一般来说有两种策略用来在并发线程中进行通信：共享数据和消息传递。使用共享数据方式的并发编程面临的最大的一个问题就是数据条件竞争，当两个实例需要访问同一个数据时，为了保证数据的一致性，通常需要为数据加锁，而Actor模型采用消息传递机制来避免数据竞争，无需复杂的加锁操作，各个实例只需要关注自身的状态以及处理收到的消息。

`Actor`是完全面向对象、无锁、异步、实例隔离、分布式的并发开发模式。`Actor`实例之间互相隔离，`Actor`实例拥有自己独立的状态，各个`Actor`之间不能直接访问对方的状态，需要通过消息投递机制来通知对方改变状态。由于每个实例的状态是独立的，没有数据被共享，所以不会发生数据竞争，从而避免了并发下的加锁问题。

举一个游戏场景的例子，在一个游戏房间中，有5个玩家，每个玩家都是一个`PlayerActor`，拥有自己的属性，比如角色ID，昵称，当前血量，攻击力等。游戏房间本身也是一个`RoomActor`，房间也拥有属性，比如当前在线的玩家，当前场景的怪物数量，怪物血量等。此时玩家A攻击某个怪物，则`PlayerActor-A`向`RoomActor`发送一个攻击怪物的指令，`RoomActor`经过计算，得出玩家A对怪物的伤害值，并给房间内的所有`PlayerActor`发送一个消息（玩家A攻击怪物A，造成175点伤害，怪物A剩余血量1200点），类似此过程，每个`PlayerActor`都可以得知房间内发生了什么事情，但又不会造成同时访问怪物A的属性，导致的共享加锁问题。

## 安装

`Actor`并没有作为内置组件，需要先引入包并进行基础配置才能够使用。

> composer require easyswoole/actor

## 使用

### 建立一个Actor

每一种对象（玩家、房间、甚至是日志服务也可以作为一种`Actor`对象）都建立一个`Actor`来进行管理，一个对象可以拥有多个实例`（Client）`并且可以互相通过信箱发送消息来处理业务。

```php
<?php

namespace App\Player;

use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;

/**
 * 玩家Actor
 * Class PlayerActor
 * @package App\Player
 */
class PlayerActor extends AbstractActor
{
    /**
     * 配置当前的Actor
     * @param ActorConfig $actorConfig
     */
    public static function configure(ActorConfig $actorConfig)
    {
        $actorConfig->setActorName('PlayerActor');
        $actorConfig->setWorkerNum(3);
    }

    /**
     * Actor首次启动时
     */
    protected function onStart()
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onStart\n";
    }

    /**
     * Actor收到消息时
     * @param $msg
     */
    protected function onMessage($msg)
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onMessage\n";
    }

    /**
     * Actor即将退出前
     * @param $arg
     */
    protected function onExit($arg)
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onExit\n";
    }

    /**
     * Actor发生异常时
     * @param \Throwable $throwable
     */
    protected function onException(\Throwable $throwable)
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onException\n";
    }

}
```

### 注册Actor服务

可以使用`setListenAddress`和`setListenPort`指定本机对外监听的端口，其他机器可以通过该端口向本机的`Actor`发送消息。

```php

public static function mainServerCreate(EventRegister $register) {

    // 注册Actor管理器
    $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();
    \EasySwoole\Actor\Actor::getInstance()->register(PlayerActor::class);
    \EasySwoole\Actor\Actor::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)
        ->setListenAddress('0.0.0.0')->setListenPort('9900')->attachServer($server);
        
}
```

### Actor实例管理

服务启动后就可以进行`Actor`的操作，管理本机的`Client`实例，则不需要给`client`传入`$node`参数，默认的`node`为本机，管理其他机器时需要传入。

```php

    // 管理本机的Actor则不需要声明节点
    $node = new \EasySwoole\Actor\ActorNode();
    $node->setIp('127.0.0.1');
    $node->setListenPort(9900);

    // 启动一个Actor并得到ActorId 后续操作需要依赖ActorId
    $actorId = PlayerActor::client($node)->create(['time' => time()]);   // 00101000000000000000001
    // 给某个Actor发消息
    PlayerActor::client($node)->send($actorId, ['data' => 'data']);
    // 给该类型的全部Actor发消息
    PlayerActor::client($node)->sendAll(['data' => 'data']);
    // 退出某个Actor
    PlayerActor::client($node)->exit($actorId, ['arg' => 'arg']);
    // 退出全部Actor
    PlayerActor::client($node)->exitAll(['arg' => 'arg']);
    
```

## 架构解读

### Actor

应该叫`ActorManager`更确切点，它用来注册`Actor`启动`Proxy`和`ActorWorker`进程。

当你在业务逻辑里定义了几种`Actor`，比如`RoomActor`、`PlayerActor`，需要在`SwooleServer`启动时注册它们。

具体就是在`EasySwooleEvent.mainServerCreate`方法中添加如下代码。

```php
$actor = Actor::getInstance();
$actor->register(RoomActor::class);
$actor->register(PlayerActor::class);
$actorConf = Config::getInstance()->getConf('ACTOR_SERVER');
$actor->setMachineId($actorConf['MACHINE_ID'])
    ->setListenAddress($actorConf['LISTEN_ADDRESS'])
    ->setListenPort($actorConf['PORT'])
    ->attachServer($server);
```

其中`ListenAddress`、`ListenPort`为`Proxy`进程的监听地址端口，`MachineId`为`ActorWorker`进程的机器码。

`MachineId`和`IP:PORT`对应。

`attachServer`将开启相应数量的`Proxy`进程，以及前边`register`的`ActorWorker`进程。

#### 工作原理

`Proxy`进程做消息中转，`Worker`进程做消息分发推送。来看个具体的例子：

游戏中玩家P请求进入房间R，抽象成`Actor`模型就是`PlayerActor`需要往`RoomActor`发送请求加入的命令。

那么这时候需要这样写：

```php
\EasySwoole\Actor\Test\RoomActor::client($node)->send($roomActorId, [
	'user_actor_id' => $userActorId,
	'data'	=> '其他进入房间的参数'
])
```

其中`$roomActorId`和`$userActorId`是事先`xxActor::client()->create()`出来的。

上面那段代码的意思就是往`$roomActorId`的`RoomActor`实例推送了一条$`userActorId`玩家的`UserActor`实例要加入房间的消息。

参数`$node`用来寻址`Proxy`，它由目标`Actor`实例的`Worker.MachineId`决定，在本例中就是`$roomActorId`被创建在了哪个`MachineId`的`WorkerProcess`。

通过`$roomActorId`中的机器码找到`IP:PORT`，生成`$node`。

`send`时会创建一个协程`TcpClient`，将消息发送给`Proxy`，然后`Proxy`将消息转发`（UnixClient）`至本机`WorkerProcess`，`WorkerProcess`收到消息，推送到具体的`Actor`实例。

这样就完成了从`PlayerActor`到`RoomActor`的请求通讯，`RoomActor`收到请求消息并处理完成后，向`PlayerActor`回发处理结果，用的是同样的通讯流程。

如果是单机部署，可以忽略`$node`参数，因为所有通讯都是在本机进行。

多机的话，需要自己根据业务来实现`Actor`如何分布和定位。

#### 主要属性

*machineId* 机器码

*proxyNum* 启动几个`ProxyProcess`

*listenPort* 监听`port`

*listenAddress* 监听`ip`

### AbstractActor

`Actor`实例的基类，所有业务中用到的`Actor`都将继承于`AbstractActor。例如游戏场景中的房间，你可以：

```php
class RoomActor extends AbstractActor
```

#### 工作原理

每个`Actor`实例都维护一份独立的数据和状态，当一个`Actor`实例通过`client()->create()`后，会开启协程循环，接收`mailbox pop`的消息，进而处理业务逻辑，更新自己的数据及状态。具体实现就是`__run()`这个方法。

#### 静态方法 configure

用来配置`ActorConfig`，只需要在具体的`Actor`（如`RoomActor`）去重写这个方法就行。

关于`ActorConfig`具体属性可以看下边`ActorConfig`部分。

#### 几个虚拟方法

以下几个虚拟方法需要在`Actor`子类中实现，这几个方法被用在`__run()`中来完成`Actor`的运行周期。

*onStart()* 在协程开启前执行，你可以在此进行`Actor`初始化的一些操作，比如获取房间的基础属性等。

*onMessage()* 当接收到消息时执行，一个`Actor`实例的生命周期基本上就是在收消息-处理-发消息，你需要在这里对消息进行解析处理。

*onExit()* 当接收到退出命令时执行。比如你希望在一个`Actor`实例退出的时候，同时通知某些关联的其他`Actor`，可以在此处理。

#### 其它

*exit()* 用于实例自己退出操作，会向自己发一条退出的命令。

*tick()、after()* 两个定时器，用于`Actor`实例的定时任务，比如游戏房间的定时刷怪`（tick）`；掉线后多长时间自动踢出`（after）`。

*static client()* 用于创建一个`ActorClient`来进行对应`Actor`（实例）的通讯。

### ActorClient

`Actor`通讯客户端，调用`xxActor::client()`来创建一个`ActorClient`进行`Actor`通讯。

上边已经大概讲过了`Actor`的通讯流程，本质就是`TcpClient->ProxyProcess->UnixClient->ActorWorkerProcess->xxActor`。

看下它实现了哪些方法：

*create()* 创建一个`xxActor`实例，返回`actorId`，在之后你可以使用这个`actorId`与此实例进行通讯。

*send()* 指定`actorId`，向其发送消息。

*exit()* 通知`xxActor`退出指定`actorId`的实例。

*sendAll()* 向所有的`xxActor`实例发送消息。

*exitAll()* 退出所有`xxActor`实例。

*exist()* 当前是否存在指定`actorId`的`xxActor`实例。

*status()* 当前`ActorWorker`下`xxActor`的分布状态。

### ActorConfig

具体`Actor`的配置项，比如`RoomActor`、`PlayerActor`都有自己的配置。

*actorName* 一般用类名就可以，注意在同一个服务中这个是不能重复的。

*actorClass* 在`Actor->register()`会将对应的类名写入。

*workerNum* 为`Actor`开启几个进程，`Actor->attachServer()`时会根据这个参数为相应`Actor`启动`WorkerNum`个`Worker`进程。

### ActorNode

上边提到过，`xxActor::client($node)`，这个`$node`就是`ActorNode`对象，属性为`Ip`和`Port`，用于寻址`Proxy`。

### WorkerConfig

`WorkerProcess`的配置项，`WorkerProcess`启动时用到。

*workerId* `worker`进程`Id`，`create Actor`的时候用于生成`actorId`

*machineId* `worker`进程机器码，`create Actor`的时候用于生成`actorId`

*trigger* 异常触发处理接口

### WorkerProcess

`Actor`的重点在这里，每个注册的`Actor`（类）会启动相应数量的`WorkerProcess`。

比如你注册了`RoomActor`、`PlayerActor`，`workerNum`都配置的是3，那么系统将启动3个`RoomActor`的`Worker`进程和3个`PlayerActor`的`Worker`进程。

每个`WorkerProcess`维护一个`ActorList`，你通过`client()->create()`的`Actor`将分布在不同`Worker`进程里，由它的`ActorList`进行管理。

`WorkerProcess`通过协程接收`client`（这个`client`就是`Proxy`做转发时的`UnixClient`）消息，区分消息类型，然后分发给对应的`Actor`实例。

请仔细阅读下`WorkerProcess`的源码，它继承于`AbstractUnixProcess`。

### UnixClient

`UnixStream Socket`，自行了解。`Proxy`转发消息给本机`Actor`所使用的`Client`。

### Protocol

数据封包协议。

### ProxyCommand

消息命令对象，`Actor2`将不同类型的消息封装成格式化的命令，最终传给`WorkerProcess`。

你可以在`ActorClient`中了解一下方法和命令的对应关系，但这个不需要在业务层去更改。

### ProxyConfig

消息代理的配置项。

*actorList* 注册的`actor`列表。

*machineId* 机器码

*tempDir* 临时目录

*trigger* 错误触发处理接口 

### ProxyProcess

`Actor->attachServer()`会启动`proxyNum`个`ProxyProcess`。

用于在`Actor`实例和`WorkerProcess`做消息中转。
