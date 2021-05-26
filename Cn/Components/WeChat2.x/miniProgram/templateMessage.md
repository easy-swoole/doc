---
title: easyswoole微信SDK
meta:
  - name: description
    content: easyswoole微信SDK
  - name: keywords
    content: easyswoole微信SDK|swoole微信SDK
---

# 模板消息

## 获取小程序模板库标题列表

```php
$miniProgram->templateMessage->list($offset, $count);
```

## 获取模板库某个模板标题下关键词库

```php
$miniProgram->templateMessage->get($id);
```

## 组合模板并添加至帐号下的个人模板库

```php
$miniProgram->templateMessage->add($id, $keywordIdList);
```

## 获取帐号下已存在的模板列表

```php
$miniProgram->templateMessage->getTemplates($offset, $count);
```

## 删除帐号下的某个模板

```php
$miniProgram->templateMessage->delete($templateId);
```

## 发送模板消息

```php
$miniProgram->templateMessage->send([
    'touser' => 'user-openid',
    'template_id' => 'template-id',
    'page' => 'index',
    'form_id' => 'form-id',
    'data' => [
        'keyword1' => 'VALUE',
        'keyword2' => 'VALUE2',
        // ...
    ],
]);

```