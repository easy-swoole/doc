---
title: easyswoole 配置中心-apollo客户端
meta:
  - name: description
    content: easyswoole 配置中心-apollo客户端
  - name: keywords
    content: swoole apollo|easyswoole apollo|swoole协程apollo
---

# apollo

`EasySwoole`实现了对[apollo](https://github.com/easy-swoole/apollo)数据中心的支持.可根据该组件,进行同步配置

## 安装

> composer require easyswoole/apollo

## 使用

```php
go(function (){
    //配置apollo服务器信息
    $server = new \EasySwoole\Apollo\Server([
        'server'=>'http://106.12.25.204:8080',
        'appId'=>'easyswoole'
    ]);
    //创建apollo客户端
    $apollo = new \EasySwoole\Apollo\Apollo($server);
    //第一次同步
    var_dump( $apollo->sync('mysql'));
    //第二次同步，若服务端没有改变，那么返回的结果，isModify标记为fasle，并带有lastReleaseKey
    var_dump( $apollo->sync('mysql'));
});
```


::: warning 
 可以在进程中起一个定时器，实现自动定时更新
:::

