---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 消息群发

微信的群发消息接口有各种乱七八糟的注意事项及限制，具体请阅读 [微信官方文档](https://developers.weixin.qq.com/doc/offiaccount/Shopping_Guide/task-account/shopping-guide.addGuideMassendJob.html)。

## 发送消息

以下所有方法均有第二个参数 `$to` 用于指定接收对象：

- 当 `$to` 为整型时为标签 `id`
- 当 `$to` 为数组时为用户的 `openid` 列表（至少两个用户的 `openid`）
- 当 `$to` 为 `null` 时表示全部用户

暂略。

### 文本消息

暂略。

### 图文消息

暂略。

### 图片消息

暂略。

### 语音消息

暂略。

### 视频消息

暂略。

### 卡券消息

暂略。

### 发送预览群发消息给指定的 openId 用户

暂略。

### 发送预览群发消息给指定的微信号用户

暂略。

### 删除群发消息

暂略。

### 查询群发消息发送状态

暂略。
