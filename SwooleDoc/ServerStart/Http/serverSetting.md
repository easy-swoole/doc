---
title: easyswoole swoole-http服务启动参数配置
meta:
  - name: description
    content: easyswoole swoole-http服务启动参数配置
  - name: keywords
    content: easyswoole swoole-http服务启动参数配置|easyswoole|swoole
---

## http server启动参数配置

### upload_tmp_dir
说明:设置上传文件的临时目录.   
默认值:/tmp
补充说明:当http提交表单包含文件时,文件将会存储到该临时目录
::: warning
最大长度不能超过220kb. 
:::

### http_parse_post
说明:是否开启POST数据解析  
默认值:true    
补充说明:  
开启之后,[Request对象](/Cn/Swoole/ServerStart/Http/request.md)将自动把`Content-Type`为`x-www-form-urlencoded`的数据解析到`post`数组中   

### http_parse_cookie
说明:是否开启cookie数据解析  
默认值:true  
补充说明:  
开启之后,[Request对象](/Cn/Swoole/ServerStart/Http/request.md)将自动把原始的`cookie`数据转到`cookie`数组中    

### http_compression
说明:是否压缩响应数据  
默认值:true  
补充说明:   
开启之后,当使用[response对象](/Cn/Swoole/ServerStart/Http/response.md) 给客户端响应数据时,   
会自动根据客户端的`Accept-Encoding `请求头进行选择压缩方式

::: warning
`http-chunk` 不支持压缩,当使用`write`方法输出时,将直接关闭压缩.  
依赖`zlib`,`brotli(br压缩格式需要用到)`库,使用时请先保证依赖存在.    

:::
### http_compression_level
说明:压缩级别  
默认值:1 (1-9)
补充说明:  
等级越高压缩后的数据越小  

### document_root
说明:配置静态文件根目录,需要和`enable_static_handler`配置一起使用  
默认值:null   
补充说明:  
配置后,客户端访问时,会先判断该目录是否存在该文件,如果存在,则直接响应文件.  
```php
$server->set([
    'document_root' => '/www/wwwroot/easyswoole.com', // 版本小于v4.4.0时必须为绝对路径
    'enable_static_handler' => true,
]);
```
::: warning
该功能很垃圾,不建议使用,建议直接使用nginx代理静态文件目录.   
:::  
### enable_static_handler   
说明:是否开启静态文件处理功能  
默认值:false  
补充说明:  
配置后,客户端访问时,会先判断该目录是否存在该文件,如果存在,则直接响应文件.  
::: warning
垃圾功能,没必要使用,请使用nginx代理静态文件    
:::  
### static_handler_locations
说明:单独设置静态处理器处理的路径  
默认值:null
补充说明:
```php
$server->set([
    "static_handler_locations" => ['/static', '/public/images'],
]);
```

### open_http2_protocol
说明:是否启用http2协议解析  
默认值:false  
::: warning
编译swoole时,需要启用`--enable-http2`配置
:::