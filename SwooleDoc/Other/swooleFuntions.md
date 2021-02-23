---
title: swoole杂项函数
meta:
  - name: description
    content: swoole杂项函数
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|EasySwoole|swoole|swoole杂项函数
---

### 函数列表

```php
//设置进程名称
swoole_set_process_name("easyswoole server");
//将错误码转换为文字错误信息
echo swoole_strerror(swoole_last_error(), 9); 
//获取最近一次swoole致命的错误信息标识,可使用上面方法转换为文字错误信息
echo swoole_last_error();
//获取当前系统swoole扩展的版本号
echo swoole_version();
//获取最近一次系统调用的错误代码，返回int数字标识，可根据数字标识去错误码章节查看错误
echo swoole_error();
//获取本机所有网络接口的ip
var_dump(swoole_get_local_ip());
//清除swoole内置DNS缓存，只对swoole_client、swoole_async_dns_lookup生效
swoole_clear_dns_cache();
//获取本机所有网卡的Mac地址
var_dump(swoole_get_local_mac());
//获取当前系统CPU的核数
echo swoole_cpu_num();

```
