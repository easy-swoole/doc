---
title: easyswoole 双机热备
meta:
  - name: description
    content: easyswoole 双机热备
  - name: keywords
    content: easyswoole 双机热备|swoole|swoole 框架|easyswoole 框架
---
### 1. 介绍

> 文章主要介绍，EasySwoole使用双机热备思路实现代码不中断部署。

### 2. 学习案例

- 1. 先部署`9501`服务
- 2. 单起一个进程，定时轮询Git分支是否有新版本发布
- 3. 如有新版本发布，clone一份
- 4. composer update 更新库
- 5. 启动`9502`服务
- 6 更改nginx配置为`9502`并重启

> 只要有新版本发布，就轮询上面那几个步骤

`整个过程的简单架构图`
![image.png](/Images/Other/Deploy/soa.jpg)

### 3. 提前需要了解的知识点

1. [Nginx负载均衡和反向代理]([https://zhuanlan.zhihu.com/p/99117739](https://zhuanlan.zhihu.com/p/99117739)
)
2. [EasySwoole自定义进程]([http://www.easyswoole.com/Cn/Components/Component/process.html](http://www.easyswoole.com/Cn/Components/Component/process.html)
)
3. [Nginx reload 和 restart的区别]([https://www.cnblogs.com/fanggege/p/12145956.html](https://www.cnblogs.com/fanggege/p/12145956.html)
)
4. [双机热备](https://baike.baidu.com/item/%E5%8F%8C%E6%9C%BA%E7%83%AD%E5%A4%87/2394182?fr=aladdin)

### 4. Nginx 配置
###### nginx.conf

>  当有新版本发布的时候EasySwoole自定义进程会将nginx.conf 的端口改为最新服务

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
    
    // 轮询配置(这里是重点)
    upstream  easyswoole.relase.com {
           server    127.0.0.1:9501;
    }

    server {
        listen       8080;
        server_name  localhost;
    }

    include servers/*;
}

```

###### es-release.conf
```
server {
        listen       80;
        server_name  easyswoole.relase.com;

        location / {
        	root html;
        	index index.html index.htm;
        	proxy_pass http://easyswoole.relase.com; // 这里是重点
        }

        access_log /usr/local/etc/nginx/logs/es.access.log main;
        error_log /usr/local/etc/nginx/logs/es.error.log error;
}%
```

### 5. EasySwoole 代码实现
> 代码只提供实现思路，并且这种脚本，最好单独去做，比如用shell脚本,防止服务down调无法正常部署代码

###### 自定义进程文件
```php
<?php
namespace App\Relase;
use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine;
class Relase extends AbstractProcess
{

    protected function run($arg)
    {
        go(static function () {
            while (true)
            {

                $shellLog = ' 2>> /Users/xxx/sites/shell.log';
                error_log('开始检测代码是否更新5'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                // 检查Git是否有新代码发布
                $diffExec = 'cd ' .EASYSWOOLE_ROOT. '; git fetch; git diff --stat master origin/master;';
                $pullResult = exec($diffExec);
                error_log(json_encode($pullResult), 3, '/Users/xxx/sites/es-log.log');

                if ($pullResult !== '') {
                    error_log('有新版本发布'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    // 新版本项目的目录
                    $newVersionPath = '/Users/xxx/sites/relase-'.time();

                    // 开始clone, 初始化代码
                    $cloneExec = "git clone https://github.com/huizhang-Easyswoole/release.git {$newVersionPath} {$shellLog};cd {$newVersionPath} {$shellLog};composer update {$shellLog}; {$shellLog}";
                    $res = exec($cloneExec, $a, $b);
                    error_log('新版本代码clone'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    // 判断当前是哪个端口正在服务
                    $lsofExec = "lsof -i:9501 {$shellLog}";
                    $lsofResult = exec($lsofExec);
                    $newPort = 9501;
                    $oldPort = 9502;
                    if ($lsofResult !== '') {
                        $newPort = 9502;
                        $oldPort = 9501;
                    }

                    // 将另一个闲置的端口，替换到新版本中
                    error_log('开始替换端口'.$newPort.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    $devConfig = file_get_contents($newVersionPath.'/dev.php');
                    $devConfig = str_replace($oldPort, $newPort, $devConfig);
                    file_put_contents($newVersionPath.'/dev.php', $devConfig);

                    // 启动新服务(这一刻新旧服务是同时存在的)
                    error_log('新服务启动'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $startExec = "cd {$newVersionPath}; php easyswoole start d {$shellLog}";
                    exec($startExec);

                    // 替换nginx配置
                    error_log('开始替换ng端口'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $ngConfigPath = '/usr/local/etc/nginx/nginx.conf';
                    $ngConfig  = file_get_contents($ngConfigPath);
                    $ngConfig = str_replace($oldPort, $newPort, $ngConfig);
                    file_put_contents($ngConfigPath, $ngConfig);

                    // 重启Nginx
                    error_log('重启ng'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $reloadNgExec = "nginx -s reload {$shellLog}";
                    exec($reloadNgExec);

                    // 停掉旧服务
                    error_log('旧服务停掉'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $stopExec = "cd ".EASYSWOOLE_ROOT."; php easyswoole stop {$shellLog}";
                    exec($stopExec);

                    // 每30秒同步一次代码
                    Coroutine::sleep(30);
                } else {
                    error_log('无新版本'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                }

            }
        });

    }

}
```

###### 进程注册
```php
<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use App\Relase\Relase;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.1
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $process = new Relase('Es-relase');
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($process);
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
````

### 6. 测试
###### 绑定host
```
127.0.0.1 easyswoole.relase.com
```

###### 访问easyswoole.relase.com

![image.png](/Images/Other/Deploy/result.jpg)

###### 查看Nginx配置的端口

```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9501;
```

###### 发布新版本
> 重新clone一份代码，更改内容提交。

###### 查看Nginx配置的端口
```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9502;
```
