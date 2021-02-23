---
title: Easyswoole框架设计原理 - 启动运行流程
---
# 框架启动流程

## 一、自动加载
我们在执行如下命令的时候：
```bash
php easyswoole server start
```
项目目录下的 `easyswoole` 这个文件，会搜索项目目录下是否存在 `composer` 所生成的 `autoload.php` 这个文件，用于实现 `psr-4` 自动加载，当文件不存在的时候，则终止框架启动。

## 二、基础常量定义
在搜索到了 `autoload.php` 文件后，框架启动脚本则会认定为已经成功注册了 `composer` 的自动加载机制，随后，会根据`autoload.php` 所在的位置，进行如下几个常量的预定义。

- IN_PHAR

    定义规则为 ```defined('IN_PHAR') or define('IN_PHAR', boolval(\Phar::running(false)));```，该常量可以用于判定当前服务是否在 `PHAR` 环境中。
    
- RUNNING_ROOT

    定义规则为 ```defined('RUNNING_ROOT') or define('RUNNING_ROOT', $realCwd);```，该常量可以用于定义当前服务运行的根目录，是一个绝对路径。

- EASYSWOOLE_ROOT
    
    定义规则为 ```defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', IN_PHAR ? \Phar::running() : $realCwd);```，该常量用于定义当前项目所在路径的根目录，是一个绝对路径。
    
## 三、bootstrap 文件引入

`EasySwoole` 启动脚本会判断在项目根目录下，也就是 ```EASYSWOOLE_ROOT.'/bootstrap.php'``` 这个文件是否存在，如果存在，那么则会执行一次 `require_once`。用户可以在框架没有做任何的真实初始化之前，做自己的预处理或者是预定义。(注：3.4.x及以上版本在框架安装时会自动生成一个 `bootstrap` 文件在项目根目录，3.4.x 之前的版本需要用户自行添加。)

## 四、启动命令解析
`EasySwoole` 主框架定义了一个命令容器，完整命名空间为 ```\EasySwoole\EasySwoole\Command\CommandRunner```，这个是一个单例对象，是对 ```\EasySwoole\Command\CommandManager``` 对象的进一步调用封装。在对象的构造函数中，默认注册了 `EasySwoole` 自带的几个命令：
- Crontab
- Install
- PhpUnit
- Process
- Server
- Task

以如下启动命令为例：
```bash
php easyswoole server start -d
```
> -d 可选，为守护启动参数

`CommandRunner` 会执行 `server` 命令的 `start` 行为，其中，`server` 命令的完整实现在 `\EasySwoole\EasySwoole\Command\DefaultCommand\Server`。
#### Server 主命令
`server` 主命令被执行时，做了以下操作：
- 判断是否指定了运行模式
  ```
  $mode = CommandManager::getInstance()->getOpt('mode');
  if(!empty($mode)){
      Core::getInstance()->runMode($mode);
  }
  ```
- 执行框架的初始化
  ```\EasySwoole\EasySwoole\Core::getInstance()->initialize()```

#### Start 行为    
在 `start` 行为中，做了如下两件事：
- 获取配置对象并设置运行时必须参数
- 执行框架的最终启动
  ```\EasySwoole\EasySwoole\Core::getInstance()->createServer()->start();```