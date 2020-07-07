---
title: easyswoole swoole-一键协程化
meta:
  - name: description
    content: easyswoole 一键协程化
  - name: keywords
    content: easyswoole 一键协程化|easyswoole|swoole|coroutine
---

# Runtime

`Swoole4+`提供了协程，所有业务代码全是同步的，但底层io是异步，保证并发的同时避免了传统异步回调所带了的离散代码逻辑和陷入多层回调中，最终导致代码无法维护的问题。

# 函数原型

```php
co::set(['hook_flags' => SWOOLE_HOOK_ALL]); // v4.4+版本
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```
通过 `flags` 设置要 `Hook` 的函数的范围，同时开启多个 `flags` 需要使用 | 操作
```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```
:::warning
被 `hook`的函数要在[协程容器](/Cn/Swoole/Coroutine/scheduler.md)内使用 
:::

# 选项

`flags` 支持的选项：

## SWOOLE_HOOK_ALL

打开所有类型的flags 除 curl

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);// 除curl
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); //hook 所有
```

## SWOOLE_HOOK_TCP

4.1+支持，`tcp socket` 类型的 `stream` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () { // 创建100个协程
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);// 产生协程调度，cpu切到下一个协程，不会阻塞进程
            echo $redis->get('key').PHP_EOL;// 产生协程调度，cpu切到下一个协程，不会阻塞进程
        });
    }
});
```
```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9510);
$http->set(['enable_coroutine' => true]);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);// 产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
    $response->end($redis->get('key'));// 产生协程调度，cpu切到下一个协程(下一个请求)，不会阻塞进程
});

$http->start();
```

## SWOOLE_HOOK_UNIX

v4.2+支持，`unix stream socket` 类型的 `stream` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_UNIX]);

Co\run(function () {
    go(function () {
        $socket = stream_socket_server(
            'unix://easyswoole.sock', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if (!$socket) {
            echo "$errstr ($errno)" . PHP_EOL;
            exit(1);
        }
        while ($client = stream_socket_accept($socket)) {
            $msg = fread($client,1024);
            var_dump($msg);
            fclose($client);
        }
    });
    echo "哈哈哈哈" . PHP_EOL;// 优先执行子协程，但会马上产生协程调度执行此行。
});
```
```php
$client = stream_socket_client("unix://easyswoole.sock", $errno, $errstr);

fwrite($client, 'easyswoole');
```

## SWOOLE_HOOK_UDP

v4.2+支持 `udp socket` 类型的 `stream` 示例：

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDP]);

Co\run(function () {
    go(function () {
        $socket = stream_socket_server(
            'udp://0.0.0.0:9999',
            $errno, $errstr, STREAM_SERVER_BIND
        );
        if (!$socket) {
            echo "$errstr ($errno)" . PHP_EOL;
            exit(1);
        }
        while ($msg = stream_socket_recvfrom($socket, 1024)) {
            var_dump($msg);
        }
    });
    echo "哈哈哈哈" . PHP_EOL;
});
```
```php
$client = stream_socket_client("udp://0.0.0.0:9999", $errno, $errstr);

fwrite($client, 'easyswoole');
```

## SWOOLE_HOOK_UDG

v4.2+支持，`unix dgram socket` 类型的 `stream` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDG]);

Co\run(function () {
    go(function () {
        $socket = stream_socket_server(
            'udg://easyswoole.sock', $errno, $errstr, STREAM_SERVER_BIND
        );
        if (!$socket) {
            echo "$errstr ($errno)" . PHP_EOL;
            exit(1);
        }
        while ($msg = stream_socket_recvfrom($socket, 1024)) {
            var_dump($msg);
        }
    });
    echo "哈哈哈" . PHP_EOL;
});
```
```php
$client = stream_socket_client("udg://easyswoole.sock", $errno, $errstr);

fwrite($client, 'easyswoole');
```

## SWOOLE_HOOK_SSL
v4.2+支持，`ssl socket` 类型的 `stream` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_SSL]);

Co\run(function () {
    go(function () {
        $host = 'easyswoole.com';
        $port = 9999;
        $timeout = 10;
        $cert = '/path/to/your/certchain/certchain.pem';
        $context = stream_context_create(array('ssl' => array('local_cert' => $cert,
        )));
        if ($fp = stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errstr, 30,
            STREAM_CLIENT_CONNECT, $context)) {
            echo "connected\n";
        } else {
            echo "ERROR: $errno - $errstr \n";
        }
    });
    echo "哈哈哈" . PHP_EOL;
});
```
## SWOOLE_HOOK_TLS

v4.2+支持，`tls socket` 类型的 `stream` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

## SWOOLE_HOOK_SLEEP

