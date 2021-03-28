---
title: easyswoole wechat
meta:
  - name: description
    content: 基于EasySwoole实现的微信公众号组件
  - name: keywords
    content: easyswoole wechat 微信SDK 微信公众号组件
---

# 网页授权

## 关于 OAuth 2.0

`OAuth` 是一个关于授权（`authorization`）的开放网络标准，在全世界得到广泛应用，目前的版本是 `2.0` 版。

`OAuth` 授权流程大致如下：

![](/Images/OAuth2.png)

> 摘自：[RFC 6749](https://datatracker.ietf.org/doc/rfc6749/?include_text=1)

步骤解释：

::: tip
 （A）用户打开客户端以后，客户端要求用户给予授权。

 （B）用户同意给予客户端授权。
 
 （C）客户端使用上一步获得的授权，向认证服务器申请令牌。
 
 （D）认证服务器对客户端进行认证以后，确认无误，同意发放令牌。
 
 （E）客户端使用令牌，向资源服务器申请获取资源。
 
 （F）资源服务器确认令牌无误，同意向客户端开放资源。
:::

关于 `OAuth` 协议我们就简单了解到这里，如果还有不熟悉的同学，请 `Google` 相关资料

## 微信 OAuth

在微信里的 `OAuth` 其实有两种：**公众平台网页授权获取用户信息**、**开放平台网页登录**。

它们的区别有两处，**授权地址** 不同，**scope** 不同。

::: warning
  - 公众平台网页授权获取用户信息 
    - **授权 URL**：https://open.weixin.qq.com/connect/oauth2/authorize
    - **Scopes**：snsapi_base 与 snsapi_userinfo

  - 开放平台网页登录 
    - **授权 URL**：https://open.weixin.qq.com/connect/qrconnect 
    - **Scopes**：snsapi_login
:::

他们的逻辑都一样：

- 用户尝试访问一个我们的业务页面，例如: `/user/profile`
- 如果用户已经登录，则正常显示该页面
- 系统检查当前访问的用户并未登录（从 `session` 或者其它方式检查），则跳转到 **微信授权服务器（上面的两种中一种 **授权 `URL`** ）**，并告知微信授权服务器我的 **回调 URL（`redirect_uri=callback.php`)**，此时用户看到蓝色的授权确认页面（`scope` 为 `snsapi_base` 时不显示）
- 用户点击确定完成授权，浏览器跳转到 **回调URL**：`callback.php` 并带上 `code`： `?code=CODE&state=STATE`。
- 在 `callback.php` 中得到 `code` 后，通过 `code` 再次向微信服务器请求得到 **网页授权 access_token** 与 `openid`
- 你可以选择拿 `openid` 去请求 `API` 得到用户信息（可选）
- 将用户信息写入 `SESSION`。
- 跳转到第 `3` 步写入的 `target_url` 页面（`/user/profile`）。

看懵了？没事，使用 `SDK`，你不用管这么多。

注意，上面的第 `3` 步：`redirect_uri=callback.php` 实际上我们在 `Swoole` 中用 `redirect_uri=callback` 回调地址，后面还会带上授权目标页面 `user/profile`，所以完整的 `redirect_uri` 应该是下面的这样的 `PHP` 去拼出来：`'redirect_uri=' . urlencode('callback?target=user/profile')`，拼接结果为：`redirect_uri=callback%3Ftarget%3Duser%2Fprofile`

## 逻辑组成

从上面我们所描述的授权流程来看，我们至少有 `3` 个页面：

- **业务页面**，也就是需要授权才能访问的页面。
- **发起授权页**，此页面其实可以省略，可以做成一个中间件，全局检查未登录就发起授权。
- **授权回调页**，接收用户授权后的状态，并获取用户信息，写入用户会话状态（`SESSION`）。

## 开始之前

在开始之前请一定要记住，先登录公众号后台，找到 **边栏 “开发”** 模块下的 **“接口权限”**，点击 **“网页授权获取用户基本信息”** 后面的修改，添加你的网页授权域名。

> 如果你的授权地址为：`http://www.abc.com/xxxxx`，那么请填写 `www.abc.com`，也就是说请填写与网址匹配的域名，前者如果填写 `abc.com` 是通过不了的。

## SDK 中 OAuth 模块的 API

