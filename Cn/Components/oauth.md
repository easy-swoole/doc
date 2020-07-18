---
title: easyswoole组件库-oauth(第三方登录)
meta:
  - name: description
    content: easyswoole组件库-oauth(第三方登录)
  - name: keywords
    content: easyswoole组件库-oauth(第三方登录)
---

# Oauth

`OAuth`在"客户端"与"服务提供商"之间，设置了一个授权层`（authorization layer）`。"客户端"不能直接登录"服务提供商"，只能登录授权层，以此将用户与客户端区分开来。"客户端"登录授权层所用的令牌`（token）`，与用户的密码不同。用户可以在登录的时候，指定授权层令牌的权限范围和有效期。

:::tip
需要用户自行补充[oauth](https://baike.baidu.com/item/OAuth2.0/6788617?fr=aladdin)的认证流程，方便自己更加快速的接入。
:::

## 安装

*请在`EasySwoole`根目录下执行以下命令*
> composer require easyswoole/o-auth

## 详情

根据`Oauth`协议，分别有如下调用方法。

- `getAuthUrl()` 获取授权地址
- `getAccessToken($storeState = null, $state = null, $code = null)` 获取AccessToken（只返回access_token）
- `getAccessTokenResult()` 执行`getAccessToken`方法后，此方法获取原结果
- `getUserInfo(string $accessToken)` 获取用户信息
- `validateAccessToken(string $accessToken)` 验证token是否有效
- `refreshToken(string $refreshToken = null)` 刷新token 返回`bool`
- `getRefreshTokenResult()` 执行`refreshToken`方法后，此方法获取原结果


## 示例代码

### 微信

```php
class WeiXin extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\WeiXin\Config();
        $config->setAppId('appid');
        $config->setState('easyswoole');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\WeiXin\OAuth($config);
        $url = $oauth->getAuthUrl();

        return $this->response()->redirect($url);
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\WeiXin\Config();
        $config->setAppId('appid');
        $config->setSecret('secret');
        $config->setOpenIdMode(\EasySwoole\OAuth\WeiXin\Config::OPEN_ID); // 可设置UNION_ID 默认为OPEN_ID

        $oauth = new \EasySwoole\OAuth\WeiXin\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['code']);
        $refreshToken = $oauth->getAccessTokenResult()['refresh_token'];

        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;


        if (!$oauth->refreshToken($refreshToken)) echo 'access_token 续期失败！' . PHP_EOL;

    }
}
```

### QQ

```php
class QQ extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\QQ\Config();
        $config->setAppId('appid');
        $config->setState('easyswoole');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\QQ\OAuth($config);
        $url = $oauth->getAuthUrl();

        return $this->response()->redirect($url);
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\QQ\Config();
        $config->setAppId('appid');
        $config->setAppKey('appkey');
        $config->setRedirectUri('redirect_uri');
        $config->setOpenIdMode(\EasySwoole\OAuth\QQ\Config::OPEN_ID); // 可设置UNION_ID 默认为OPEN_ID

        $oauth = new \EasySwoole\OAuth\QQ\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['code']);
        $refreshToken = $oauth->getAccessTokenResult()['refresh_token'];

        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;


        if (!$oauth->refreshToken($refreshToken)) echo 'access_token 续期失败！' . PHP_EOL;

    }
}
```

### 微博

```php
class Weibo extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\Weibo\Config();
        $config->setClientId('clientid');
        $config->setState('easyswoole');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\Weibo\OAuth($config);
        $url = $oauth->getAuthUrl();

        return $this->response()->redirect($url);
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\Weibo\Config();
        $config->setClientId('clientid');
        $config->setClientSecret('secret');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\Weibo\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['code']);

        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;
    }
}
```

### 支付宝

```php
class AliPay extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\AliPay\Config();
        $config->setState('easyswoole');
        $config->setAppId('appid');
        $config->setRedirectUri('redirect_uri');

        // 进行测试开发的时候 把OAuth的源码文件里面的 API_DOMAIN 和 AUTH_DOMAIN 进行修改
        $oauth = new \EasySwoole\OAuth\AliPay\OAuth($config);
        $url = $oauth->getAuthUrl();
        return $this->response()->redirect($url);
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\AliPay\Config();
        $config->setAppId('appid');
//        $config->setAppPrivateKey('私钥');
        $config->setAppPrivateKeyFile('私钥文件'); // 私钥文件(非远程) 此方法与上个方法二选一

        $oauth = new \EasySwoole\OAuth\AliPay\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['auth_code']);
        $refreshToken = $oauth->getAccessTokenResult()['alipay_system_oauth_token_response']['refresh_token'];

        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;
        var_dump($oauth->getAccessTokenResult());

        if (!$oauth->refreshToken($refreshToken)) echo 'access_token 续期失败！' . PHP_EOL;
        var_dump($oauth->getRefreshTokenResult());
    }
}
```

### Github

```php
class Github extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\Github\Config();
        $config->setClientId('clientid');
        $config->setRedirectUri('redirect_uri');
        $config->setState('easyswoole');
        $oauth = new \EasySwoole\OAuth\Github\OAuth($config);
        $this->response()->redirect($oauth->getAuthUrl());
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();
        $config = new \EasySwoole\OAuth\Github\Config();
        $config->setClientId('clientid');
        $config->setClientSecret('secret');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\Github\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['code']);
        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;
    }
}
```

### Gitee

```php
class Gitee extends \EasySwoole\Http\AbstractInterface\Controller
{
    public function index()
    {
        $config = new \EasySwoole\OAuth\Gitee\Config();
        $config->setState('easyswoole');
        $config->setClientId('clientid');
        $config->setRedirectUri('redirect_uri');
        $oauth = new \EasySwoole\OAuth\Gitee\OAuth($config);
        $this->response()->redirect($oauth->getAuthUrl());
    }

    public function callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\Gitee\Config();
        $config->setClientId('client_id');
        $config->setClientSecret('secret');
        $config->setRedirectUri('redirect_uri');

        $oauth = new \EasySwoole\OAuth\Gitee\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['code']);
        $userInfo = $oauth->getUserInfo($accessToken);
        var_dump($userInfo);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;
        var_dump($oauth->getAccessTokenResult());
    }
}
```
