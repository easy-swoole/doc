---
title: IP地址归属地
meta:
  - name: description
    content: IP地址归属地
  - name: keywords
    content: easyswoole IP地址归属地 | IPv6地址查询
---

# IP地址归属地，支持IPv6地址(离线数据库，定期更新)

## 为什么建立这个库

政府在大力推进IPv6建设，作者响应国家号召，整合网络免费数据库，制作成离线版供大家使用
国家公文链接：[关于开展2019年IPv6网络就绪专项行动的通知](http://www.miit.gov.cn/newweb/n1146285/n1146352/n3054355/n3057674/n4704636/c6791072/content.html)


## 链接

[Github](https://github.com/ritaswc/zx-ip-address)
[Packagist](https://packagist.org/packages/ritaswc/zx-ip-address)
[Blog](https://blog.yinghualuo.cn)


## 使用方法
```shell script
composer require ritaswc/zx-ip-address
```

```php
$result = \Ritaswc\ZxIPAddress\IPv4Tool::query('114.114.114.114');
/*
$result = [
    "start" => "114.114.114.114"
    "end" => "114.114.114.114"
    "addr" => array:2 [
        0 => "江苏省南京市"
        1 => "南京信风网络科技有限公司GreatbitDNS服务器"
    ]
    "disp" => "江苏省南京市 南京信风网络科技有限公司GreatbitDNS服务器"
]
 */
$result = \Ritaswc\ZxIPAddress\IPv6Tool::query('240e:e9:8819:0:3::3f9');
/*
$result = [
    "start" => "240e:e9:8800::"
    "end" => "240e:e9:8fff:ffff::"
    "addr" => array:2 [
        0 => "中国江苏省苏州市"
        1 => "中国电信IDC"
    ]
    "disp" => "中国江苏省苏州市 中国电信IDC"
]
 */
```
