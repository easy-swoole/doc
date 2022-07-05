---
title: easyswoole注解控制器 - 熔断注解
meta:
- name: description
  content: easyswoole注解控制器 - 熔断注解
- name: keywords
  content:  easyswoole注解控制器 - 熔断注解
---

# CircuitBreaker

熔断注解可为单个方法设定超时时间，以及超时之后的处理方法，例如：

```php
<?php

namespace App\HttpController;

use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Index extends AnnotationController
{
    /**
     * 设定了超时时间和超时之后的方法,还传输了一个自定义的超时时间用于测试
     * @CircuitBreaker(timeout=1.5,failAction="circuitBreakerFail")
     * @Param(name="timeout",required="123123",between={1,5})
     */
    public function circuitBreaker($timeout)
    {
        \co::sleep($timeout);
        $this->writeJson(200, null, 'success call');
    }

    public function circuitBreakerFail()
    {
        $this->writeJson(200, null, 'this is fail call');
    }
}
```

- 访问 `http://ip:port/circuitBreaker?timeout=1.4，例如：http://192.168.217.129:9501/circuitBreaker?timeout=1.4` 即可调用成功，不触发熔断，结果如下：
```json
{
    "code": 200,
    "result": null,
    "msg": "success call"
}
```

- 访问 `http://ip:port/circuitBreaker?timeout=1.6，例如：http://192.168.217.129:9501/circuitBreaker?timeout=1.6` 即可触发熔断，调用结果如下：
```json
{
    "code": 200,
    "result": null,
    "msg": "this is fail call"
}
```