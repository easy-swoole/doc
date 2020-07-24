---
title: Easyswoole框架设计原理 - 核心文件ServerManager.php解析
---

# ServerManager

ServerManager是EasySwoole框架中，用于全局存储Swoole对象实例所用的一个单例对象。完整的代码实现在```EasySwoole\EasySwoole\ServerManager```。

## 关键函数
### __construct
在构造函数中，ServerManager实例化了一个事件注册器```EasySwoole\EasySwoole\Swoole\EventRegister```，本质上这是一个数组容器。该容器用于存储注册给Swoole实例的事件回调。

### createSwooleServer
该函数会根据所传递的参数与配置项，创建一个Swoole实例，并把该实例赋值存储到ServerManager对象的swooleServer属性中。

### addServer
该函数用于调用Swoole实例的addlistener方法，创建一个Swoole的子服务，并返回该主服务的事件注册器```EventRegister```，注意该注册器的作用域仅仅在对应的子服务中。

### start
该函数用于调用Swoole实例的start方法，也就是实质性的启动一个Swoole服务。该函数做了如下事情：
- 注册主实例的事件回调
- 注册各个子服务的事件回调
- 启动服务

## 其他函数
| 名称 | 参数 | 功能描述 |
| --- | --- | --- |
| getSwooleServer | string $serverName = null | 用于获取当前的Swoole实例或者是对应子服务的subPort对象 |
| getEventRegister | string $serverName = null | 用于获取主Swoole实例或者是对应子服务的subPort对象的事件回调注册容器 |
| isStart | - | 用于判断当前服务是否已经启动 |