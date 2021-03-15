# 文档编写规范事项

## 文档风格

为了保障EasySwoole文档的风格基本统一，方便使用者阅读，请使用下面提供的示例风格编写文档内容。

### 多版本
每个章节文档默认显示最新版本对应的文档。如果组件错在多个版本，或者需要特殊说明。则请在对应章节文档开头处写上对应的版本连接。
效果如：[说明2](/Update/doc2.md)或者如<layerOpen href="/Update/doc2.md">说明2</layerOpen>
```
风格1：
[说明2](/Update/doc2.md)
风格2：
<layerOpen href="/Update/doc2.md">说明2</layerOpen>
```


### 层级风格

EasySwoole 文档采用三级目录风格，语言->模块->章节 的形式进行组织内容，如某内容并不具体属于某模块，可以省略，具体组织方式如下：

```
└── 语言
    ├── 模块-1
    │   └── 章节.md
    ├── 模块-2
    └── 章节.md
```

### 分层原则

- `语言` 毫无争议，目前仅支持`zh-cn`和`en`
- `模块` 通常为相似功能的集合，或某个较为复杂的组件，新增一个模块应当谨慎
- `章节` 应当强调阅读顺序，从上到下渐进，非模块的章节可以长一些，相反模块的章节不应当过长

### 关键词风格

文档系统会对 `.md` 格式的特定语法进行美化，为了方便大家统一风格，请使用以下语法进行关键词修饰。

#### 希望用户在终端执行命令

如果希望用户在终端执行某命令，可以使用 `引用` 语法。

```md
> composer require easyswoole/easyswoole=3.4.x
```

效果如下：
> composer require easyswoole/easyswoole=3.4.x

如果希望用户在特定的地方执行命令，最好使用 `重点` 注释说明

```md
*请在终端执行以下命令*
> composer require easyswoole/easyswoole=3.4.x
```

效果如下：

*请在终端执行以下命令*
> composer require easyswoole/easyswoole=3.4.x

#### 高亮关键名词

如果希望在描述中高亮某些名词，可以使用 `反引号` 进行修饰

```md
强大的 `TCP/UDP Server` 框架，`多线程`，`EventLoop`，`事件驱动`，`异步`，`Worker进程组`，`Task异步任务`，`毫秒定时器`，`SSL/TLS隧道加密`
```

效果如下：

强大的 `TCP/UDP Server` 框架，`多线程`，`EventLoop`，`事件驱动`，`异步`，`Worker进程组`，`Task异步任务`，`毫秒定时器`，`SSL/TLS隧道加密`


#### 描述函数或者 `function`

描述函数和 `function` 必须简述其作用和输入输出参数，使用 `代码块` 指令最方便

```md
```php
DbManager::getInstance()->invoke(callable function(MysqlClient $client));
```

#### 希望用户注意或警告用户

如果希望提醒用户或者警告用户，可以使用扩展语法[^非md语法] `tip` `warning` 进行描述

```md
::: tip
旧版本的invoke没有return值，请更新orm版本。
:::

::: warning  
    注意EasySwoole的Temp目录不在虚拟机与宿主机共享目录下，否则会导致没有权限创建UnixSocket链接
:::
```

效果如下：

::: tip
旧版本的invoke没有return值，请更新orm版本。
:::

::: warning  
注意EasySwoole的Temp目录不在虚拟机与宿主机共享目录下，否则会导致没有权限创建UnixSocket链接
:::

## 章节案例

### 组件库基本结构

组件库的章节主要围绕如何让用户快速上手组件，和如果遇到问题如何解决构成的；优秀的文档应该是站在用户的角度进行编写的，提供可运行的代码片段非常重要。


```md
# 组件名称

在章节开头描述组件的作用和用途，以及依赖的其他组件或者注意事项；以及组件主要是用来做什么的。

## 组件要求

最好在这里说明组件要求，防止用户在安装时遭遇失败。

- php >= 7.1
- swoole >= 4.4

## 安装方法

> composer require 组件名称

## 仓库地址

在这里给出GitHub的仓库地址 使用[仓库名称](https://github.com/easy-swoole) 来创建一个超链接

## 基本使用

在这里描述组件的常用方法，以及用来做什么，具体怎么使用；给出代码。


## 进阶使用

在这里描述组件的进阶使用方法，比如可以用作其他相关业务，给出示例。


## 相关仓库

在这里给出组件的demo仓库，或基于此组件的开源项目。
```


### 非组件库案例

非组件案例一般是指常用的核心功能，或者是某个经典案例示例。


```md
# 功能名称

## 功能介绍

在这里介绍功能的主要用途，和通常适用于什么场景。

## 相关Class位置

在这里给出Class的Github地址，或是完整的`namespace`

- HttpController
    - [GitHub](https://github.com/easy-swoole/http/blob/master/src/AbstractInterface/Controller.php)
    - `namespace`: `EasySwoole\Http\AbstractInterface`

## 核心方法

在这里给出核心或常用方法的原型；

## 注意事项

在这里给出常见的注意事项

```

### 版本痕迹

如果某个方法是特定版本增加，应当使用 `重点` 或 `tip` 语法进行描述。

```md
*3.3.4新增*

::: tip
3.3.4新增
:::
```

效果如下：

*3.3.4新增*

::: tip
3.3.4新增
:::

