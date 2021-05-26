---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# JS SDK

企业微信 `JSSDK` 官方文档：[https://open.work.weixin.qq.com/api/doc/90000/90136/90514](https://open.work.weixin.qq.com/api/doc/90000/90136/90514)

## API

### 获取 `config` 接口配置

```php
$work->jssdk->buildConfig(string $url, array $apis, bool $debug = false, bool $beta = false, array $openTagList = []): string;
```

返回 `JSON` 字符串，可以转成数组，然后直接使用到网页中。

#### 示例

我们可以生成 `js` 配置文件：

```html
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $work->jssdk->buildConfig('http://test.com', array('updateAppMessageShareData', 'updateTimelineShareData')) ?>);
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

### 获取 `agentConfig` 接口配置

调用 `wx.agentConfig` 之前，必须确保先成功调用 `wx.config`. 注意：从企业微信 `3.0.24` 及以后版本（可通过企业微信 `UA` 判断版本号），无须先调用 `wx.config`，可直接 `wx.agentConfig`。

```php
<?php
$work->jssdk->buildAgentConfig(
    array $jsApiList, // 需要检测的JS接口列表
    $agentId, // 应用id
    bool $debug = false,
    bool $beta = false,
    bool $json = true,
    array $openTagList = [],
    string $url = null // 设置当前URL
);
```

#### 前端示例

```html
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script src="https://open.work.weixin.qq.com/wwopen/js/jwxwork-1.0.0.js"></script>
<script type="text/javascript" charset="utf-8">
    wx.config({
        debug: true, // 请在上线前删除它
        appId: 'wx3cf0f39249eb0e60',
        timestamp: 1430009304,
        nonceStr: 'qey94m021ik',
        signature: '4F76593A4245644FAE4E1BC940F6422A0C3EC03E',
        jsApiList: ['updateAppMessageShareData', 'updateTimelineShareData']
    });
    wx.ready(function () {
        wx.agentConfig({ //调用agentConfig
            corpid: '',
            agentid: '',
            timestamp: '',
            nonceStr: '',
            signature: '',
            jsApiList: ['selectExternalContact'],
            success: function (res) {
                // 回调
            },
            fail: function (res) {
                if (res.errMsg.indexOf('function not exist') > -1) {
                    alert('版本过低请升级')
                }
            }
        });
    });
    wx.error(function (res) {
        console.log('失败');
    });
</script>
```