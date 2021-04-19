---
title: easyswoole 基础使用-协程注意事项
meta:
  - name: description
    content: easyswoole 基础使用-协程注意事项
  - name: keywords
    content: easyswoole 基础使用-协程注意事项
---

# 协程注意事项

## 变量使用

- 在协程中，需要特别注意，不要使用 `$_GET`、`$_POST`、`$GLOBALS` 等超全局变量，尤其是需要修改变量值并读取时，将造成协程间变量数据错乱。  
- 协程中访问外部变量必须使用 `use` 关键字，或者传形参方式，不能引用变量。  
- 如果需要做多协程之间的通信，可使用 `channel` 方式通信。

## 扩展冲突

- `swoole` 协程与 `xdebug`、`xhprof`、`blackfire` 等 `zend` 扩展不兼容，例如不能使用 `xhprof` 对协程 `server` 进行性能分析采样。

## 退出协程

在 `Swoole >= v4.1.0` 之后，使用 `exit` 退出将只退出当前协程，并在当前协程抛出一个 `Swoole\ExitException` 异常。

在 `Swoole < v4.1.0` 时，如果使用 `exit`，将会造成整个进程退出，禁止使用。  

通过拦截 `Swoole\ExitException` 异常，可获得这次退出的具体异常信息。

简单使用示例：

```php
<?php
go(function () {
    try {
        test();
    } catch (\Swoole\ExitException $exception) {
        var_dump($exception);
    }
});

function test()
{
    test2();
}
function test2()
{
    exit(2);
}
```