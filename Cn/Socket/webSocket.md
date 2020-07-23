---
title: 用php基于swoole实现websocket通讯的聊天室
meta:
  - name: description
    content: 用php基于swoole实现websocket通讯的聊天室
  - name: keywords
    content: easyswoole websocket服务|swoole websocket|swoole即时通讯|swoole聊天室|php websocket|php聊天室
---
# WebSocket

## 新人帮助

* 本文遵循PSR-4自动加载类规范，如果你还不了解这个规范，请先学习相关规则。
* 本节基础命名空间App 默认指项目根目录下App文件夹，如果你的App指向不同，请自行替换。
* 只要遵循PSR-4规范，无论你怎么组织文件结构都没问题，本节只做简单示例。

## 安装

首先引入 `easyswoole/socket` composer 包
> composer require easyswoole/socket

::: warning
socket包支持独立使用，但本文档以`EasySwoole`框架内使用编写 
:::

## WebSocket控制器

socket包 支持以控制器模式来处理你的消息。

首先，修改项目根目录下配置文件dev.php(如果是生产环境则是produce.php)，修改SERVER_TYPE为:
```php
# WebSocket Server 是Http Server 的子类，完全支持Http Server的功能
'SERVER_TYPE'    => EASYSWOOLE_WEB_SOCKET_SERVER,
```

::: warning   
消息解析是指，将消息按照预定的规则进行解析，使得其可以按照`Controller` `Action` 的模式去执行代码;
:::

## 实现命令解析

**创建App/WebSocket/WebSocketParser.php文件，写入以下代码**

```php
<?php
namespace App\WebSocket;

use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * Class WebSocketParser
 *
 * 此类是自定义的 websocket 消息解析器
 * 此处使用的设计是使用 json string 作为消息格式
 * 当客户端消息到达服务端时，会调用 decode 方法进行消息解析
 * 会将 websocket 消息 转成具体的 Class -> Action 调用 并且将参数注入
 *
 * @package App\WebSocket
 */
class WebSocketParser implements ParserInterface
{
    /**
     * decode
     * @param  string         $raw    客户端原始消息
     * @param  WebSocket      $client WebSocket Client 对象
     * @return Caller         Socket  调用对象
     */
    public function decode($raw, $client) : ? Caller
    {
        /**
         * 当消息到达 Server时，会首先调用此方法进行 decode
         * 示例使用 json 作为默认的消息格式，如果你使用其他的格式，在这里解析即可
         */
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            echo "decode message error! \n";
            return null;
        }

        // new 调用者对象
        $caller =  new Caller();
        /**
         * 设置被调用的类 这里会将ws消息中的 class 参数解析为具体想访问的控制器
         * 如果更喜欢 event 方式 可以自定义 event 和具体的类的 map 即可
         * 注 目前 easyswoole 3.0.4 版本及以下 不支持直接传递 class string 可以通过这种方式
         */
        $class = '\\App\\WebSocket\\'. ucfirst($data['class'] ?? 'Index');
        $caller->setControllerClass($class);

        // 提供一个事件风格的写法
        // $eventMap = [
        //     'index' => Index::class
        // ];
        // $caller->setControllerClass($eventMap[$data['class']] ?? Index::class);

        // 设置被调用的方法
        $caller->setAction($data['action'] ?? 'index');
        // 检查是否存在args
        if (!empty($data['content'])) {
            // content 无法解析为array 时 返回 content => string 格式
            $args = is_array($data['content']) ? $data['content'] : ['content' => $data['content']];
        }

        // 设置被调用的Args
        $caller->setArgs($args ?? []);
        return $caller;
    }
    /**
     * encode
     * @param  Response     $response Socket Response 对象
     * @param  WebSocket    $client   WebSocket Client 对象
     * @return string             发送给客户端的消息
     */
    public function encode(Response $response, $client) : ? string
    {
        /**
         * 这里对返回给客户端的信息进行最后一次处理
         * 假如你需要对消息进行加密或是序列化，在这里处理即可
         * 这里建议只进行消息的encode处理，具体的状态码应当在Controller处理
         */
        return $response->getMessage();
    }
}

```

## 注册服务

**新人提示**

::: warning 
如果你尚未明白easyswoole运行机制，那么这里你简单理解为，当easyswoole运行到一定时刻，会执行以下方法。
:::


**在根目录下EasySwooleEvent.php文件mainServerCreate方法下加入以下代码**

```php
<?php
//注意：在此文件引入以下命名空间
use EasySwoole\Socket\Dispatcher;
use App\WebSocket\WebSocketParser;

public static function mainServerCreate(EventRegister $register): void
{
    /**
     * **************** websocket控制器 **********************
     * 这里需要创建一个 Dispatcher 并将我们编写的 Parser 规则进行注入
     * 同时在 onMessage 事件中转发给 Dispatcher 进行处理
     */
    // 创建一个 Dispatcher 配置
    $conf = new \EasySwoole\Socket\Config();
    // 设置 Dispatcher 为 WebSocket 模式
    $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
    // 设置解析器对象
    $conf->setParser(new WebSocketParser());
    // 创建 Dispatcher 对象 并注入 config 对象
    $dispatch = new Dispatcher($conf);
    // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
    $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
        $dispatch->dispatch($server, $frame->data, $frame);
    });
}
```

