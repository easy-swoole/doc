---
title: easyswoole 服务熔断-熔断注解
meta:
  - name: description
    content: easyswoole 服务熔断-熔断注解
  - name: keywords
    content: swoole分布式框架|easyswoole分布式|swoole微服务
---

# CircuitBreaker

熔断注解可为单个方法设定超时时间,以及超时之后的处理方法,例如:
  
```php
    /**
     * 设定了超时时间和超时之后的方法,还传输了一个自定义的超时时间用于测试
     * @CircuitBreaker(timeout="1.5",failAction="circuitBreakerFail")
     * @Param(name="timeout",required="",between={1,5})
     */
    public function circuitBreaker($timeout)
    {
        \co::sleep($timeout);
        $this->writeJson(200,null,'success call');
    }

    public function circuitBreakerFail()
    {
        $this->writeJson(200,null,'this is fail call');
    }
```