---
title: easyswoole swoole-协程进程管理
meta:
  - name: description
    content: easyswoole swoole-协程进程管理
  - name: keywords
    content: easyswoole swoole-协程进程管理|easyswoole swoole-进程池|easyswoole|swoole|process/pool
---

# 协程进程管理

由于在协程空间内进行 `fork` 会带着其它协程上下文，所以底层禁止在 `Coroutine` 中使用 `Process`。可以使用：
- `System::exec()` 或者 `Runtime Hook` + `shell_exec`实现外面程序运行
- `Runtime Hook` + `proc_open` 实现父子进程交互通信

## 简单示例代码

`main.php`
```php
<?php
Swoole\Coroutine::create(function () {
    Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
    $process = proc_open('php ' . __DIR__ . '/read_stdin.php', [
        ['pipe', 'r'],
        ['pipe', 'w'],
        ['file', '/tmp/error-output.txt', 'a'],
    ], $pipes);

    fwrite($pipes[0], "hello world !\n");
    echo fread($pipes[1], 8192);

    fclose($pipes[0]);
    proc_close($process);
});
```

`read_stdin.php`
```php
<?php
$msg = fgets(STDIN);
if ($msg) {
    echo 'Receive：'.$msg;
}
```