v4.2+支持，`sleep` 函数的 `hook` 比如 `sleep`、 `usleep` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_SLEEP]);

Co\run(function () {
    go(function () {
        sleep(1);
        echo 'easyswoole' . PHP_EOL;
    });
    go(function () {
        echo 'gaobinzhan' . PHP_EOL;
    });
});
```
> 底层定时器最小粒度`1ms`

## SWOOLE_HOOK_FILE

v4.3+支持
- `fopen`
- `fread`/`fgets`
- `fwrite`/`fwrite`
- `file_get_contents`/`file_put_contents`
- `unlink`
- `mkdir`
- `rmdir`

示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    go(function () {
        $fp = fopen("easyswoole.log", "a+");
        fwrite($fp, str_repeat('A', 1024));
        fwrite($fp, str_repeat('B', 1024));
    });
    echo "here" . PHP_EOL;
});
```

## SWOOLE_HOOK_STREAM_FUNCTION

v4.4+支持，`stream_select()` 的 `hook` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);

Co\run(function () {
    go(function () {
        $fp1 = stream_socket_client("tcp://www.easyswoole.com:80", $errno, $errstr, 30);
        $fp2 = stream_socket_client("tcp://blog.gaobinzhan.com:80", $errno, $errstr, 30);
        if (!$fp1) {
            echo "$errstr ($errno) \n";
        } else {
            fwrite($fp1, "GET / HTTP/1.0\r\nHost: www.easyswoole.com\r\nUser-Agent: curl/7.58.0\r\nAccept: */*\r\n\r\n");
            $r_array = [$fp1, $fp2];
            $w_array = $e_array = null;
            stream_select($r_array, $w_array, $e_array, 10);
            $html = '';
            while (!feof($fp1)) {
                $html .= fgets($fp1, 1024);
                var_dump($html);
            }
            fclose($fp1);
        }
    });
    echo "哈哈哈哈" . PHP_EOL;
});
```
## SWOOLE_HOOK_BLOCKING_FUNCTION

v4.4+支持 （`gethostbyname`、`exec`、`shell_exec`）示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    go(function () {
        while (true) {
            var_dump(shell_exec("ls -l"));
        }
    });
    echo "哈哈哈哈" . PHP_EOL;
});
```
## SWOOLE_HOOK_PROC

v4.4+支持 `proc_open、proc_close、proc_get_status、proc_terminate` 示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_PROC]);

Co\run(function () {
    go(function () {
        $process = proc_open('php', [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ], $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], '<?php echo "EasySwoole\n"; ?>');
            fclose($pipes[0]);

            echo fread($pipes[1], 1024);

            fclose($pipes[1]);
            proc_close($process);
        }
    });

    echo "哈哈哈哈" . PHP_EOL;
});
```

## SWOOLE_HOOK_CURL

v4.4LTS+支持 
- `curl_init`
- `curl_setopt`
- `curl_exec`
- `curl_multi_getcontent`
- `curl_setopt_array`
- `curl_error`
- `curl_getinfo`
- `curl_errno`
- `curl_close`
- `curl_reset` 

示例：
```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    go(function () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.easyswoole.com/");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump($result);
    });
    echo "哈哈哈" . PHP_EOL;
});
```

> `Swoole\Runtime::enableCoroutine()` VS `Co::set(['hook_flags'])`
- `Swoole\Runtime::enableCoroutine()`，可以在服务运行后动态设置flags，调用后当前进程内全局生效
- `Co::set()` 可以理解为php的`ini_set()`，在程序启动前执行
- `Co::set(['hook_flags])` 或者 `Swoole\Runtime::enableCoroutine()` 都应该只调用一次，重复调用会被覆盖。

# 方法

## getHookFlags
作用：获取当前已经hook的flags
方法原型：Swoole\Runtime::getHookFlags(): int;

# 常见的Hook列表

## 可用列表
- `redis` 扩展
- `mysqlnd` 模式的 `pdo_mysql`、`mysqli` 扩展，未启用 `mysqlnd` 不支持协程化
- `soap` 扩展
- `file_get_contents`、`fopen`
- `stream_socket_client` (`predis`、`php-amqplib`)
- `stream_socket_server`
- `stream_select`
- `fsockopen`
- `proc_open`
- `curl`

## 不可用列表
- `mysql`：底层使用 `libmysqlclient`
- `mongo`：底层使用 `mongo-c-client`
- `pdo_pgsql`
- `pdo_ori`
- `pdo_odbc`
- `pdo_firebird`

# API变更

v4.3及之前，enableCoroutine 需要两个参数
```bash
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```
- $enable 打开或关闭 hook
- $flags 选择hook的类型

:::warning
`Runtime::enableCoroutine(false)` 关闭上一次设置的所有选项协程 `Hook` 设置。
:::