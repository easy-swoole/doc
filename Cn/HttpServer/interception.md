---
title: easyswoole请求拦截之中间件实现原理
meta:
  - name: description
    content: easyswoole请求拦截之中间件实现原理
  - name: keywords
    content: easyswoole中间件|easyswoole请求拦截|easyswoole权限验证
---

# 请求拦截

Easyswoole的控制器并没有提供类似中间件的说法，而是提供了控制器中的```onRequest```事件进行验证。
例如，我们需要对```/api/user/*```下的路径进行cookie验证。那么步骤如下
# 定义Base控制器

```php
namespace App\HttpController\Api\User;


use EasySwoole\Http\AbstractInterface\Controller;

abstract class Base extends Controller
{
    protected function onRequest(?string $action): ?bool
    {
        $cookie = $this->request()->getCookieParams('user_cookie');
        //对cookie进行判断，比如在数据库或者是redis缓存中，存在该cookie信息，说明用户登录成功
        $isLogin = true;
        if($isLogin){
            //返回true表示继续往下执行控制器action
            return  true;
        }else{
            //这一步可以给前端响应数据，告知前端未登录
            $this->writeJson(401,null,'请先登录');
            //返回false表示不继续往下执行控制器action
            return  false;
        }
    }
}

```

后续，只要```/api/user/*```下路径的控制器，都继承自Base控制器，都可以实现自动的cookie拦截了

> 行为权限校验也是如此，可以判断某个用户是否对该控制器的action或者请求路径有没有权限