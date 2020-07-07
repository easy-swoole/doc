---
title: easyswoole swoole注意事项
meta:
  - name: description
    content: easyswoole swoole注意事项
  - name: keywords
    content: easyswoole swoole注意事项|easyswoole|swoole|
---

## 注意事项
由于swoole的常驻内存以及协程切换的特性,在使用一些php追踪调试的扩展时可能会造成swoole崩溃,安装swoole时必须禁用以下扩展:
- xhprof
- xdebug
- phptrace
- aop
- molten
- phalcon

::: warning
建议使用var_dump,return进行调试,也可以使用`        debug_print_backtrace()` 进行栈打印跟踪
:::


::: warning
xhprof 只要不在swoole服务中使用即可,可以开启
:::

