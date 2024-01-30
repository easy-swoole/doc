---
title: easyswoole配置文件
meta:
  - name: description
    content: easyswoole提供了非常灵活的全局配置功能，可自行扩展独立的配置文件和进行动态配置。
  - name: keywords
    content: easyswoole配置文件
---


# 配置文件
`EasySwoole` 框架提供了非常灵活自由的全局配置功能，配置文件采用 `PHP` 返回数组方式定义，对于一些简单的应用，无需修改任何配置，对于复杂的要求，还可以自行扩展自己独立的配置文件和进行动态配置。  
框架安装完成后系统默认的全局配置文件是项目根目录下的 `produce.php` 、 `dev.php` 文件，(在 `3.1.2` 版本之前是 `dev.env`、`produce.env`)，`3.4.x` 版本(最新版)支持在启动 `EasySwoole` 框架时以指定的配置文件( `dev.php` / `produce.php`)运行，详细启动命令请看 [基本管理命令章节](/QuickStart/command.md)。

配置文件内容如下:
```php
<?php

return [
    // 服务名称
    'SERVER_NAME'   => "EasySwoole",
    'MAIN_SERVER'   => [
        // 监听地址
        'LISTEN_ADDRESS' => '0.0.0.0',
        // 监听端口
        'PORT'           => 9501,
        // 可选 EASYSWOOLE_SERVER,EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SERVER_TYPE'    => EASYSWOOLE_WEB_SERVER, 
        // 可选 SWOOLE_TCP SWOOLE_TCP6 SWOOLE_UDP SWOOLE_UDP6 SWOOLE_UNIX_DGRAM SWOOLE_UNIX_STREAM
        'SOCK_TYPE'      => SWOOLE_TCP,
        // 默认 Server 运行模式
        'RUN_MODEL'      => SWOOLE_PROCESS,
        // Swoole_Server 运行配置（ 完整配置可见[Swoole 文档](http://swoole.easyswoole.com/ServerStart/Tcp/method.html) 的 mode 参数 ）
        'SETTING'        => [
            // 运行的 worker 进程数量
            'worker_num'            => 8,
            // 设置异步重启开关。设置为 true 时，将启用异步安全重启特性，Worker 进程会等待异步事件完成后再退出。
            'reload_async'          => true,
            // 开启后自动在 onTask 回调中创建协程
            'task_enable_coroutine' => true,
            'max_wait_time'         => 3,
            // (可选参数）使用 http 上传大文件时可以进行配置
            // 'package_max_length' => 100 * 1024 * 1024, // 即 100 M
            
            // (可选参数) 允许处理静态文件 html 等，详细请看 http://swoole.easyswoole.com/ServerStart/Http/serverSetting.html
            // 'document_root' => '/easyswoole/public',
            // 'enable_static_handler' => true,
        ],
        // 设置 EasySwoole 框架独立实现的 Task 任务组件的配置
        'TASK'=>[
            'workerNum'     => 4,
            'maxRunningNum' => 128,
            'timeout'       => 15
        ]
    ],
    // 临时文件存放的目录
    'TEMP_DIR'      => null,
    
    ### 日志相关配置 (目前最新)EasySwoole 3.4.4 及以后版本支持
    "LOG" => [
        // 设置记录日志文件时日志文件存放目录
        'dir' => null,
        // 设置记录日志时的日志最低等级，低于此等级的日志不进行记录和显示
        'level' => \EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG,
        // 设置日志处理器 `handler` (handler)
        'handler' => null,
        // 设置开启控制台日志记录到日志文件
        'logConsole' => true,
        // 设置开启在控制台显示日志
        'displayConsole'=>true,
        // 设置打印日志时忽略哪些分类的日志不进行记录
        'ignoreCategory' => []
    ],
    
    ### 日志相关配置 EasySwoole 3.4.3 版本支持
    // "LOG" => [
    //    'dir' => null,
    //    'level' => \EasySwoole\Log\LoggerInterface::LOG_LEVEL_DEBUG,
    //    'handler' => null,
    // ],
    
    
    ### 日志相关配置 EasySwoole 3.4.3 之前支持
    // 日志文件存放的目录
    // 'LOG_DIR'       => null,
];
```

> 以上配置关于日志的相关配置的说明，详细请看 [日志](/BaseUsage/log.md) 章节。

上述参数补充说明：
- MAIN_SERVER.SERVER_TYPE: 
    - EASYSWOOLE_WEB_SERVER: 表示框架主服务为 `Http` 服务(框架默认提供的服务类型)
    - EASYSWOOLE_SERVER: 表示框架主服务为 `Tcp` 服务
    - EASYSWOOLE_WEB_SOCKET_SERVER: 表示框架主服务为 `WebSocket` 服务

