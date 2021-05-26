---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 数据统计与分析

获取小程序概况趋势：

```php
$miniProgram->dataCube->summaryTrend('20170313', '20170313');
```

开始日期与结束日期的格式为 `YYYYmmdd`。

## API

:::tip
- summaryTrend(string $from, string $to); 概况趋势
- dailyVisitTrend(string $from, string $to); 访问日趋势
- weeklyVisitTrend(string $from, string $to); 访问周趋势
- monthlyVisitTrend(string $from, string $to); 访问月趋势
- visitDistribution(string $from, string $to); 访问分布
- dailyRetainInfo(string $from, string $to); 访问日留存
- weeklyRetainInfo(string $from, string $to); 访问周留存
- monthlyRetainInfo(string $from, string $to); 访问月留存
- visitPage(string $from, string $to); 访问页面
- userPortrait(string $from, string $to); 用户画像分布数
:::