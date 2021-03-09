---
title: easyswoole 微服务-Rpc
meta:
  - name: description
    content: EasySwoole中用RPC实现分布式微服务架构
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|easyswoole|Rpc服务端|swoole RPC|swoole微服务|swoole分布式|PHP 分布式
---

# EasySwoole RPC

很多传统的 `Phper` 并不懂 `RPC` 是什么，`RPC` 全称 `Remote Procedure Call`，中文译为 `远程过程调用`，其实你可以把它理解为是一种架构性上的设计，或者是一种解决方案。

例如在某庞大商场系统中，你可以把整个商场拆分为 `N` 个微服务（理解为 `N` 个独立的小模块也行），例如：
    
- 订单系统
- 用户管理系统
- 商品管理系统
- 等等 

那么在这样的架构中，就会存在一个 `API 网关` 的概念，或者是叫 `服务集成者`。我的 `API 网关` 的职责，就是把一个请求，拆分成 `N` 个小请求，分发到各个小服务里面，再整合各个小服务的结果，返回给用户。例如在某次下单请求中，那么大概发送的逻辑如下：
- API 网关接受请求
- API 网关提取用户参数，请求用户管理系统，获取用户余额等信息，等待结果
- API 网关提取商品参数，请求商品管理系统，获取商品剩余库存和价格等信息，等待结果
- API 网关融合用户管理系统、商品管理系统的返回结果，进行下一步调用（假设满足购买条件）
- API 网关调用用户管理信息系统进行扣款，调用商品管理系统进行库存扣减，调用订单系统进行下单（事务逻辑和撤回可以用 `请求 id` 保证，或者自己实现其他逻辑调度）
- API 网关返回综合信息给用户

而在以上发生的行为，就称为 `远程过程调用`。而调用过程实现的通讯协议可以有很多，比如常见的 `HTTP` 协议。而 `EasySwoole RPC` 采用自定义短链接的 `TCP` 协议实现，每个请求包，都是一个 `JSON`，从而方便实现跨平台调用。

## 全新特性
 - 协程调度
 - 服务自动发现
 - 服务熔断
 - 服务降级
 - Openssl 加密
 - 跨平台、跨语言支持
 - 支持接入第三方注册中心

## 安装

> composer require easyswoole/rpc=4.x

## 执行流程

服务端：  
注册RPC服务，创建相应的服务swoole table表（ps:记录调用成功和失败的次数） 
注册worker,tick进程  
  
woker进程监听：   
客户端发送请求->解包成相对应的格式->执行对应的服务->返回结果->客户端  

tick进程：  
注册定时器发送心跳包到本节点管理器   
启用广播：每隔几秒发送本节点各个服务信息到其他节点  
启用监听：监听其他节点发送的信息，发送相对应的命令（心跳|下线）到节点管理器处理  
进程关闭：主动删除本节点的信息，发送下线广播到其他节点  

![](/Images/Passage/rpcDesign.png)


# Rpc-Server

## 场景
例如在一个商场系统中，我们将商品库和系统公告两个服务切分开到不同的服务器当中。当用户打开商场首页的时候，
我们希望App向某个网关发起请求，该网关可以自动的帮我们请求商品列表和系统公共等数据，合并返回。

## 服务定义

每一个Rpc服务其实就一个EasySwoole\Rpc\AbstractService类。 如下：

## 定义商品服务

```php
namespace App\RpcService;


use EasySwoole\Rpc\AbstractService;

class Goods extends AbstractService
{
    
    /**
     *  重写onRequest(比如可以对方法做ip拦截或其它前置操作)
     *
     * @param string $action
     * @return bool
     * CreateTime: 2020/6/20 下午11:12
     */
    protected function onRequest(?string $action): ?bool
    {
        return true;
    }

    public function serviceName(): string
    {
        return 'goods';
    }

    public function list()
    {
        $this->response()->setResult([
            [
                'goodsId'=>'100001',
                'goodsName'=>'商品1',
                'prices'=>1124
            ],
            [
                'goodsId'=>'100002',
                'goodsName'=>'商品2',
                'prices'=>599
            ]
        ]);
        $this->response()->setMsg('get goods list success');
    }
}
```

