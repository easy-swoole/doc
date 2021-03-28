---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# JSSDK

微信 `JSSDK` 官方文档：[https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115)

## API

获取 `JSSDK` 的配置数组

```php
$officialAccount->jssdk->buildConfig(string $url, array $apis, bool $debug = false, bool $beta = false, array $openTagList = []);
```

返回 `JSON` 字符串。

设置当前 `URL`

```php
$officialAccount->jssdk->buildConfig($url, []);
```

如果不想用默认读取的 `URL`，可以使用此方法手动设置，通常不需要。


使用示例：

我们可以生成 `js` 配置文件：

```html
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $officialAccount->jssdk->buildConfig(array('updateAppMessageShareData', 'updateTimelineShareData'), true) ?>);
</script>
```

结果如下：

```html
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
wx.config({
    debug: true, // 请在上线前删除它
    appId: 'wx3cf0f39249eb0e60',
    timestamp: 1430009304,
    nonceStr: 'qey94m021ik',
    signature: '4F76593A4245644FAE4E1BC940F6422A0C3EC03E',
    jsApiList: ['updateAppMessageShareData', 'updateTimelineShareData']
});
</script>
```