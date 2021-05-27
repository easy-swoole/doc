---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# OA

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

## 获取打卡数据

API：

```php
mixed checkinRecords(int $startTime, int $endTime, array $userList, int $type = 3)
```

> `$type`: 打卡类型 1：上下班打卡；2：外出打卡；3：全部打卡

```php
<?php
// 全部打卡数据
$work->oa->checkinRecords(1492617600, 1492790400, ["james","paul"]);

// 获取上下班打卡
$work->oa->checkinRecords(1492617600, 1492790400, ["james","paul"], 1);

// 获取外出打卡
$work->oa->checkinRecords(1492617600, 1492790400, ["james","paul"], 2);
```

## 获取审批数据

API：

```php
mixed approvalRecords(int $startTime, int $endTime, int $nextNumber = null)
```

> `$nextNumber`: 第一个拉取的审批单号，不填从该时间段的第一个审批单拉取

```php
<?php
$work->oa->approvalRecords(1492617600, 1492790400);

// 指定第一个拉取的审批单号，不填从该时间段的第一个审批单拉取
$work->oa->approvalRecords(1492617600, 1492790400, '201704240001');
```