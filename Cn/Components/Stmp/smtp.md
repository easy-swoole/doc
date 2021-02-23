---
title: easyswoole Smtp客户端
meta:
  - name: description
    content: Easyswoole提供了一个发送邮件组件，电子邮件是—种用电子手段提供信息交换的通信方式，是互联网应用最广的服务。电子邮件几乎是每个web应用程序不可或缺的，无论是时事通讯还是订单确认。本组件采用swoole协程客户端实现了电子邮件的发送。
  - name: keywords
    content: easyswoole Smtp客户端|swoole smtp|swoole协程smtp
---

# Smtp

电子邮件是—种用电子手段提供信息交换的通信方式，是互联网应用最广的服务。电子邮件几乎是每个web应用程序不可或缺的，无论是时事通讯还是订单确认。本库采用swoole协程客户端实现了电子邮件的发送。

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.2.6
- easyswoole/spl: ^1.1
- easyswoole/utility: ^1.0

## 安装方法

> composer require easyswoole/smtp

## 仓库地址

[easyswoole/smtp](https://github.com/easy-swoole/smtp)

## 基本使用

### 邮件配置

#### set

设置服务器地址
```php
public function setServer(string $server): void
```

设置服务器端口
```php
public function setPort(int $port): void
```

设置ssl
```php
public function setSsl(bool $ssl): void
```

设置用户名
```php
public function setUsername(string $username): void
```

设置密码
```php
public function setPassword(string $password): void
```

设置邮件发送方
```php
public function setMailFrom(string $mailFrom): void
```

设置超时时间
```php
public function setTimeout(float $timeout): void
```

设置邮件大小
```php
public function setMaxPackage(int $maxPackage)
```

#### get

获取服务地址
```php
public function getServer(): string
```

获取服务端口
```php
public function getPort(): int
```

是否设置了ssl
```php
public function isSsl(): bool
```

获取用户名
```php
public function getUsername(): string
```

获取密码
```php
public function getPassword(): string
```

获取邮件发送方
```php
public function getMailFrom(): string
```

获取超时时间
```php
public function getTimeout(): float
```

获取邮件大小
```php
public function getMaxPackage()
```

### 内容配置

#### set

设置协议版本
```php
public function setMimeVersion($mimeVersion): void
```

设置contentType
```php
public function setContentType($contentType): void
```

设置字符
```php
public function setCharset($charset): void
```

设置编码
```php
public function setContentTransferEncoding($contentTransferEncoding): void
````

设置主题
```php
public function setSubject($subject): void
```

设置邮件内容
```php
public function setBody($body): void
````

添加附件
```php
public function addAttachment($attachment)
```

#### get

获取协议版本
```php
public function getMimeVersion()
```

获取contenttype
```php
public function getContentType()
```

获取字符
```php
public function getCharset()
```

获取编码
```php
public function getContentTransferEncoding()
```

获取主题
```php
public function getSubject()
```

获取邮件内容
```php
public function getBody()
```

获取附件
```php
public function getAttachments()
```

### 使用示例
```php
use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Attach;
// 必须用go
go(function (){
    $config = new MailerConfig();
    $config->setServer('smtp.163.com');
    $config->setSsl(false);
    $config->setUsername('huizhang');
    $config->setPassword('*******');
    $config->setMailFrom('xx@163.com');
    $config->setTimeout(10);//设置客户端连接超时时间
    $config->setMaxPackage(1024*1024*5);//设置包发送的大小：5M

    //设置文本或者html格式
    $mimeBean = new Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');

    //添加附件
    $mimeBean->addAttachment(Attach::create('./test.txt'));

    $mailer = new Mailer($config);
    $mailer->sendTo('xx@qq.com', $mimeBean);
});

```

## 进阶使用

邮件内容支持文本和html两种类型

### 文本

#### 示例
```php
$mimeBean = new \EasySwoole\Smtp\Message\Text();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```

#### 效果
![](/Images/Passage/Smtp/text.png) 

### Html
```php
$mimeBean = new \EasySwoole\Smtp\Message\Html();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```

#### 效果
![](/Images/Passage/Smtp/html.png) 

### 附件
```php
$mimeBean = new \EasySwoole\Smtp\Message\Text();
//$mimeBean = new \EasySwoole\Smtp\Message\Html();

...

// 创建附件
$createAttachment = Attach::create('./test.txt');

// 添加附件
$mimeBean->addAttachment($createAttachment);

...
```
