---
title: easyswoole开发工具
meta:
  - name: description
    content: easyswoole提供了高效的开发工具。
  - name: keywords
    content: easyswoole开发工具
---
# 开发工具

## IDE 代码提示组件
为了让用户能够更高效地进行 `EasySwoole` 开发，我们提供了 `IDE` 代码提示组件。在 `PhpStrom` 等 `IDE` 环境下开发时，该组件能够对 `Swoole` 内置函数、类等自动提示，该组件安装方法如下：
> composer require easyswoole/swoole-ide-helper
  
  
## IDE 注解提示组件
用户在使用 [EasySwoole 注解组件](/HttpServer/Annotation/install.md) 进行 `EasySwoole` 开发时，仍需要 `use` 注解相对应的命名空间。这显然不是一个高效的做法。我们推荐在 `PhpStorm` 环境下进行开发，并且在 `PhpStorm` 中安装 Jetbrain 自带的 `PHP Annotation` 组件，可以提供注解命名空间自动补全、注解属性代码提醒、注解类跳转等非常有帮助的。(PhpStorm 2019 以上版本的 IDE，该组件可能不能正常使用。)