::: warning 
  EASYSWOOLE_SERVER、EASYSWOOLE_WEB_SOCKET_SERVER类型，都需要在 `EasySwooleEvent.php` 的 `mainServerCreate` 事件中自行设置回调( `receive` 或 `message` )，否则将出错。具体设置对应的回调的方式请参考 [Tcp 服务章节](/Socket/tcp.md) 和 [WebSocket 服务章节](/Socket/webSocket.md)。关于同时支持多个服务的使用也请查看 [Tcp 服务章节](/Socket/tcp.md) 和 [WebSocket 服务章节](/Socket/webSocket.md)。
:::

::: warning 
  注意：目前框架 `3.4.x` 的配置驱动默认为 `SplArray`，自定义配置驱动可查看本文最后章节。
:::

::: tip
 注意：`EasySwoole 3.4.x` 之前版本的配置驱动使用的是 `\Swoole\Table`，由于 `swoole_table` 的特殊特性，不适合存储大量/大长度的配置，如果是存储支付秘钥、签名等大长度字符串，建议使用类常量方法定义，而不是通过 `dev.php` 存储。如果你必须用配置文件存储，请看本文下文的自定义 `config` 驱动。
:::


## 配置操作类
配置操作类为 `\EasySwoole\EasySwoole\Config` 类，使用方式非常简单，具体请看下面的代码示例，操作类还提供了 `load` 方法重载全部配置，基于这个方法，可以自己定制更多的高级操作。

::: warning 
  设置和获取配置项都支持点语法分隔，具体请看下面获取配置的代码示例
:::

```php
<?php

$instance = \EasySwoole\EasySwoole\Config::getInstance();

// 获取配置 按层级用点号分隔
$instance->getConf('MAIN_SERVER.SETTING.task_worker_num');

// 设置配置 按层级用点号分隔
$instance->setConf('DATABASE.host', 'localhost');

// 获取全部配置
$conf = $instance->getConf();

// 用一个数组覆盖当前配置项
$conf['DATABASE'] = [
    'host' => '127.0.0.1',
    'port' => 13306
];
$instance->load($conf);
```

::: warning 
  需要注意的是 `由于进程隔离的原因`，在 `Server` 启动后，动态新增修改的配置项，只对执行操作的进程生效，如果需要全局共享配置需要自己进行扩展
:::

## 添加用户配置项

每个用户都有自己的配置项，添加自己的配置项非常简单，其中一种方法是直接在配置文件中添加即可，如下面的例子:
下面示例中添加了自定义的 `MySQL` 和 `Redis` 配置。

```php
<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, // 可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time'=>3,
            'document_root'            => EASYSWOOLE_ROOT . '/Static',
            'enable_static_handler'    => true,
        ],
        'TASK'=>[
            'workerNum'=>0,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,
    
    
    // 添加 MySQL 及对应的连接池配置
    /*################ MYSQL CONFIG ##################*/
    'MYSQL' => [
        'host'          => '127.0.0.1', // 数据库地址
        'port'          => 3306, // 数据库端口
        'user'          => 'root', // 数据库用户名
        'password'      => 'root', // 数据库用户密码
        'timeout'       => 45, // 数据库连接超时时间
        'charset'       => 'utf8', // 数据库字符编码
        'database'      => 'easyswoole', // 数据库名
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => false, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 20, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],

    // 添加 Redis 及对应的连接池配置
    /*################ REDIS CONFIG ##################*/
    'REDIS' => [
        'host'          => '127.0.0.1', // Redis 地址
        'port'          => '6379', // Redis 端口
        'auth'          => 'easyswoole', // Redis 密码
        'timeout'       => 3.0, // Redis 操作超时时间
        'reconnectTimes' => 3, // Redis 自动重连次数
        'db'            => 0, // Redis 库
        'serialize'     => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE, // 序列化类型，默认不序列化
        'packageMaxLength' => 1024 * 1024 * 2, // 允许操作的最大数据
        // 配置 Redis 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 20, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],
];
```

## 生产与开发配置分离
在 `php easyswoole server start` 命令下，默认为开发模式，加载 `dev.php` (3.1.2 之前为 `dev.env`)
运行 `php easyswoole server start -mode=produce` 命令时，为生产模式，加载 `produce.php` (3.1.2 之前为 `produce.env`)

::: tip
旧版本 EasySwoole (3.4.x 以前的版本)，在 `php easyswoole start` 命令下，默认为开发模式，加载 `dev.php` (3.1.2 之前为 `dev.env`)。运行 `php easyswoole start produce` 命令时，为生产模式，加载 `produce.php` (3.1.2 之前为 `produce.env`)
:::

