---
title: easyswoole swoole-客户端属性
meta:
  - name: description
    content: easyswoole swoole-客户端属性
  - name: keywords
    content: easyswoole swoole-客户端属性|easyswoole|swoole
---

## 客户端属性

### errCode  
错误码.  
::: warning
当客户端调用`connect/send/recv/close`方法失败时,会将错误码复制到该属性上.   
可使用`echo socket_strerror($client->errCode);`获取错误信息.  
:::   
### sock
客户端连接成功后的socket连接描述符.

::: warning
- 可使用`$sock = fopen("php://fd/".$client->sock);`将socket转换成`stream socket`.  
- sock可转换成int类型,作为数组的key.  
:::

### reuse
表示此连接是新创建的还是复用已存在的.  
与 `SWOOLE_KEEP` 配合使用.


## 客户端常量
### SWOOLE_KEEP
`\Swoole\Client`支持`php-fpm/apache`的`keep-alive`长连接.   
```php
$client = new \Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
``` 

::: warning
开启之后,请求结束不会关闭`socket`,下一次调用`connect`会自动复用上次的连接,而不是直接重新建立连接.这样可以减少tcp的3次握手/4次挥手开销  
如果上次的连接被服务器主动关闭,`connect`将会自动建立新的连接.  
:::

### MSG_WAITALL
消息等待.  
- 当`recv`设置了这个参数后,就必须设置准确的`$size`接收字节数,`recv方法`会一直接收服务端的数据,直到数据长度达到了`$size`才会返回数据.   
- 如果没有设置该参数,`recv`最大为`64k`,设置错误将会使`recv`超时,返回false

### MSG_DONTWAIT
设置为非阻塞接收数据,无论是否有数据,都将立即返回.  

### MSG_PEEK
设置该参数后. 仅把`tcp buffer`中的数据读取到`buf`中,并不把已读取的数据从`tcp buffer`中移除,再次调用`recv`仍然可以读到刚才读到的数据.

### MSG_OOB
额外获取 `tcp带外数据` 

