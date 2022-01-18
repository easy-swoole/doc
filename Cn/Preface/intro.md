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

EasySwoole 是一款基于 Swoole Server 开发的常驻内存型的分布式 PHP 框架，专为 API 而生，摆脱传统 PHP 运行模式在进程唤起和文件加载上带来的性能损失。
EasySwoole 高度封装了 Swoole Server 而依旧维持 Swoole Server 原有特性，支持同时混合监听 HTTP、自定义 TCP、UDP 协议，让开发者以最低的学习成本和精力编写出多进程、可异步、高可用的应用服务。在开发上，我们为您准备了以下常用组件：

- HTTP 服务服务器
- 协程 ORM (类似Tp ORM)
- 图片验证码
- Validate 验证器
- 协程模板渲染引擎
- JWT 组件
- 协程 TCP、UDP、WEB_SOCKET 服务端
- 协程 Redis 连接池
- 协程 MySQL 连接池
- 协程 Memcached 客户端
- 协程通用连接池
- 协程 Kafka 客户端
- NSQ 协程客户端
- 分布式跨平台 RPC 组件
- 协程 Consul 客户端
- 协程 Apollo 配置中心
- 协程 Actor 组件
- 协程 SMTP 客户端
- 协程版微信公众号与小程序 SDK
- 协程版微信、支付宝支付 SDK
- 协程 ElasticSearch 客户端
- 协程 HTTP 客户端组件
- 协程上下文管理
- IOC、协程上下文管理器
- Snowflake Id生成器
- Crontab 秒级任务
- 自定义进程
- 自定义消息队列
- Tracker 链路跟踪
- Atomic 限流器
- Fast-Cache 组件
- Policy 权限组件
- 注解及API文档自动生成组件
- Casbin 验证权限组件
- 自动生成代码组件
- OAuth 组件
- 协程 OSS 客户端
- Printer 易联云打印机SDK
- 数据库迁移工具
- 协程 ETCD 客户端


::: warning 
  以上组件为常用组件，更多组件请看组件库文档
:::

## 生产可用
EasySwoole 从最早的前身 EasyPHP-Swoole，到更名为 EasySwoole，再到现如今的 EasySwoole 3.x 版本，多年时间在众多社区小伙伴的共同努力下，EasySwoole 的稳定与可靠已经经历了非常多的大企业检验。

例如：

- 腾讯公司的 IEG 部门
- WEGAME 部门
- 网宿科技（国内 CDN 厂家）
- 360 金融
- 360 小游戏（Actor）
- 9377 小游戏
- 厦门美图网
- 蝉大师
- 宝宝巴士
- 瑞祥科技集团

等公司都在使用 EasySwoole。

## 特性

- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker 进程组，Task 异步任务，毫秒定时器，SSL/TLS 隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将 Socket、Stream、管道等 Linux 文件加入到事件循环中
- 定时器、协程对象池、HTTP/SOCKET 控制器、分布式微服务、RPC 支持

## 优势

- 简单易用开发效率高
- 并发百万 TCP 连接
- TCP/UDP/UnixSocket
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU 亲和性/守护进程

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
    - XueSi 1592328848@qq.com
    

::: warning 
  以上排名不分先后        
:::

## 其他
- [GitHub](https://github.com/easy-swoole/easyswoole)  喜欢记得点个***star***
- [GitHub for Doc](https://github.com/easy-swoole/doc)
- [DEMO](https://github.com/easy-swoole/demo/) 暂且没有完全适配新版本，具体看文档。

- QQ 交流群
    - VIP 群 579434607 （本群需要付费599元）
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
  您的捐赠是对 EasySwoole 项目开发组最大的鼓励和支持。我们会坚持开发维护下去。 您的捐赠将被用于:
        
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
