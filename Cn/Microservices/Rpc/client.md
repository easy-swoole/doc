---
title: easyswoole rpc客户端
meta:
  - name: description
    content: easyswoole rpc客户端
  - name: keywords
    content: swoole rpc|swoole微服务|swoole分布式|easyswoole rpc
---

# Rpc-Client

在服务端章节已注册商品及公共服务.

## 控制器调用

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Rpc\Protocol\Response;

class Index extends Controller
{
    public function index()
    {
        // 如果在同server中 直接用保存的rpc实例调用即可
        // 如果不是需要重新new一个rpc 注意config的配置 节点管理器 以及所在ip是否能被其他服务广播到 如果不能请调整其他服务的广播地址
        $config = new \EasySwoole\Rpc\Config();
        $rpc = new \EasySwoole\Rpc\Rpc($config);
        
        $ret = [];
        $client = $rpc->client();
        // client 全局参数
        $client->setClientArg([1,2,3]);
        /**
         * 调用商品列表
         */
        $ctx1 = $client->addRequest('Goods.GoodsModule.list');
        // 设置请求参数
        $ctx1->setArg(['a','b','c']);
        // 设置调用成功执行回调
        $ctx1->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'list' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        /**
         * 调用信箱公共
         */
        $ctx2 = $client->addRequest('Common.CommonModule.mailBox');
        // 设置调用成功执行回调
        $ctx2->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'mailBox' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        /**
         * 获取系统时间
         */
        $ctx2 = $client->addRequest('Common.CommonModule.serverTime');
        // 设置调用成功执行回调
        $ctx2->setOnSuccess(function (Response $response) use (&$ret) {
            $ret[] = [
                'serverTime' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });

        // 执行调用
        $client->exec();
        $this->writeJson(200, $ret);
    }
}
```

