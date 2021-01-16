---
title: easyswoole hot standby
meta:
  - name: description
    content: easyswoole hot standby
  - name: keywords
    content: easyswoole hot standby|swoole|swoole framework|easyswooleframework 
---
### 1. introduce 

> This paper mainly introduces that easysoole uses the idea of dual hot standby to realize code non-stop deployment.

### 2. Learning cases

- 1. Deploy the `9501` service first
- 2. Start a single process and periodically poll git branch for new version release
- 3. If a new version is released, a copy of clone is required
- 4. Composer update library
- 5. Start the '9502' service
- 6 change nginx configuration to '9502' and restart

> As long as a new version is released, poll the above steps

`A simple architecture diagram of the whole process`
![image.png](/Images/Other/Deploy/soa.jpg)

### 3. Knowledge points need to be understood in advance

1. [Nginx load balancing and reverse proxy]([https://zhuanlan.zhihu.com/p/99117739](https://zhuanlan.zhihu.com/p/99117739)
)
2. [Easysoole custom process]([http://www.easyswoole.com/Cn/Components/Component/process.html](http://www.easyswoole.com/Cn/Components/Component/process.html)
)
3. [The difference between nginx reload and restart]([https://www.cnblogs.com/fanggege/p/12145956.html](https://www.cnblogs.com/fanggege/p/12145956.html)
)
4. [hot standby](https://baike.baidu.com/item/%E5%8F%8C%E6%9C%BA%E7%83%AD%E5%A4%87/2394182?fr=aladdin)

### 4. Nginx configuration
###### nginx.conf

>  When a new version is released, the easysoole customization process will nginx.conf  Change the port of to the latest service

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
    
    // Polling configuration (here's the point)
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

### 5.Easysoole code implementation
> The code only provides implementation ideas, and it is better to do this script alone, such as using shell script, to prevent the service down call from failing to deploy the code normally

###### Custom process file
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
                error_log('Start checking whether the code is updated 5'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                // Check git for new code releases
                $diffExec = 'cd ' .EASYSWOOLE_ROOT. '; git fetch; git diff --stat master origin/master;';
                $pullResult = exec($diffExec);
                error_log(json_encode($pullResult), 3, '/Users/xxx/sites/es-log.log');

                if ($pullResult !== '') {
                    error_log('A new version has been released'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    // Directory of new version project
                    $newVersionPath = '/Users/xxx/sites/relase-'.time();

                    // Start clone and initialize the code
                    $cloneExec = "git clone https://github.com/huizhang-Easyswoole/release.git {$newVersionPath} {$shellLog};cd {$newVersionPath} {$shellLog};composer update {$shellLog}; {$shellLog}";
                    $res = exec($cloneExec, $a, $b);
                    error_log('New version code clone'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    // Determine which port is currently serving
                    $lsofExec = "lsof -i:9501 {$shellLog}";
                    $lsofResult = exec($lsofExec);
                    $newPort = 9501;
                    $oldPort = 9502;
                    if ($lsofResult !== '') {
                        $newPort = 9502;
                        $oldPort = 9501;
                    }

                    // Replace another idle port with the new version
                    error_log('Start port replacement'.$newPort.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                    $devConfig = file_get_contents($newVersionPath.'/dev.php');
                    $devConfig = str_replace($oldPort, $newPort, $devConfig);
                    file_put_contents($newVersionPath.'/dev.php', $devConfig);

                    // Start a new service (the new and old services exist at the same time)
                    error_log('New service launch'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $startExec = "cd {$newVersionPath}; php easyswoole start d {$shellLog}";
                    exec($startExec);

                    // Replace nginx configuration
                    error_log('Start replacing ng port'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $ngConfigPath = '/usr/local/etc/nginx/nginx.conf';
                    $ngConfig  = file_get_contents($ngConfigPath);
                    $ngConfig = str_replace($oldPort, $newPort, $ngConfig);
                    file_put_contents($ngConfigPath, $ngConfig);

                    // Restart nginx
                    error_log('Restart ng'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $reloadNgExec = "nginx -s reload {$shellLog}";
                    exec($reloadNgExec);

                    // Stop the old service
                    error_log('The old service stopped'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');
                    $stopExec = "cd ".EASYSWOOLE_ROOT."; php easyswoole stop {$shellLog}";
                    exec($stopExec);

                    // Synchronize the code every 30 seconds
                    Coroutine::sleep(30);
                } else {
                    error_log('No new version'.PHP_EOL, 3, '/Users/xxx/sites/es-log.log');

                }

            }
        });

    }

}
```

###### Process registration
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

### 6. test
###### Bind host
```
127.0.0.1 easyswoole.relase.com
```

###### visit easyswoole.relase.com

![image.png](/Images/Other/Deploy/result.jpg)

###### View ports configured by nginx

```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9501;
```

###### Release a new version
> Clone a new code, change the content and submit it.

###### View ports configured by nginx
```
➜  nginx cat nginx.conf | grep 950
           server    127.0.0.1:9502;
```
