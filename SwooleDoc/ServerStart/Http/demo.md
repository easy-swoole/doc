---
title: easyswoole swoole-简单运行
meta:
  - name: description
    content: easyswoole swoole-简单运行
  - name: keywords
    content: easyswoole swoole-简单运行|easyswoole|swoole
---

## 简单运行
新增`http.php`文件,通过以下代码可实现一个简单的http服务器.  
```php
<?php
$server = new Swoole\Http\Server("0.0.0.0", 9501);

//当浏览器发送http请求时,将会到这里回调
$server->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    
    var_dump($request->get);//在终端打印get参数
    var_dump($request->post);//在终端打印post参数 
    //发送header头,不能直接通过header函数发送
    $response->header("Content-Type", "text/html; charset=utf-8");
    //向浏览器响应数据
    $response->write("<h1>easyswoole</h1>");
    $response->write("<h1>easy学swoole</h1>");
    $response->write("<h1>你是第{$request->fd}个访问者</h1>");
    //结束最后的响应
    $response->end("<hr>");
});
echo "http服务器启动成功\n";

$server->start();
```