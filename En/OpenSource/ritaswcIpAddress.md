## IP Address location，Support IPv6(Offline database，keep updating...)

## Introduction

### Why release this software

China government is vigorously promoting the construction of IPv6. In response to the call of the government, the author integrates the free network database and makes it an offline version for everyone to use
National documents：[关于开展2019年IPv6网络就绪专项行动的通知](http://www.miit.gov.cn/newweb/n1146285/n1146352/n3054355/n3057674/n4704636/c6791072/content.html)


## Usage
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

## Links

[Packagist](https://packagist.org/packages/ritaswc/zx-ip-address)
[Github](https://github.com/ritaswc/zx-ip-address)
[Blog](https://blog.yinghualuo.cn)