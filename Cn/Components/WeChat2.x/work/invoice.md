---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 电子发票

```php
<?php
$config = [
    // 企业微信后台的 企业 ID
    'corpId' => 'xxxxxxxxxxxxxxxxx',
    
    // 企业微信后台的 secret
    'corpSecret' => 'xxxxxxxxxxxxxxxxx',
    
    // ...
];

// 企业微信
$work = \EasySwoole\WeChat\Factory::work($config);
```

## 查询电子发票

API：

```php
mixed get(string $cardId, string $encryptCode)
```

Example：

```php
<?php
$work->invoice->get('CARDID', 'ENCRYPTCODE');
```

## 批量查询电子发票

API：

```php
mixed select(array $invoices)
```

> `$invoices`: 发票参数列表

Example：

```php
<?php
$invoices = [
    ["card_id" => "CARDID1", "encrypt_code" => "ENCRYPTCODE1"],
    ["card_id" => "CARDID2", "encrypt_code" => "ENCRYPTCODE2"]
];

$work->invoice->select($invoices);
```

## 更新发票状态

API：

```php
mixed update(string $cardId, string $encryptCode, string $status)
```

:::tip
 `$status`: 发报销状态
- INVOICE_REIMBURSE_INIT：发票初始状态，未锁定；
- INVOICE_REIMBURSE_LOCK：发票已锁定，无法重复提交报销;
- INVOICE_REIMBURSE_CLOSURE:发票已核销，从用户卡包中移除
:::

## 批量更新发票状态

API：

```php
mixed batchUpdate(array $invoices, string $openid, string $status)
```

Example：

```php
<?php
$invoices = [
    ["card_id" => "CARDID1", "encrypt_code" => "ENCRYPTCODE1"],
    ["card_id" => "CARDID2", "encrypt_code" => "ENCRYPTCODE2"]
];

$openid = 'oV-gpwSU3xlMXbq0PqqRp1xHu9O4';

$status = 'INVOICE_REIMBURSE_CLOSURE';

$work->invoice->batchUpdate($invoices, $openid, $status);
```