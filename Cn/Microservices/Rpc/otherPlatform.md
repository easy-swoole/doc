---
title: easyswoole rpc跨平台
meta:
  - name: description
    content: easyswoole rpc跨平台
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---

# 跨平台

`Rpc` 的请求响应是通过 `tcp` 作为传输层协议实现，服务广播使用 `udp` 协议，所以当我们使用其他语言作为 `Rpc` 客户端时，只需要实现对应的应用层网络协议即可。

下面客户端使用的服务端是 [微服务 - 服务端章节](/Microservices/Rpc/server.md) 基于自定义节点管理器 `Redis 节点管理器` 实现的。

具体 `RPC` 服务端 `demo` 代码可查看 Github [RPC 5.x Demo Github](https://github.com/easy-swoole/demo/tree/5.x-rpc-demo) 或者 Gitee [RPC 5.x Demo Gitee](https://gitee.com/1592328848/easyswoole_demo/tree/5.x-rpc-demo)

## PHP RPC 客户端示例代码

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

$data = [
    'service' => 'Goods', // 需要调用的服务名称
    'module'  => 'GoodsModule', // 需要调用的服务下的子模块名称
    'action'  => 'list',  // 需要调用的服务下的子模块的方法名称
    'arg'     => ['a', 'b', 'c'], // 需要传递的参数
];

$raw = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// tcp://127.0.0.1:9600（示例请求地址） 是 rpc 服务端的地址，这里是本地，所以使用 127.0.0.1
// 开发者需要根据实际情况调整进行调用
$fp = stream_socket_client('tcp://127.0.0.1:9600');
fwrite($fp, pack('N', strlen($raw)) . $raw); // pack 数据校验

$data = fread($fp, 65533);
// 做长度头部校验
$len = unpack('N', $data);
$data = substr($data, '4');
if (strlen($data) != $len[1]) {
    echo 'data error';
} else {
    $data = json_decode($data, true);
    // 这就是服务端返回的结果
    var_dump($data);
}
fclose($fp);

/**
 * 调用结果如下：
 * 其中 
 * statue 为服务端返回给客户端的调用状态码 （具体可查看服务端：https://www.easyswoole.com/Microservices/Rpc/client.html）
 * result 为服务端返回给客户端的调用结果
 * msg    为服务端返回给客户端的调用状态信息
 * responseUUID 为服务端响应客户端的唯一标识
 */
array(4) {
  ["status"]=>
  int(0)
  ["result"]=>
  array(2) {
    [0]=>
    array(3) {
      ["goodsId"]=>
      string(6) "100001"
      ["goodsName"]=>
      string(7) "商品1"
      ["prices"]=>
      int(1124)
    }
    [1]=>
    array(3) {
      ["goodsId"]=>
      string(6) "100002"
            ["goodsName"]=>
      string(7) "商品2"
            ["prices"]=>
      int(599)
    }
  }
  ["msg"]=>
  string(22) "get goods list success"
  ["responseUUID"]=>
  string(36) "3897f7ea-12a0-39c1-8948-ee9b9bc37274"
}
```

## Go RPC 客户端示例代码

```go
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

package main

import (
	"encoding/binary"
	"net"
)

func main() {
	var tcpAddr *net.TCPAddr
	tcpAddr, _ = net.ResolveTCPAddr("tcp","192.168.1.107:9600")
	conn, _ := net.DialTCP("tcp", nil, tcpAddr)
	defer conn.Close()
	sendEasyswooleMsg(conn)
}

func sendEasyswooleMsg(conn *net.TCPConn) {
	var sendData []byte
	data := `{"service":"Goods","module":"GoodsModule","action":"list","arg":["a","b","c"]}`
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
```

## Java RPC 客户端示例代码

```java
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */


import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;

public class Main {
	public static void main(String[] args) throws IOException {
        byte[] msg = "{\"service\":\"Goods\",\"module\":\"GoodsModule\",\"action\":\"list\",\"arg\":[\"a\",\"b\",\"c\"]}".getBytes();
        byte[] head = Main.toLH(msg.length);
        byte[] data = Main.mergeByteArr(head, msg);

        // 创建 Socket 对象，连接  rpc 服务器
        Socket socket = new Socket("127.0.0.1",9600);
        // 通过客户端的套接字对象 Socket 方法，获取字节输出流，将数据写向服务器
        OutputStream out = socket.getOutputStream();
        out.write(data);

        // 读取服务器返回的数据，使用 socket 套接字对象中的字节输入流
        InputStream in = socket.getInputStream();
        byte[] response = new byte[1024];
        int len = in.read(response);
        // 这里是 rpc 服务端返回的结果为 json 字符串
        System.out.println(new String(response, 4, len-4));
        socket.close();
    }

    public static byte[] toLH(int n) {
        byte[] b = new byte[4];
        b[3] = (byte) (n & 0xff);
        b[2] = (byte) (n >> 8 & 0xff);
        b[1] = (byte) (n >> 16 & 0xff);
        b[0] = (byte) (n >> 24 & 0xff);
        return b;
    }

    public static byte[] mergeByteArr(byte[] a, byte[] b) {
        byte[] c = new byte[a.length + b.length];
        System.arraycopy(a, 0, c, 0, a.length);
        System.arraycopy(b, 0, c, a.length, b.length);
        return c;
    }
}

/**
 * 服务端返回结果如下：
 */
{"status":0,"result":[{"goodsId":"100001","goodsName":"商品1","prices":1124},{"goodsId":"100002","goodsName":"商品2","prices":599}],"msg":"get goods list success","responseUUID":"66b81f45-10f7-1a3e-fecd-9b57b021e31e"}
```

::: warning 
 其他语言只需要实现对应的应用层协议即可
:::
