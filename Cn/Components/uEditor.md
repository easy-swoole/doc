---
title: easyswoole u-editor(百度编辑器)组件
meta:
  - name: description
    content: EasySwoole提供了一个百度富文本编辑器组件，方便用户开发有关富文本编辑器的业务逻辑。
  - name: keywords
    content: easyswoole链路追踪|swoole u-editor|swoole 百度编辑器
---

# u-editor(百度编辑器)组件

一个用 `EasySwoole` 实现的百度富文本编辑器组件，开箱即用，用户使用几行简单的代码就可以轻松使用富文本编辑器。 

## 组件要求
- php: >= 7.1
- easyswoole/http-client: ^1.3

## 安装
> composer require easyswoole/u-editor

## 仓库地址
[easyswoole/u-editor](https://github.com/easy-swoole/u-editor)


## 基本使用方法
新增一个控制器，继承 `EasySwoole\UEditor\UEditorController`。
```php
<?php
namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\UEditor\UEditorController;

class UEditor extends UEditorController
{

}
```
> 该控制器请求地址为:/UEditor  

修改百度编辑器 `ueditor.config.js` => `window.UEDITOR_CONFIG` => `serverUrl = URL + "/UEditor"`
如图![](/Images/uEditorJsConfig.png)

即可直接使用。

## 补充说明
百度编辑器前端初始化后，会通过 `ueditor.config.js` 获取配置，通过获取到的服务器路径，前端就可以去请求，请求格式为：服务器路径 + "?action=操作方法"。

前端请求路径示例：`http://127.0.0.1:9501/UEditor?action=config&&noCache=1587973402520`   

前端请求之后，将通过 `UEditorController` 的 `index` 方法进行识别 `action`，转发到不同的请求逻辑上，实现百度编辑器的后端接口。

## 自定义使用方法
在 `EasySwoole\UEditor\UEditorController` 控制器中，有默认的实现方法，如果你需要修改配置，可以通过重写控制器方法进行修改，实现自定义配置。

### 保存路径
默认保存路径为 `EASYSWOOLE_ROOT . '/Static'`。可通过属性继承 `$rootPath` 来修改默认的保存路径。

### 权限控制实现
本组件默认控制器的实现是继承 `EasySwoole\Http\AbstractInterface\Controller`，如果你有需要登录用户上传等权限验证的需求，可以直接复制 `EasySwoole\UEditor\UEditorController` 里面的代码，然后重新实现自定义控制器并继承 `EasySwoole\Http\AbstractInterface\Controller`，然后重写控制器的方法，即可实现权限控制。