## 定义公共服务

```php
namespace App\RpcService;


use EasySwoole\Rpc\AbstractService;

class Common extends AbstractService
{
    public function serviceName(): string
    {
        return 'common';
    }

    public function mailBox()
    {
        $this->response()->setResult([
            [
                'mailId'=>'100001',
                'mailTitle'=>'系统消息1',
            ],
            [
                'mailId'=>'100001',
                'mailTitle'=>'系统消息1',
            ],
        ]);
        $this->response()->setMsg('get mail list success');
    }

    public function serverTime()
    {
        $this->response()->setResult(time());
        $this->response()->setMsg('get server time success');
    }
}
```

## 服务注册

在`Easyswoole`全局的`Event`文件中，进行服务注册。至于节点管理、服务类定义等具体用法请看对应章节。

```php
namespace EasySwoole\EasySwoole;

use App\RpcService\Common;
use App\RpcService\Goods;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Config as RpcConfig;
use EasySwoole\Rpc\Rpc;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /*
         * 定义节点Redis管理器
         */
        $redisPool = new RedisPool(new RedisConfig([
            'host'=>'127.0.0.1'
        ]));
        $manager = new RedisManager($redisPool);
        //配置Rpc实例
        $config = new RpcConfig();
        //这边用于指定当前服务节点ip，如果不指定，则默认用UDP广播得到的地址
        $config->setServerIp('127.0.0.1');
        $config->setNodeManager($manager);
        /*
         * 配置初始化
         */
        Rpc::getInstance($config);
        //添加服务
        Rpc::getInstance()->add(new Goods());
        Rpc::getInstance()->add(new Common());
        Rpc::getInstance()->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
```

> 为了方便测试，我把两个服务放在同一台机器中注册。实际生产场景应该是N台机注册商品服务，N台机器注册公告服务，把服务分开。


# Rpc-Client

## 控制器聚合调用

```php
namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Rpc\Response;
use EasySwoole\Rpc\Rpc;

class Index extends Controller
{

    function index()
    {
        $ret = [];
        $client = Rpc::getInstance()->client();
        /*
         * 调用商品列表
         */
        $client->addCall('goods','list',['page'=>1])
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['goods'] = $response->toArray();
            })->setOnFail(function (Response $response)use(&$ret){
                $ret['goods'] = $response->toArray();
            });
        /*
         * 调用信箱公共
         */
        $client->addCall('common','mailBox')
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['mailBox'] = $response->toArray();
            })->setOnFail(function (Response $response)use(&$ret){
                $ret['mailBox'] = $response->toArray();
            });
        /*
        * 获取系统时间
        */
        $client->addCall('common','serverTime')
            ->setOnSuccess(function (Response $response)use(&$ret){
                $ret['serverTime'] = $response->toArray();
            });

        $client->exec(2.0);

        $this->writeJson(200,$ret);
    }
}
```

> 注意，控制器中可以这样调用，是因为服务端章节中，在EasySwoole的全局启动事件已经对当前的Rpc实例定义注册了节点管理器。因此在控制器中调用的时候
> 该Rpc实例可以找到对应的节点。一般来说，在做聚合网关的节点，是不需要注册服务进去的，仅需注册节点管理器即可。

## 客户端

> 当rpc服务和客户端不在同一服务中时，并且服务端客户端使用的都是es

````php
<?php
require_once 'vendor/autoload.php';

use EasySwoole\Rpc\Config;
use EasySwoole\Rpc\Rpc;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Response;
$redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
$redisConfig->setHost('127.0.0.1'); // 服务端使用的redis节点地址
$redisConfig->setPort('6379'); // 服务端使用的redis节点端口
$pool=new \EasySwoole\RedisPool\RedisPool($redisConfig);
$config = new Config();
$config->setServerIp('127.0.0.1'); // 指定rpc服务地址
$config->setListenPort(9502); // 指定rpc服务端口
$config->setNodeManager(new RedisManager($pool));
$rpc = new Rpc($config);

