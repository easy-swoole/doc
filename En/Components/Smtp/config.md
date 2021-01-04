---
title: easyswoole Smtp Client
meta:
  - name: description
    content: Easyswoole provides a mail sending component. E-mail is a kind of communication mode that provides information exchange by electronic means. It is the most widely used service on the Internet. Email is essential to almost every web application, whether it's a newsletter or an order confirmation. This component uses the swoole cooperation client to realize the sending of e-mail。
  - name: keywords
    content: easyswoole Smtp Client|swoole smtp|swoole coroutine smtp
---

# MailerConfig
For mail configuration, it is recommended to know what corresponding information is needed to send mail first, so as to facilitate correspondence with the following methods.

## set

Set server address
```php
public function setServer(string $server): void
```

Set server port
```php
public function setPort(int $port): void
```

Set ssl
```php
public function setSsl(bool $ssl): void
```

Set username
```php
public function setUsername(string $username): void
```

Set password
```php
public function setPassword(string $password): void
```

Set mail sender
```php
public function setMailFrom(string $mailFrom): void
```

Set timeout
```php
public function setTimeout(float $timeout): void
```

Set mail size
```php
public function setMaxPackage(int $maxPackage)
```

## get

Get server address
```php
public function getServer(): string
```

Get server port
```php
public function getPort(): int
```

Whether SSL is set
```php
public function isSsl(): bool
```

Get username
```php
public function getUsername(): string
```

Get password
```php
public function getPassword(): string
```

Get mail sender
```php
public function getMailFrom(): string
```

Get timeout
```php
public function getTimeout(): float
```

Get email size
```php
public function getMaxPackage()
```
