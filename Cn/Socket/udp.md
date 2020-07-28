---
title: easyswoole udp服务
meta:
  - name: description
    content: easyswoole udp服务
  - name: keywords
    content: easyswoole udp服务|swoole 硬件|swoole iot
---

# UDP

UDP 为应用程序提供了一种无需建立连接就可以发送封装的 IP 数据包的方法

## 基本使用

### EasySwooleEvent.php中进行创建子服务

```php

public static function mainServerCreate(EventRegister $register)
{
    $server = ServerManager::getInstance()->getSwooleServer();
    $subPort = $server->addListener('0.0.0.0','9601',SWOOLE_UDP);
    $subPort->on('packet',function (\swoole_server $server, string $data, array $client_info){
        var_dump($data);
    });
}
```


###  UDP客户端
```php

public static function mainServerCreate(EventRegister $register)
{
  //添加自定义进程做定时udp发送
    $server->addProcess(new \swoole_process(function (\swoole_process $process){
        //服务正常关闭
        $process::signal(SIGTERM,function ()use($process){
            $process->exit(0);
        });
        //默认5秒广播一次
        \Swoole\Timer::tick(5000,function (){
            if($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
            {
                socket_set_option($sock,SOL_SOCKET,SO_BROADCAST,true);
                $msg= '123456';
                socket_sendto($sock,$msg,strlen($msg),0,'255.255.255.255',9602);//广播地址
                socket_close($sock);
            }
        });
    }));
}
```