\Swoole\Coroutine::create(function () use ($rpc) {
    $client = $rpc->client();
    $client->addCall('UserService', 'register', ['arg1', 'arg2'])
        ->setOnFail(function (Response $response) {
            print_r($response->toArray());
        })
        ->setOnSuccess(function (Response $response) {
            print_r($response->toArray());
        });

    $client->exec();
});
swoole_timer_clear_all();
````


# 跨平台

`Rpc`的请求响应通过`tcp`协议,服务广播使用`udp`协议,我们只需要实现网络协议即可。

## PHP示例代码

````php
<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2019/6/17
 * Time: 14:30
 */
$data = [
    'command' => 1,//1:请求,2:状态rpc 各个服务的状态
    'request' => [
        'serviceName' => 'UserService',
        'action' => 'register',//行为名称
        'arg' => [
            'args1' => 'args1',
            'args2' => 'args2'
        ]
    ]
];

//$raw = serialize($data);//注意序列化类型,需要和RPC服务端约定好协议 $serializeType

$raw = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$fp = stream_socket_client('tcp://127.0.0.1:9600');
fwrite($fp, pack('N', strlen($raw)) . $raw);//pack数据校验

$data = fread($fp, 65533);
//做长度头部校验
$len = unpack('N', $data);
$data = substr($data, '4');
if (strlen($data) != $len[1]) {
    echo 'data error';
} else {
    $data = json_decode($data, true);
//    //这就是服务端返回的结果，
    var_dump($data);//默认将返回一个response对象 通过$serializeType修改
}
fclose($fp);
````

## Go示例代码

````
package main

import (
	"encoding/binary"
	"net"
)

func main() {
	var tcpAddr *net.TCPAddr
	tcpAddr,_ = net.ResolveTCPAddr("tcp","127.0.0.1:9600")
	conn,_ := net.DialTCP("tcp",nil,tcpAddr)
	defer conn.Close()
	sendEasyswooleMsg(conn)
}

func sendEasyswooleMsg(conn *net.TCPConn) {
	var sendData []byte
	data := `{"command":1,"request":{"serviceName":"UserService","action":"register","arg":{"args1":"args1","args2":"args2"}}}`
	b := []byte(data)
	// 大端字节序(网络字节序)大端就是将高位字节放到内存的低地址端，低位字节放到高地址端。
	// 网络传输中(比如TCP/IP)低地址端(高位字节)放在流的开始，对于2个字节的字符串(AB)，传输顺序为：A(0-7bit)、B(8-15bit)。
	sendData = int32ToBytes8(int32(len(data)))
	// 将数据byte拼装到sendData的后面
	for _, value := range b {
		sendData = append(sendData, value)
	}
	conn.Write(sendData)
}

func int32ToBytes8(n int32) []byte {
	var buf = make([]byte, 4)
	binary.BigEndian.PutUint32(buf, uint32(n))
	return buf
}
````

## Java

````
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;

public class Main {
    public static void main(String[] args) throws IOException {
        byte[] msg = "{\"command\":1,\"request\":{\"serviceName\":\"UserService\",\"action\":\"register\",\"arg\":{\"args1\":\"args1\",\"args2\":\"args2\"}}}".getBytes();
        byte[] head = Main.toLH(msg.length);
        byte[] data = Main.mergeByteArr(head, msg);

        //创建Socket对象，连接服务器
        Socket socket=new Socket("127.0.0.1",9600);
        //通过客户端的套接字对象Socket方法，获取字节输出流，将数据写向服务器
        OutputStream out=socket.getOutputStream();
        out.write(data);

        //读取服务器发回的数据，使用socket套接字对象中的字节输入流
        InputStream in=socket.getInputStream();
        byte[] response=new byte[1024];
        int len=in.read(response);
        System.out.println(new String(response,4, len-4));
        socket.close();
    }

