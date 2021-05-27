---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 物流助手

## 生成运单

```php
<?php
$miniProgram->express->createWaybill($data);

// 例如：
try {
    $ret = $miniProgram->express->createWaybill($data);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}

// 成功返回
{
  "order_id": "01234567890123456789",
  "waybill_id": "123456789",
  "waybill_data": [
    {
      "key": "SF_bagAddr",
      "value": "广州"
    },
    {
      "key": "SF_mark",
      "value": "101- 07-03 509"
    }
  ]
}

// 失败返回，抛出 \EasySwoole\WeChat\Kernel\Exceptions\HttpException 异常
```

## 取消运单

```php
<?php
$miniProgram->express->deleteWaybill($data);

// 例如：
try {
    $ret = $miniProgram->express->deleteWaybill($data);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 获取支持的快递公司列表

```php
<?php
$miniProgram->express->listProviders();

// 例如：
try {
    $ret = $miniProgram->express->listProviders();
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}

// 列表：
{
  "count": 8,
  "data": [
    {
      "delivery_id": "BEST",
      "delivery_name": "百世快递"
    },
    ...
  ]
}
```

## 获取运单数据

```php
<?php
$miniProgram->express->getWaybill($data);

// 例如：
try {
    $ret = $miniProgram->express->getWaybill($data);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 查询运单轨迹

```php
<?php
$miniProgram->express->getWaybillTrack($data);

// 例如：
try {
    $ret = $miniProgram->express->getWaybillTrack($data);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 获取打印员

```php
<?php
$miniProgram->express->getPrinter();

// 例如：
try {
    $ret = $miniProgram->express->getPrinter();
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 获取电子面单余额

仅在使用加盟类快递公司时，才可以调用。

```php
<?php
$miniProgram->express->getBalance($deliveryId, $bizId);

// 例如：
try {
    $ret = $miniProgram->express->getBalance('YTO', 'xyz');
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 绑定打印员

若需要使用微信打单 `PC` 软件，才需要调用。

```php
<?php
$miniProgram->express->bindPrinter($openid);

// 例如：
try {
    $ret = $miniProgram->express->bindPrinter($openid);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```

## 解绑打印员

若需要使用微信打单 PC 软件，才需要调用。

```php
<?php
$miniProgram->express->unbindPrinter($openid);

// 例如：
try {
    $ret = $miniProgram->express->unbindPrinter($openid);
} catch (\EasySwoole\WeChat\Kernel\Exceptions\HttpException $httpException) {
    $error = $httpException->getMessage();
}
```