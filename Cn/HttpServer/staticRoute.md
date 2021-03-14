---
title: easyswoole 路由
meta:
  - name: description
    content: easyswoole URL解析规则 自定义路由
  - name: keywords
    content:  easyswoole URL解析规则| easyswoole 自定义路由 |swoole web框架
---

# 静态路由
静态路由是直接通过 `URl` 映射，因此效率高，但作用也是有限的。

## URL解析规则
仅支持 `PATHINFO` 模式的 `URL` 解析，且与控制器名称(方法)保持一致，控制器搜索规则为优先完整匹配模式

## 解析规则

在没有路由干预的情况下，内置的解析规则支持无限级嵌套目录，如下方两个例子所示

- <http://serverName/api/auth/login>

    对应执行的方法为 `\App\HttpController\Api\Auth::login()`

- <http://serverName/a/b/c/d/f>

    - 如果 `f` 为控制器名，则执行的方法为 `\App\HttpController\A\B\C\D\F::index()`

    - 如果 `f` 为方法名，则执行的方法为 `\App\HttpControllers\A\B\C\D::f()`

    - 如果最后的路径为 `index` 时，底层会自动忽略，并直接调用控制器的默认方法(也就是 index)

## 解析层级

理论上 `EasySwoole` 支持无限层级的 URL -> 控制器 映射，但出于系统效率和防止恶意 `URL` 访问， 系统默认为 3 级，若由于业务需求，需要更多层级的 `URL` 映射匹配，请在框架初始化事件中向 `DI` 注入常量 `SysConst::HTTP_CONTROLLER_MAX_DEPTH`，值为 URL 解析的最大层级，注入方式如下代码，允许 URL 最大解析至 5 层

```php
public static function initialize()
{
	\EasySwoole\Component\Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH, 5);
}
```

## 特殊情况

当控制器和方法都为 `index` 时，可直接忽略不写

- 如果方法为 `index`，则可以忽略:  
    如果对应执行方法名为 `\App\HttpController\Api\User::index()`
    url 可直接写 <http://serverName/api/User>  

- 如果控制器和方法都为 `Index`，则可以忽略
    如果对应执行方法名为 `\App\HttpController\Index::index()`
    url 可直接写 <http://serverName/>   

- index 忽略规则理论支持无限层级，根据解析层级最大进行逐层查找

::: warning 
  注意，`EasySwoole` 的 `URL` 路径区分大小写，控制器首字母支持小写转换
:::