    static byte[] toLH(int n) {
        byte[] b = new byte[4];
        b[3] = (byte) (n & 0xff);
        b[2] = (byte) (n >> 8 & 0xff);
        b[1] = (byte) (n >> 16 & 0xff);
        b[0] = (byte) (n >> 24 & 0xff);
        return b;
    }


    static byte[] mergeByteArr(byte[] a, byte[] b) {
        byte[] c= new byte[a.length + b.length];
        System.arraycopy(a, 0, c, 0, a.length);
        System.arraycopy(b, 0, c, a.length, b.length);
        return c;
    }
}
````

::: warning 
其他语言只需要实现tcp协议即可
:::


# EasySwoole RPC 自定义注册中心

`EasySwoole`默认为通过`UDP`广播+自定义进程定时刷新自身节点信息的方式来实现无主化/注册中心的服务发现。在服务正常关闭的时候，自定义定时进程的`onShutdown`方法会执行`deleteServiceNode`方法来实现节点下线。在非正常关闭的时候，心跳超时也会被节点管理器踢出。

有些情况，不方便用`UDP`广播的情况下，那么`EasySwoole`支持你自定义一个节点管理器，来变更服务发现方式。

## 例如用Redis来实现

```php
namespace EasySwoole\Rpc\NodeManager;

use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\ServiceNode;
use EasySwoole\Utility\Random;

class RedisManager implements NodeManagerInterface
{
    protected $redisKey;

    protected $pool;

    function __construct(RedisPool $pool, string $hashKey = 'rpc')
    {
        $this->redisKey = $hashKey;
        $this->pool = $pool;
    }

    function getServiceNodes(string $serviceName, ?string $version = null): array
    {
        $redis = $this->pool->getObj(15);
        try {
            $nodes = $redis->hGetAll("{$this->redisKey}_{$serviceName}");
            $nodes = $nodes ?: [];
            $ret = [];
            foreach ($nodes as $nodeId => $node) {
                $node = new ServiceNode(json_decode($node,true));
                /**
                 * @var  $nodeId
                 * @var  ServiceNode $node
                 */
                if (time() - $node->getLastHeartBeat() > 30) {
                    $this->deleteServiceNode($node);
                }
                if ($version && $version != $node->getServiceVersion()) {
                    continue;
                }
                $ret[$nodeId] = $node;
            }
            return $ret;
        } catch (\Throwable $throwable) {
            //如果该redis断线则销毁
            $this->pool->unsetObj($redis);
        } finally {
            $this->pool->recycleObj($redis);
        }
        return [];
    }

    function getServiceNode(string $serviceName, ?string $version = null): ?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName, $version);
        if (empty($list)) {
            return null;
        }
        return Random::arrayRandOne($list);
    }

    function deleteServiceNode(ServiceNode $serviceNode): bool
    {
        $redis = $this->pool->getObj(15);
        try {
            $redis->hDel($this->generateNodeKey($serviceNode), $serviceNode->getNodeId());
            return true;
        } catch (\Throwable $throwable) {
            $this->pool->unsetObj($redis);
        } finally {
            $this->pool->recycleObj($redis);
        }
        return false;
    }

    function serviceNodeHeartBeat(ServiceNode $serviceNode): bool
    {
        if (empty($serviceNode->getLastHeartBeat())) {
            $serviceNode->setLastHeartBeat(time());
        }
        $redis = $this->pool->getObj(15);
        try {
            $redis->hSet($this->generateNodeKey($serviceNode), $serviceNode->getNodeId(), $serviceNode->__toString());
            return true;
        } catch (\Throwable $throwable) {
            $this->pool->unsetObj($redis);
        } finally {
            //这边需要测试一个对象被unset后是否还能被回收
            $this->pool->recycleObj($redis);
        }
        return false;
    }

    protected function generateNodeKey(ServiceNode $node)
    {
        return "{$this->redisKey}_{$node->getServiceName()}";
    }
}
```

::: warning 
即使关闭了`UDP`定时广,`EasySwoole Rpc`的`tick`进程依旧会每3秒执行一次`serviceNodeHeartBeat`用于更新自身的节点心跳信息。
:::