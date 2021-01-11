---
title: IP address home
meta:
  - name: description
    content: IP address home
  - name: keywords
    content: easyswoole IP address home | IPv6 address query
---

# IP address home, support IPv6 address (offline database, regularly updated)

## Link 

[Github](https://github.com/ritaswc/zx-ip-address)
[Packagist](https://packagist.org/packages/ritaswc/zx-ip-address)
[Blog](https://blog.yinghualuo.cn)


## Use 
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
        0 => "Nanjing, Jiangsu Province"
        1 => "Greatbit DNS server of Nanjing trade wind Network Technology Co., Ltd"
    ]
    "disp" => "Greatbit DNS server of Nanjing trade wind Network Technology Co., Ltd"
]
 */
$result = \Ritaswc\ZxIPAddress\IPv6Tool::query('240e:e9:8819:0:3::3f9');
/*
$result = [
    "start" => "240e:e9:8800::"
    "end" => "240e:e9:8fff:ffff::"
    "addr" => array:2 [
        0 => "Suzhou City, Jiangsu Province, China"
        1 => "China Telecom IDC"
    ]
    "disp" => "China Telecom IDC, Suzhou City, Jiangsu Province, China"
]
 */
```