在 `SDK` 中，我们使用名称为 `oauth` 的模块来完成授权服务，我们主要用到以下两个 `API`：

### 发起授权

```php
// $redirectUrl 为跳转目标，请自行 `302` 跳转到目标地址
$redirectUrl = $officialAccount->oauth->scopes(['snsapi_userinfo'])
    ->redirect();
```
              
当然你也可以在发起授权的时候指定回调 `URL`，比如设置回调 `URL` 为当前页面：

```php
<?php

// 在 EasySwoole 中，$this->request() 为 EasySwoole 的请求对象
$redirectUrl = $officialAccount->oauth->scopes(['snsapi_userinfo'])
    ->redirect($this->request()->getUri());

// 在原生 Swoole 中，$request 为 \Swoole\Http\Request 的实例对象
$redirectUrl = $officialAccount->oauth->scopes(['snsapi_userinfo'])
    ->redirect($request->server['request_uri']);
```

它的返回值 `$redirectUrl` 是一个字符串跳转地址，请自行使用框架的跳转方法实现跳转，在 `EasySwoole` 中写法为：

```php
$this->response()->redirect($redirectUrl);
```

在原生 `Swoole` 中可以这样写：

```php
// $response 为 \Swoole\Http\Response 的实例对象
$response->redirect($redirectUrl);
```

### 获取已授权用户

```php
<?php

$code = "微信回调URL携带的 code";
        
$user = $officialAccount->oauth->userFromCode($code);
```

返回的 `$user` 是 `EasySwoole\WeChat\OfficialAccount\OAuth\User` 对象，你可以从该对象拿到更多的信息。

`$user` 可以用的方法:

- `$user->getId();` 对应微信的 `openid`
- `$user->getNickname();` 对应微信的 `nickname`
- `$user->getName();` 对应微信的 `nickname`
- `$user->getAvatar();` 头像地址
- `$user->getRaw();` 原始 `API` 返回的结果
- `$user->getAccessToken();` `access_token`
- `$user->getRefreshToken();` `refresh_token`
- `$user->getExpiresIn();` `expires_in`，`access_token` 的过期时间
- `$user->getTokenResponse();` 返回 `access_token` 时的响应值

> 注意：`$user` 里没有 `openid`，`$user->id` 便是 `openid`。如果你想拿微信返回给你的原样的全部信息，请使用：`$user->getRaw()`;

当 `scope` 为 `snsapi_base` 时 `$officialAccount->oauth->user();` 对象里只有 `id`，没有其它信息。

## 网页授权实例

我们这里来用 `PHP` 原生 `Swoole` 写法举个例子，`oauth_callback` 是我们的授权回调 `URL` (未 `urlencode` 编码的 `URL`)，`user/profile` 是我们需要授权才能访问的页面，它的 `PHP` 代码如下：

```php

// http://easyswoolewechat.com/user/profile

<?php

// ... 这里省略

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

    $config = [
        // ...
        'oauth' => [
            'scopes' => ['snsapi_userinfo'],
            'callback' => '/oauth_callback',
        ],
        // ..
    ];

    $officialAccount = \EasySwoole\WeChat\Factory::officialAccount($config);

    $oauth = $officialAccount->oauth;

    // 未登录
    if (empty($_SESSION['wechat_user'])) {

        $_SESSION['target_url'] = 'user/profile';
        $redirectUrl = $oauth->redirect();
        $request->redirct($redirectUrl);
        exit;
    }

    // 已经登录过
    $user = $_SESSION['wechat_user'];
});
 
// ... 这里省略
```

授权回调页：

```php
// http://easyswoolewechat.com/oauth_callback

<?php

// ... 这里省略

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {


    $config = [
        // ...
    ];

    $officialAccount = \EasySwoole\WeChat\Factory::officialAccount($config);

    $oauth = $officialAccount->oauth;

    // 获取 OAuth 授权结果用户信息
    $code = "微信回调URL携带的 code";

    $user = $oauth->userFromCode($code);

    $_SESSION['wechat_user'] = $user->toArray();

    $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];

    // 跳转到 user/profile
    $response->redirect($targetUrl);
});

// ... 这里省略
```

上面的例子呢都是基于 `$_SESSION` 来保持会话的，在微信客户端中，你可以结合 `Cookies` 来存储，但是有效期平台不一样时间也不一样，好像 `Android` 的失效会快一些，不过基本也够用了。