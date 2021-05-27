---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 小程序码

## 获取小程序码

### 接口 A：适用于需要的码数量较少的业务场景

> API：

```php
$miniProgram->appCode->get(string $path, array $optional = []);
```

其中 $optional 为以下可选参数：

- `width int` - 默认 `430` 二维码的宽度
- `auto_color` 默认 `false` 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
- `line_color` 数组，`auto_color` 为 `false` 时生效，使用 `rgb` 设置颜色，例如：`["r" => 0,"g" => 0,"b" => 0]`。

> 示例代码：

```php
<?php
$response = $miniProgram->appCode->get('path/to/page');
// 或者
$response = $miniProgram->appCode->get('path/to/page', [
    'width' => 600,
    // ...
]);

// 或者指定颜色
$response = $miniProgram->appCode->get('path/to/page', [
    'width' => 600,
    'line_color' => [
        'r' => 105,
        'g' => 166,
        'b' => 134,
    ],
]);

// $response 成功时为 EasySwoole\WeChat\Kernel\Psr\StreamResponse 实例，失败时会返回 bool 值或抛出异常（可通过捕获异常的形式获取失败原因）

// 保存小程序码到文件
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->save('/path/to/directory');
}

// 或
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->saveAs('/path/to/directory', 'appcode.png');
}
```

### 接口 B：适用于需要的码数量极多，或仅临时使用的业务场景

> API：

```php
$miniProgram->appCode->getUnlimit(string $scene, array $optional = []);
```

其中 `$scene` 必填，`$optinal` 与 `get` 方法一致，多一个 `page` 参数。

> 示例代码：

```php
<?php
$response = $miniProgram->appCode->getUnlimit('scene-value', [
    'page'  => 'path/to/page',
    'width' => 600,
]);
// $response 成功时为 EasySwoole\WeChat\Kernel\Psr\StreamResponse 实例，失败时会返回 bool 值或抛出异常（可通过捕获异常的形式获取失败原因）

// 保存小程序码到文件
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->save('/path/to/directory');
}
// 或
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->saveAs('/path/to/directory', 'appcode.png');
}
```

## 获取小程序二维码

> API: 

```php
$miniProgram->appCode->getQrCode(string $path, int $width = null);
```

其中 `$path` 必填，其余参数可留空。


> 示例代码：

```php
<?php
$response = $miniProgram->appCode->getQrCode('/path/to/page');

// $response 成功时为 \EasySwoole\WeChat\Kernel\Psr\StreamResponse 实例，失败时会返回 bool 值或抛出异常（可通过捕获异常的形式获取失败原因）

// 保存小程序码到文件
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->save('/path/to/directory');
}

// 或
if ($response instanceof \EasySwoole\WeChat\Kernel\Psr\StreamResponse) {
    $filename = $response->saveAs('/path/to/directory', 'appcode.png');
}
```