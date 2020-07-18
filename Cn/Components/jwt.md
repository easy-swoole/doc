---
title: easyswoole jwt
meta:
  - name: description
    content: 基于EasySwoole组件实现的json web token
  - name: keywords
    content: easyswoole jwt|swoole jwt
---

# JWT

JSON Web Token（JWT）是目前最流行的跨域身份验证解决方案。
随着技术的发展，分布式web应用的普及，通过session管理用户登录状态成本越来越高，因此慢慢发展成为token的方式做登录身份校验，然后通过token去取redis中的缓存的用户信息，随着之后jwt的出现，校验方式更加简单便捷化，无需通过redis缓存，而是直接根据token取出保存的用户信息，以及对token可用性校验，单点登录更为简单。

![](/Images/Passage/Jwt/framework.png)

::: warning 
[建议阅读一下](https://baijiahao.baidu.com/s?id=1608021814182894637&wfr=spider&for=pc)
:::

## 组件要求

- php: >=7.1.0
- ext-openssl: >=1.0.0
- easyswoole/spl: ^1.2
- easyswoole/utility: ^1.1

## 安装方法


> composer require easyswoole/jwt 

## 仓库地址

[easyswoole/jwt](https://github.com/easy-swoole/jwt)

## 核心类库方法

### 编码相关

#### 设置加密方式，默认HMACSHA256
```php
    function algMethod(string $method):Jwt
```

#### 设置秘钥，默认Easyswoole
```php
    function setSecretKey(string $key):Jwt
```

#### 初始化一个没有附带信息的token的JwtObject
```php
    public function publish():JwtObject
```

#### 设置加密方式, 默认HMACSHA256
```php
    public function setAlg($alg): self
```

#### 设置用户
```php
    public function setAud($aud): self
```

#### 设置过期时间
```php
    public function setExp($exp): self
```

#### 设置发布时间
```php
    public function setIat($iat): self
```

#### 设置发行人
```php
    public function setIss(string $iss): self
```

#### 设置jwt-id，用于标识该jwt
```php
    public function setJti($jti): self
```

#### 在此之前不可用
```php
    public function setNbf($nbf): self
```

#### 设置主题
```php
    public function setSub($sub): self
```

#### 设置其他数据
```php
    public function setData($data): self
```

#### 获取token
```php
    function __toString()
```

### 解码相关

#### 解码
```php
    public function decode(?string $raw):?JwtObject
```

#### 获取解码状态, 1:通过, -1:无效, -2:token过期
```php
    public function getStatus(): int
```
#### 获取加密方式
```php
    public function getAlg()
```

#### 获取用户
```php
    public function getAud()
```

#### 获取过期时间
```php
    public function getExp()
```

#### 获取发布时间
```php
    public function getIat()
```

#### 获取发行人
```php
    public function getIss(): string
```

#### 获取jwt-id
```php
    public function getJti()
```

#### 获取生效时间
```php
    public function setNbf($nbf): void
```

#### 获取主题
```php
    public function getSub()
```

#### 获取自定义数据
```php
    public function getData()
```

#### 获取签名
```php
    public function getSignature()
```

#### 通过key获取相关数据
```php
    final public function getProperty($name)
```

## 基本使用

### 生成token

```php
use EasySwoole\Jwt\Jwt;

$jwtObject = Jwt::getInstance()
    ->setSecretKey('easyswoole') // 秘钥
    ->publish();

$jwtObject->setAlg('HMACSHA256'); // 加密方式
$jwtObject->setAud('user'); // 用户
$jwtObject->setExp(time()+3600); // 过期时间
$jwtObject->setIat(time()); // 发布时间
$jwtObject->setIss('easyswoole'); // 发行人
$jwtObject->setJti(md5(time())); // jwt id 用于标识该jwt
$jwtObject->setNbf(time()+60*5); // 在此之前不可用
$jwtObject->setSub('主题'); // 主题

// 自定义数据
$jwtObject->setData([
    'other_info'
]);

// 最终生成的token
$token = $jwtObject->__toString();
```

### 解析token

```php
use EasySwoole\Jwt\Jwt;

$token = "eyJhbGciOiJITUFDU0hBMjU2IiwiaXNzIjoiZWFzeXN3b29sZSIsImV4cCI6MTU3MzgzNTIxMSwic3ViIjoi5Li76aKYIiwibmJmIjoxNTczODMxOTExLCJhdWQiOiJ1c2VyIiwiaWF0IjoxNTczODMxNjExLCJqdGkiOiJjYWJhZmNiMWIxZTkxNTU3YzIxMDUxYTZiYTQ0MTliMiIsInNpZ25hdHVyZSI6IjZlNTI1ZjJkOTFjZGYzMjBmODE1NmEwMzE1MDhiNmU0ZDQ0YzhkNGFhYzZjNmU1YzMzMTNjMDIyMGJjYjJhZjQiLCJzdGF0dXMiOjEsImRhdGEiOlsib3RoZXJfaW5mbyJdfQ%3D%3D";

try {
    $jwtObject = Jwt::getInstance()->decode($token);

    $status = $jwtObject->getStatus();
    
    // 如果encode设置了秘钥,decode 的时候要指定
    // $status = $jwt->setSecretKey('easyswoole')->decode($token)

    switch ($status)
    {
        case  1:
            echo '验证通过';
            $jwtObject->getAlg();
            $jwtObject->getAud();
            $jwtObject->getData();
            $jwtObject->getExp();
            $jwtObject->getIat();
            $jwtObject->getIss();
            $jwtObject->getNbf();
            $jwtObject->getJti();
            $jwtObject->getSub();
            $jwtObject->getSignature();
            $jwtObject->getProperty('alg');
            break;
        case  -1:
            echo '无效';
            break;
        case  -2:
            echo 'token过期';
        break;
    }
} catch (\EasySwoole\Jwt\Exception $e) {

}
```