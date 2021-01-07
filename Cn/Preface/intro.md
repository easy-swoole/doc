---
title: easyswoole框架,一款基于swoole实现的高性能协程框架
meta:
  - name: description
    content: easyswoole是一款基于swoole的一个高性能分布式微服务框架，旨在提供一个高效、快速、优雅的框架给php开发者
  - name: keywords
    content: swoole|swoole拓展|easyswoole|swoole框架
---
```
  ______                          _____                              _        
 |  ____|                        / ____|                            | |       
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___ 
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |                                                
                         |___/                                                
```
# EasySwoole
[![Latest Stable Version](https://poser.pugx.org/easyswoole/easyswoole/v/stable)](https://packagist.org/packages/easyswoole/easyswoole)
[![Total Downloads](https://poser.pugx.org/easyswoole/easyswoole/downloads)](https://packagist.org/packages/easyswoole/easyswoole)
[![Latest Unstable Version](https://poser.pugx.org/easyswoole/easyswoole/v/unstable)](https://packagist.org/packages/easyswoole/easyswoole)
[![License](https://poser.pugx.org/easyswoole/easyswoole/license)](https://packagist.org/packages/easyswoole/easyswoole)
[![Monthly Downloads](https://poser.pugx.org/easyswoole/easyswoole/d/monthly)](https://packagist.org/packages/easyswoole/easyswoole)

EasySwoole 是一款基于Swoole Server 开发的常驻内存型的分布式PHP框架，专为API而生，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失。
EasySwoole 高度封装了 Swoole Server 而依旧维持 Swoole Server 原有特性，支持同时混合监听HTTP、自定义TCP、UDP协议，让开发者以最低的学习成本和精力编写出多进程，可异步，高可用的应用服务。在开发上，我们为您准备了以下常用组件：

- http 服务服务器
- 协程ORM(类似Tp Orm)
- 图片验证码
- validate验证器
- 协程模板渲染引擎
- jwt组件
- 协程TCP、UDP、WEB_SOCKET 服务端
- 协程redis连接池
- 协程mysql 连接池
- 协程Memcached客户端
- 协程通用链接池
- 协程kafka客户端
- NSQ协程客户端
- 分布式跨平台RPC组件
- 协程consul客户端
- 协程apollo配置中心
- 协程Actor
- 协程Smtp客户端
- 协程微信公众号与小程序SDK
- 协程协程版微信、支付宝支付SDK
- 协程elasticsearch客户端
- 协程HTTP客户端组件
- 协程上下文管理
- IOC、协程上下文管理器
- snowflake Id生成器
- crontab 秒级任务
- 自定义进程
- 自定义消息队列
- Tracker链路跟踪
- Atomic限流器
- fast-cache组件
- policy权限组件


::: warning 
 以上组件为常用组件，更多组件请看组件库文档
:::

## 生产可用
Easyswoole从最早的前身EasyPHP-Swoole，到更名为Easyswoole,再到现如今的EasySwoole 3.x版本，多年时间在众多社区小伙伴的共同努力下，EasySwoole的稳定与可靠已经经历了非常多的大企业检验。

例如：

- 腾讯公司的IEG部门
- WEGAME部门
- 网宿科技（国内CDN厂家）
- 360金融
- 360小游戏（Actor）
- 9377小游戏
- 厦门美图网
- 蝉大师

等公司都在使用EasySwoole。

## 特性

- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中
- 定时器、协程对象池、HTTP\SOCK控制器、分布式微服务、RPC支持

## 优势

- 简单易用开发效率高
- 并发百万TCP连接
- TCP/UDP/UnixSock
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU亲和性/守护进程

## 维护团队
- 作者
    - 如果的如果 admin@fosuss.com   
- 团队成员
    - 阿正 1589789807@qq.com
    - 会长 2788828128@qq.com
    - 北溟有鱼 1769360227@qq.com
    - 机器人 694050314@qq.com
    - Siam(宣言) 59419979@qq.com
    - 仙士可 1067197739@qq.com
    - 史迪仔 975975398@qq.com
    

::: warning 
 以上排名不分先后        
:::

## 其他
- [GitHub](https://github.com/easy-swoole/easyswoole)  喜欢记得点个***star***
- [GitHub for Doc](https://github.com/easy-swoole/doc)
- [DEMO](https://github.com/easy-swoole/demo/) 暂且没有完全适配新版本,具体看文档.

- QQ交流群
    - VIP群 579434607 （本群需要付费599元）
    - EasySwoole官方一群 633921431(已满)
    - EasySwoole官方二群 709134628(已满)
    - EasySwoole官方三群 932625047(已满)
    - EasySwoole官方四群 779897753
    - EasySwoole官方五群 853946743
    
- 商业支持：
    - QQ 291323003
    - EMAIL admin@fosuss.com   
- 作者微信

    ![](/Images/authWx.png)    
    
- [捐赠](/Preface/donate.md)
  您的捐赠是对EasySwoole项目开发组最大的鼓励和支持。我们会坚持开发维护下去。 您的捐赠将被用于:
        
  - 持续和深入地开发
  - 文档和社区的建设和维护

<script src="/Js/jquery.min.js"></script>
<script src="/Js/Layer/layer.js"></script>
<script>
if (/(iPhone|iPad|iPod|iOS|Android)/i.test(navigator.userAgent)) {

}else{
        if(localStorage.getItem('isNew2') != 1){
            $.ajax({
                url: '/Preface/contact.html',
                method: 'POST',
                success: function (res) {
                    var newHtml = $(res);
                    var newBody = newHtml.find('.markdown-body').eq(0).html();
                    localStorage.setItem('isNew2',1);
                    layer.open({
                      type: 1,
                      title: '欢迎来到 EasySwoole，欢迎加入 QQ 交流群',
                      shadeClose: true,
                      shade: false,
                      maxmin: true, 
                      area: ['893px', '600px'],
                      content: "<div style='padding-left: 5rem'>"+newBody+"</div>"
                    });                     
                }
            });        
                         
        }
}   
</script>