## DI 注入配置
`EasySwoole 3.x` 提供了几个 `Di` 参数配置，可自定义配置脚本错误异常处理回调、控制器命名空间、最大解析层级等。
```php
<?php
// 配置错误处理回调
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::ERROR_HANDLER, function () {
});

// 配置脚本结束回调
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::SHUTDOWN_FUNCTION, function () {
});

// 配置控制器命名空间
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_NAMESPACE, 'App\\HttpController\\');

// 配置 HTTP 控制器最大解析层级
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_MAX_DEPTH, 5);

// 配置http控制器异常回调
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_EXCEPTION_HANDLER, function () {});

// HTTP 控制器对象池最大数量
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_POOL_MAX_NUM, 15);
```

## 自定义 Config 驱动
`EasySwoole` 在 `3.2.5` 版本后，默认配置驱动存储从 `SplArray` 改为了 `swoole_table`，修改配置之后，所有进程同时生效。在 `3.4.x` 版本后，由于特殊原因，默认配置驱动存储又从 `swoole_table` 改为了 `SplArray`，修改配置之后，仅在当前进程生效。所以在 `3.2.5` ~ `3.3.7` 版本中，默认配置驱动存储为 `swoole_table`。

### AbstractConfig

`\EasySwoole\Config\AbstractConfig` 抽象类提供了以下几个方法，用于给其他 `config` 驱动继承：

- isDev()
  该方法在 `3.4.x` 版本中已移除，请用 `\EasySwoole\EasySwoole\Core::getInstance()->runMode() == 'dev'` 判断是否为开发环境。
  (在 `3.4.x` 之前版本可通过该方法获得当前运行环境是否为开发环境)
 
- abstract function getConf($key = null);
  获取一个配置
  
- abstract function setConf($key, $val): bool;
  设置一个参数
  
- abstract function load(array $array): bool;
  重新加载配置项
  
- abstract function merge(array $array): bool;
  合并配置项
  
- abstract function clear(): bool;
  清除所有配置项
  
### 自定义配置存储驱动

在 `EasySwoole` 中，自带了 `SplArray` 和 `swoole_table` 驱动实现，可自行查看源码进行深入了解。 

目前最先版本默认驱动为 `SplArray`。 

如需要修改配置存储驱动，配置步骤如下:  

* 继承 `AbstractConfig` 实现各个方法
* 在 [Bootstrap 事件](/FrameDesign/event/bootstrap.md) 事件中修改 `config` 驱动(直接在 `bootstrap.php` 文件中加入如下代码即可)

```php 
<?php
\EasySwoole\EasySwoole\Config::getInstance(new \EasySwoole\Config\SplArrayConfig());
```

::: warning
  由于 `bootstrap 事件` 是由 `EasySwoole` 启动脚本执行，当你需要写 `cli` 脚本需要初始化 `EasySwoole` 框架基础组件时，需要自行引入 `bootstrap.php` 文件。
:::

### 动态配置问题
由于 `swoole` 是多进程的，如果使用 `SplArray` 方式存储，在单个进程修改配置后，其他进程将不会生效，使用`swoole_table` 方式的则会全部生效，需要特别注意。

::: tip
  在 `EasySwoole 3.4.x` 之前版本，框架采用 `swoole_table` 作为默认配置驱动存储。所以当你在控制器( `worker` 进程)中修改某一项配置时，是从 `swoole_table` 直接操作，所有进程都可以使用。但是在目前最新版本中默认配置驱动存储变成了 `SplArray`，在单个进程修改配置后，由于进程隔离原因，其他进程将不会生效，需要特别注意。
:::

## 其他

- QQ 交流群
    - VIP 群 579434607 （本群需要付费 599 元）
    - EasySwoole 官方一群 633921431(已满)
    - EasySwoole 官方二群 709134628(已满)
    - EasySwoole 官方三群 932625047(已满)
    - EasySwoole 官方四群 779897753(已满)
    - EasySwoole 官方五群 853946743(已满) 
    - EasySwoole 官方六群 524475224
    
- 商业支持：
    - QQ 291323003
    - EMAIL admin@fosuss.com
        
- 作者微信

     ![](/Images/authWx.png)
    
- [捐赠](/Preface/donate.md)
    您的捐赠是对 `EasySwoole` 项目开发组最大的鼓励和支持。我们会坚持开发维护下去。 您的捐赠将被用于:
        
  - 持续和深入地开发
  - 文档和社区的建设和维护
  
- `EasySwoole` 的文档使用 `EasySwoole 框架` 提供服务，采用 `MarkDown 格式` 和自定义格式编写，若您在使用过程中，发现文档有需要纠正 / 补充的地方，请 `fork` 项目的文档仓库，进行修改补充，提交 `Pull Request` 并联系我们。
