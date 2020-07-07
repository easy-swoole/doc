---
title: easyswoole swoole-测试tcp服务
meta:
  - name: description
    content: easyswoole swoole-测试tcp服务
  - name: keywords
    content: easyswoole swoole-测试tcp服务|easyswoole|swoole
---

## 测试tcp服务
我们可通过telnet客户端进行测试:   
```bash
tioncico@tioncico-PC:~$ telnet 127.0.0.1 9501
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
```

服务器将打印:  
```bash
tioncico@tioncico-PC:~/PhpstormProjects/easyswoole/tioncico-demo$ php tcp.php 
服务器启动成功
客户端 1 连接成功
```

发送内容:  
```bash
tioncico@tioncico-PC:~$ telnet 127.0.0.1 9501
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
hello,easyswoole
服务器响应: hello,easyswoole

```

服务器输出:  
```bash
客户端 1 关闭
客户端 5 连接成功
客户端 5 发来消息:hello,easyswoole
```