## WebSocket 控制器

::: warning 
WebSocket控制器必须继承EasySwoole\Socket\AbstractInterface\Controller;
:::

**创建App/WebSocket/Index.php文件，写入以下内容**

```php
<?php

namespace App\WebSocket;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Socket\AbstractInterface\Controller;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Index extends Controller
{
    /**
     * 此方法会返回 Client 发送的 content 参数
     * 这里的 $this->caller 是 ParserInterface->decode() 时返回的 caller 对象
     */
    public function hello()
    {
        $this->response()->setMessage('call hello with arg:'. json_encode($this->caller()->getArgs()));
    }

    /*
     * 此方法会返回 当前Client 的fd
     * 在swoole中 fd 代表当前Client 对应的文件描述符 ID，关于fd的具体解释参加下面的文档
     * @buy https://github.com/swoole/swoole-wiki/blob/master/doc/2.7.2%20-%20%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0%E4%B8%AD%E7%9A%84%20reactor_id%20%E5%92%8C%20fd.md
     */
    public function who(){
        $this->response()->setMessage('your fd is '. $this->caller()->getClient()->getFd());
    }

    /**
     * 此方法会立刻 向客户端发送 `this is delay action`
     * 并且使用异步推送的方式，在随后的5秒内每秒向客户端发送一次消息
     */
    public function delay()
    {
        $this->response()->setMessage('this is delay action');
        $client = $this->caller()->getClient();

        /**
         * 异步推送
         * swoole 向客户端发送消息主要依赖的是客户端的fd，所以这里直接use fd也可以
         * 此function 依赖 `easyswoole/task` 组件，easyswoole框架默认安装
         * 如果你使用原生swoole 或其他框架，请自行 require 或使用其他方案
         */
        TaskManager::getInstance()->async(function () use ($client){
            $server = ServerManager::getInstance()->getSwooleServer();
            $i = 0;
            while ($i < 5) {
                sleep(1);
                $server->push($client->getFd(),'push in http at '. date('H:i:s'));
                $i++;
            }
        });
    }
}
```


::: warning 
该控制器使用了[task组件](task.html) 如果你使用原生swoole 请自行 `composer require easyswoole/task`
:::

## 测试

*如果你按照本文配置，那么你的文件结构应该是以下形式*

```
App
└── WebSocket
    ├── Index.php
    └── WebSocketParser.php
```

首先我们在项目根目录启动easyswoole

```shell
php easyswoole start
```

如果没有错误此时已经启动了easyswoole服务;  
此时可以使用eeasyswoole 提供的WebSocket调试工具:[WEBSOCKET CLIEN](http://www.easyswoole.com/wstool.html)；

### 连接

在 `wstool` 工具中的 `服务地址` 中填写 `ws://服务器IP:注册端口`，如果你使用本地调试，并且使用默认端口那么地址为：`ws://127.0.0.1:9501` 如果你使用虚拟机或者Docker进行开发，则需要填写其内网地址，如果使用远程服务器进行开发，则需要填写对应的外网地址。

然后点击 `开启连接` 如果没有发生错误就说明你和服务端已经建立了 websocket 连接

::: warning 
如果你使用虚拟机或Docker或远程服务器进行开发，请务必记得开放防火墙其使用的端口，尤其是使用云服务器进行开发的，还需要在安全组开放端口。
:::

### 消息结构

在此示例中，我们在 `Parser` 使用的消息结构如下：

```json
{
    "class":"Index",
    "action":"hello",
    "content":{
        "key":"value"
    }
}
```

- `class` 代表希望执行的 `Controller`
    - 示例中此参数为空时会默认为 `Index`
    - 示例中默认前缀`namespace`为：`App\WebSocket\` 如果你想要自定义，则需要自己修改 `Parser` 相关代码。
- `action` 代表希望执行的 `action`
    - 示例中此参数为空时会默认为 `index`
- `content` 代表携带的参数，可以是key-value 形式，也可以是 list形式
    - 示例中，如果这里提交为`string`时则会返回 `content: string` 的key-value形式
    

### 消息测试

此时在 `wstool` 页面中 `发送到服务端内容` 框中填写以下消息：
```json
{
    "class":"Index",
    "action":"hello",
    "content":{
        "name":"eayswoole",
        "age": 3
    }
}
```
此消息代表执行`App\WebSocket\Index` Controller 中的 `hello` 方法


发送后如果正常工作的话，你会收到以下消息：
> call hello with arg:{"name":"eayswoole","age":3}


```json
{
    "class":"Index",
    "action":"who"
}
```
此消息代表执行`App\WebSocket\Index` Controller 中的 `who` 方法


发送后如果正常工作的话，你会收到以下消息(这里fd不一定为1)：
> your fd is 1


```json
{
    "class":"Index",
    "action":"delay"
}
```
此消息代表执行`App\WebSocket\Index` Controller 中的 `delay` 方法


发送后如果正常工作的话，你会立刻收到以下消息：
> this is delay action

并在随后的5秒内每秒收到一次消息：
> push in http at 07:50:45
> push in http at 07:50:46
> push in http at 07:50:47
> push in http at 07:50:48
> push in http at 07:50:49

至此你已经基本掌握了 `socket` 中websocket部分的使用

::: tip
 **当然这里是举例，你可以根据自己的业务场景进行设计消息结构**
:::
