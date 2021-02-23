---
title: easyswoole Smtp Client
meta:
  - name: description
    content: Easyswoole provides a mail sending component. E-mail is a kind of communication mode that provides information exchange by electronic means. It is the most widely used service on the Internet. Email is essential to almost every web application, whether it's a newsletter or an order confirmation. This component uses the swoole cooperation client to realize the sending of e-mail。
  - name: keywords
    content: easyswoole Smtp Client|swoole smtp|swoole coroutine smtp
---
# Content configuration

## set

Set protocol version
```php
public function setMimeVersion($mimeVersion): void
```

Set contentType
```php
public function setContentType($contentType): void
```

Setting characters
```php
public function setCharset($charset): void
```

Set encoding
```php
public function setContentTransferEncoding($contentTransferEncoding): void
````

Set up themes
```php
public function setSubject($subject): void
```

Set mail content
```php
public function setBody($body): void
````

Add attachments
```php
public function addAttachment($attachment)
```

## get

Get protocol version
```php
public function getMimeVersion()
```

Get contenttype
```php
public function getContentType()
```

Get Character
```php
public function getCharset()
```

Get Acquisition
```php
public function getContentTransferEncoding()
```

Get theme
```php
public function getSubject()
```

Get mail content
```php
public function getBody()
```

Get attachment
```php
public function getAttachments()
```
