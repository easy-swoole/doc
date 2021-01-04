---
title: easyswoole Smtp Client
meta:
  - name: description
    content: Easyswoole provides a mail sending component. E-mail is a kind of communication mode that provides information exchange by electronic means. It is the most widely used service on the Internet. Email is essential to almost every web application, whether it's a newsletter or an order confirmation. This component uses the swoole cooperation client to realize the sending of e-mail。
  - name: keywords
    content: easyswoole Smtp Client|swoole smtp|swoole coroutine smtp
---

# Smtp

Easyswoole provides a mail sending component. E-mail is a kind of communication mode that provides information exchange by electronic means. It is the most widely used service on the Internet. Email is essential to almost every web application, whether it's a newsletter or an order confirmation. This component uses the swoole cooperation client to realize the sending of e-mail。
## Install
```php
composer require easyswoole/smtp
```
## Use
```php
use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Attach;
// Must use go function
go(function (){
    $config = new MailerConfig();
    $config->setServer('smtp.163.com');
    $config->setSsl(false);
    $config->setUsername('huizhang');
    $config->setPassword('*******');
    $config->setMailFrom('xx@163.com');
    $config->setTimeout(10);// Set client connection timeout
    $config->setMaxPackage(1024*1024*5);//Set the size of the package sent：5M

    //Set text or html
    $mimeBean = new Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');

    //Add attachments
    $mimeBean->addAttachment(Attach::create('./test.txt'));

    $mailer = new Mailer($config);
    $mailer->sendTo('xx@qq.com', $mimeBean);
});

```
