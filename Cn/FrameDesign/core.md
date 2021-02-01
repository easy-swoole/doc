---
title: Easyswoole框架设计原理 - 核心文件Core.php解析
---

# Core 
`Core` 是 `EasySwoole` 框架中核心的基础架构对象，这是一个单例对象，它的完整实现在 ```\EasySwoole\EasySwoole\Core```。

## 关键函数
### __construct
在构造函数中，做了以下两件事：
- 常量定义
    - ```defined('SWOOLE_VERSION') or define('SWOOLE_VERSION', intval(phpversion('swoole')));```
    - ``` defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', realpath(getcwd()));```
    - ```defined('EASYSWOOLE_SERVER') or define('EASYSWOOLE_SERVER', 1);```
    - ``` defined('EASYSWOOLE_WEB_SERVER') or define('EASYSWOOLE_WEB_SERVER', 2);```
    - ```defined('EASYSWOOLE_WEB_SOCKET_SERVER') or define('EASYSWOOLE_WEB_SOCKET_SERVER', 3);```
- 全局 `EasySwooleEvent.php` 事件引入

> 在该构造函数中尝试重新定义 `EASYSWOOLE_ROOT` 常量是为了支持用户自定义脚本启动

### runMode
可以通过调用此函数获取框架当前运行的模式，默认模式为 `dev`。可在框架启动时指定模式和要加载的配置文件，详细见 [基础管理命令](/QuickStart/command)。然后在框架任何地方调用此函数以获取当前运行的模式，方便调试。

使用示例：在控制器中调用
```
if (\EasySwoole\EasySwoole\Core::getInstance()->runMode() == 'dev') {
    // 开发模式下执行的逻辑
}
```

### initialize
框架核心骨架初始化，做了以下几件事
- 根据运行模式加载配置文件
- 初始化临时目录和日志目录
- 初始化错误处理器
- 调用全局 `EasySwooleEvent.php` 中的 `initialize` 事件

### createServer
根据配置文件，调用 `ServerManager` 初始化对应的 `Swoole实例`，并执行以下事情：
- 调用全局 `EasySwooleEvent.php` 中的 `mainServerCreate` 事件
- 注册框架系统默认的事件回调
- 附加处理，例如注册 `CronTab` 进程、注册 `Task` 进程等

### start
根据配置文件中设置的服务名称，设置服务名称和进程的名称，调用 `ServerManager`，启动框架。