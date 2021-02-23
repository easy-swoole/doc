---
title: easyswoole swoole-测试udp服务
meta:
  - name: description
    content: easyswoole swoole-测试udp服务
  - name: keywords
    content: easyswoole swoole-测试udp服务|easyswoole|swoole
---


## 测试udp服务
udp协议没有连接的概念,无需连接,只需要知道ip,端口,即可直接发送数据.

## udp客户端代码
新增文件udpClient.php文件:  
```php
<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/2/28 0028
 * Time: 16:07
 */

$host='127.0.0.1';
$port = '9502';
$client_socket = stream_socket_client("udp://$host:$port", $errno, $errstr, 30);
echo "服务器连接成功\n";
while (!feof($client_socket)) {
    //获取标准输入数据
    $msg = fgets(STDIN);
    //发送到udp服务端
    fwrite($client_socket, $msg);
    $buffer = fread($client_socket,255);//默认阻塞类型,没有消息会一直阻塞
    echo  $buffer . PHP_EOL;
    sleep(1);
}
```

通过在命令行运行udpClient,并且输入信息,即可发送到服务端:  
```text
[sftp://root@x.cn:22]:/usr/bin/php /www/easyswoole/tioncico-doc-3.3.x/udpClient.php
服务器连接成功
easyswoole牛逼
服务器回复:easyswoole牛逼

仙士可
服务器回复:仙士可

```

服务器端显示:
```bash
#udp客户端发送了数据:easyswoole牛逼
#
#array(4) {
#  ["server_socket"]=>
#  int(4)
#  ["server_port"]=>
#  int(9502)
#  ["address"]=>
#  string(9) "127.0.0.1"
#  ["port"]=>
#  int(44563)
#}
#udp客户端发送了数据:仙士可
#
#array(4) {
#  ["server_socket"]=>
#  int(4)
#  ["server_port"]=>
#  int(9502)
#  ["address"]=>
#  string(9) "127.0.0.1"
#  ["port"]=>
#  int(44563)
#}
```
