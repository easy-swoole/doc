# 框架更新记录
> 框架更新记录仅仅整理自2020年10-24后的记录，其余记录以老版本文档或github记录为准。

## 3.7.1 - 2023-03-13

### 变更

- 调整框架依赖的核心组件 `spl` 组件版本 为 `^2.0`
- 移除框架内部对注解 `doc` 命令的支持，调整为由依赖的 `http` 注解组件负责提供 `doc` 命令，用于基于注解生成 `api` 文档

## 3.6.3 - 2023-02-22

### 变更

- 调整框架的日志目录名跟随运行模式
- 调整 `swoole` 的 `worker` 进程名称、进程组名称跟随运行模式

## 3.6.2 - 2023-01-12

### 优化

- 修复在 `swoole-cli`、`CYGWIN` 等环境时设置进程名称不兼容的问题

### 变更

- 调整框架依赖的 `task` 组件版本为 `^2.0`

## 3.6.1 - 2022-09-28

### 变更

- 调整框架启动监听的 `socket` 文件名称跟随运行模式（包括 `pid` 文件、`swoole.log`、`bridge.sock` 等）
- 调整框架依赖的核心组件 `socket` 组件的版本
- 调整框架依赖的 `php` 版本为 `^8.0`
- 调整框架依赖的核心组件 `http` 注解组件版本为 `3.x`
- 调整框架的启动文件由原来的 `easywoole` 变更为 `easyswoole.php`

### 新增

- 新增自动获取 `Swoole` 的 `EventRegister` 的 `key`

### 优化

- 优化 `logger` 组件 `waring` 方法名命名不规范问题
- 优化 `crontab` 列表查看命令 `php easyswoole crotab show` 的异常提示

## 3.5.1 - 2022-01-18

### 修复

- 修复框架启动脚本不兼容 `composer` `2.2.0` 版本问题

## 3.4.6 - 2021-06-13

### 修复

- 修复定时任务在极端情况下多次执行的问题

## 3.4.5 - 2021-04-26 

### 修复

- 修复 `trigger` 不记录日志问题

### 变更

- 移除框架主库 `phpunit/phpunit` 组件，改为 `easyswoole/phpunit` 组件。

## 3.4.4 - 2021-03-08 

### 优化

- 优化完善默认的日志处理机制，详细请看 [配置文件 章节](/QuickStart/config.md)
- 集成 `http-annotatiaon 2.x` 的注解文档生成命令，用户可以方便地使用命令生成 `api` 文档

## 3.4.3 - 2021-02-23

### 新增

- 新增 `EASYSWOOLE_RUNNING` 常量 

### 优化

- 调整依赖的 `http-annotation 注解组件` 为 `2.x`
- 优化对日志等级(level)的设置，详细请看 [配置文件 章节](/QuickStart/config.md)
- 优化用户可在配置文件中配置日志处理器，详细请看 [日志 章节](/BaseUsage/log.md)
- 优化调整`Crontab`定时任务组件状态显示、执行.

### 移除

- 废弃 `SysConst::EXECUTE_COMMAND` 常量

## 3.4.2 - 2020-12-22

### 优化

- `task`优化,更加灵活的配置.
- `crontab`优化,避免极端情况下任务不执行.
- `http-dispatcher`优化,更加灵活的注册路由.

## 3.4.1 - 2020-11-18

### 新增

- 增加`SysConst::EXECUTE_COMMAND`,此常量可获取主框架内部所执行的`command`.
- `install`命令函数检查`symlink`和`readlink`。

### 修复

- 修复`worker`异常退出，没有清理`table`信息。

### 移除

- 移除 `command`及 `bridge` 中动态配置 `config` 的功能，因为 `3.4.x` 的配置的存储不是使用 `swoole-table` 来存储。

## 3.4.0 - 2020-10-24

与`3.3.x`不兼容，需进行调整.

`3.3.x -> 3.4.x`需要重新执行`php vendor/bin/easyswoole install`.

### 新增

- `Core::getInstance()->runMode();`方法. 可通过此方法修改运行文件,默认`dev`,也可以通过`command`进行修改.

### 变更

- [command](/QuickStart/command.md)命令变更.

- 自定义`command`需进行[调整](https://github.com/easy-swoole/command).

- `config`从`swoole-table`改为`splArray`,用户可自行调整.

- `onRequest`及`afterRequest`全局事件
变更为(`initialize`注册即可):
```php
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, callback);
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, callback);
```
`callback`为回调函数,注入参数为：
```php
function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response){}
```
`onRequest`事件需要返回`bool`,来决定程序是否继续进行`dispatcher`.

### 移除

- 移除`EasySwooleEvent`中`onRequest`及`afterRequest`全局事件.

- 移除`Core::getInstance()->isDev();`方法.

- 移除`Core::getInstance()->globalInitialize();`,可自行调用`EasySwooleEvent::initialize()`.
