---
title: easyswoole 双机热备
meta:
  - name: description
    content: easyswoole 双机热备
  - name: keywords
    content: easyswoole 双机热备|swoole|swoole 框架|easyswoole 框架
---

# 双机热备

## 1. 介绍

> 文章主要介绍，`EasySwoole` 使用双机热备思路实现代码不中断部署。

## 2. 学习案例

- 1. 先部署 `9501` 服务
- 2. 单起一个进程，定时轮询 `Git` 分支是否有新版本发布
- 3. 如有新版本发布，`clone` 一份
- 4. `composer update` 更新库
- 5. 启动 `9502` 服务
- 6. 更改 `nginx` 配置为 `9502` 并重启

> 只要有新版本发布，就轮询上面那几个步骤

`整个过程的简单架构图`

![image.png](/Images/Other/Deploy/soa.jpg)

## 3. 需要提前了解的知识点

1. [Nginx 负载均衡和反向代理](https://zhuanlan.zhihu.com/p/99117739)
2. [EasySwoole 自定义进程](/Components/Component/process.md)
3. [Nginx reload 和 restart 的区别](https://www.cnblogs.com/fanggege/p/12145956.html)
4. [双机热备](https://baike.baidu.com/item/%E5%8F%8C%E6%9C%BA%E7%83%AD%E5%A4%87/2394182?fr=aladdin)

## 4. Nginx 配置

### nginx.conf 配置文件示例

>  当有新版本发布的时候 `EasySwoole` 自定义进程会将 `nginx.conf` 的端口改为最新服务的端口

```
worker_processes  1;

events {
    worker_connections  1024;
}

http {

    include       mime.types;
    default_type  application/octet-stream;

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';
    sendfile        on;
 
    keepalive_timeout  65;
    
    ### 轮询配置（这里是重点）
    upstream  easyswoole_release_upstream {
        server 127.0.0.1:9501;
        server 127.0.0.1:9502;
    }

    include servers/*;
}

```

### es-release.conf 站点配置文件

该配置文件在 `servers` 目录下。（此示例是以 `Mac` 本地环境作为运行环境） 

```
server {
    listen 80;
    server_name easyswoole.release.com;

    location / {
        root html;
        index index.html index.htm;
        proxy_pass http://easyswoole_release_upstream; ### 这里是重点
    }
    access_log /usr/local/etc/nginx/logs/es.access.log main;
    error_log /usr/local/etc/nginx/logs/es.error.log error;
}
```

## 5. EasySwoole 代码实现

> 代码只提供实现思路，并且这种脚本，最好单独去做，比如用 `shell` 脚本，防止服务宕机导致无法正常部署代码

### 创建自定义进程类文件

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\Release;

use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine;

class Release extends AbstractProcess
{
    public function dolog($msg, $filename = '/Users/xxx/sites/release_log.log')
    {
        if ($msg) {
            error_log($msg . PHP_EOL, 3, $filename);
        }
    }

    protected function run($arg)
    {
        go(function () {
            while (true) {
                $shellLog = ' 2>> /Users/xxx/sites/release_log.log';
                $this->dolog(date('Y-m-d H:i:s') . '开始检测代码是否更新 ===> START <=== ');
                // 检查 Git 是否有新代码发布
                $diffExec = 'cd ' . EASYSWOOLE_ROOT . '; git fetch; git diff --stat master origin/master;';
                $this->dolog($diffExec);
                $pullResult = exec($diffExec);

                $this->dolog('git fetch res: => ' . json_encode($pullResult));

                if ($pullResult !== '') {
                    $this->dolog('有新版本发布' . json_encode($pullResult));
                    // 新版本项目的目录
                    $newVersionPath = '/Users/xxx/sites/release-' . time();

                    // 开始 clone, 初始化代码
                    ### 这里需要换成自己的 EasySwoole 项目的 github 地址
                    $cloneExec = "git clone https://github.com/huizhang-Easyswoole/release.git {$newVersionPath} {$shellLog};cd {$newVersionPath} {$shellLog};composer update {$shellLog}; {$shellLog}";
                    $this->dolog($cloneExec);

                    $res = exec($cloneExec, $output, $returnVar);
                    $this->dolog('git clone res: => ' . json_encode($res, JSON_UNESCAPED_UNICODE));
                    $this->dolog('新版本代码 clone end');


                    // 判断当前是哪个端口正在服务
                    $lsofExec = "lsof -i:9501 {$shellLog}";
                    $this->dolog($lsofExec);
                    $lsofResult = exec($lsofExec);
                    $newPort = 9501;
                    $oldPort = 9502;
                    if ($lsofResult !== '') {
                        $newPort = 9502;
                        $oldPort = 9501;
                    }

                    // 将另一个闲置的端口，替换到新版本中
                    $this->dolog('开始替换端口' . $newPort);
                    $devConfig = file_get_contents($newVersionPath . '/dev.php');
                    $devConfig = str_replace($oldPort, $newPort, $devConfig);
                    file_put_contents($newVersionPath . '/dev.php', $devConfig);

                    // 启动新服务(这一刻新旧服务是同时存在的)
                    $this->dolog('新服务启动');
                    $startExec = "cd {$newVersionPath}; php easyswoole server start -d {$shellLog}";
                    $this->dolog($startExec);
                    exec($startExec);

                    // 替换 Nginx 配置
                    $this->dolog('开始替换 nginx 端口');
                    ### 这里需要换成自己服务器环境 nginx 配置文件所在的目录
                    $ngConfigPath = '/usr/local/etc/nginx/nginx.conf';
                    $ngConfig = file_get_contents($ngConfigPath);
                    $ngConfig = str_replace($oldPort, $newPort, $ngConfig);
                    file_put_contents($ngConfigPath, $ngConfig);

                    // 重启 Nginx 服务
                    $this->dolog('重启 nginx ');
                    $reloadNgExec = "nginx -s reload {$shellLog}";
                    $this->dolog($reloadNgExec);
                    exec($reloadNgExec);

                    // 停掉旧服务
                    $this->dolog('旧服务停掉');
                    $stopExec = "cd " . EASYSWOOLE_ROOT . "; php easyswoole server stop {$shellLog}";
                    $this->dolog($stopExec);
                    exec($stopExec);

                    // 每 30 秒同步一次代码
                    Coroutine::sleep(30);
                } else {
                    Coroutine::sleep(10);
                    $this->dolog('无新版本更新');
                }
            }
        });
    }
}
```

### 注册自定义进程

在框架的 `EasySwooleEvent` 事件（即项目根目录的 `EasySwoolEvent.php`）中注册自定义进程，示例代码如下：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace EasySwoole\EasySwoole;

use App\Release\Release;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        ###### 注册 双机热备服务 自定义进程 ######
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'Es-release', // 设置 自定义进程名称
            'processGroup' => 'Es-release', // 设置 自定义进程组名称
        ]);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new Release($processConfig));
    }
}
```

## 6. 测试

### 绑定 host

```
127.0.0.1 easyswoole.release.com
```

### 访问 easyswoole.release.com

![image.png](/Images/Other/Deploy/result.jpg)

### 查看 Nginx 配置的端口

```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9501;
```

### 发布新版本

> 重新 `clone` 一份代码，更改内容提交。

### 查看Nginx配置的端口

```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9502;
```
