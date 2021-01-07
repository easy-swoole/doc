---
title: easyswoole Smtp Client
meta:
  - name: description
    content: Easyswoole provides a mail sending component. E-mail is a kind of communication mode that provides information exchange by electronic means. It is the most widely used service on the Internet. Email is essential to almost every web application, whether it's a newsletter or an order confirmation. This component uses the swoole cooperation client to realize the sending of e-mail。
  - name: keywords
    content: easyswoole Smtp Client|swoole smtp|swoole coroutine smtp
---

# Email content

Support two types ofr text and html

## Text

#### Example
```php
$mimeBean = new \EasySwoole\Smtp\Message\Text();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```

#### Effect
![](/Images/Passage/Smtp/text.png) 

## Html
```php
$mimeBean = new \EasySwoole\Smtp\Message\Html();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```

#### Effect
![](/Images/Passage/Smtp/html.png) 

## Attachments
```php
$mimeBean = new \EasySwoole\Smtp\Message\Text();
//$mimeBean = new \EasySwoole\Smtp\Message\Html();

...

// Create attachment
$createAttachment = Attach::create('./test.txt');

// Get attachment
$mimeBean->addAttachment($createAttachment);

...
```

