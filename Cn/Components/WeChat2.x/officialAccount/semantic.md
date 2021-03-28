---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 语义理解

> 貌似此接口已经下线，调用无正确返回值。

```php
$officialAccount->semantic->query(string $keyword, string $categories, array $optional = []);
```

参数说明：

- `string $keyword` 为关键字
- `string $categories` 需要使用的服务类型，多个用 “,” 隔开字符串，不能为空
- `$optional 为其它属性：
  - `float latitude` 纬度坐标，与经度同时传入；与城市二选一传入
  - `float longitude` 经度坐标，与纬度同时传入；与城市二选一传入
  - `string city` 城市名称，与经纬度二选一传入
  - `stringregion` 区域名称，在城市存在的情况下可省；与经纬度二选一传入
  - `string uid` 用户唯一 `id`（非开发者 `id`），用户区分公众号下的不同用户（建议填入用户 `openid`），如果为空，则无法使用上下文理解功能。`appid` 和 `uid` 同时存在的情况下，才可以使用上下文理解功能。
  
> 注：单类别意图比较明确，识别的覆盖率比较大，所以如果只要使用特定某个类别，建议将 `category` 只设置为该类别。

使用示例：

```php
<?php

$result = $officialAccount->semantic->query('查一下明天从北京到上海的南航机票', "flight,hotel", array(
            'city' => '北京', 
            'uid' => '123456'
        ));

// 查询参数：
/*
{
	"query": "查一下明天从北京到上海的南航机票",
	"city": "北京",
	"category": "flight,hotel",
	"appid": "wxaaaaaaaaaaaaaaaa",
	"uid": "123456"
}
*/
```

返回值示例：

```json
{
	"errcode": 0,
	"query": "查一下明天从北京到上海的南航机票",
	"type": "flight",
	"semantic": {
		"details": {
			"start_loc": {
				"type": "LOC_CITY",
				"city": "北京市",
				"city_simple": "北京",
				"loc_ori": "北京"
			},
			"end_loc": {
				"type": "LOC_CITY",
				"city": "上海市",
				"city_simple": "上海",
				"loc_ori": "上海"
			},
			"start_date": {
				"type": "DT_ORI",
				"date": "2014-03-05",
				"date_ori": "明天"
			},
			"airline": "中国南方航空公司"
		},
		"intent": "SEARCH"
	}
}
```