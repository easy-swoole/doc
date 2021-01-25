---
title: easyswoole支付宝SDK
meta:
  - name: description
    content: easyswoole支付宝SDK,
  - name: keywords
    content: easyswoole支付宝SDK|swoole支付宝SDK|swoole协程支付宝SDK
---

# 协程支付网关(支付宝支付)

## 组件安装方法和说明
> 注意：请务必检查你的 `php` 环境有没有安装 `php-bcmath` 扩展，没有安装 `php-bcmath` 扩展时安装的 `pay` 组件的版本是 `1.2.17` 之前的版本(不是最新)。想要使用最新稳定版 `pay` 组件的功能，请先安装 `php-bcmath` 扩展，`php` 安装此扩展的方法请自行查询。

```
composer require easyswoole/pay
```

## 支付宝

#### 支付方法

支付宝支付目前支持 7 种支付方法，对应的支付 method 如下：

| method   | 说明        | 参数    | 返回值    |
|:---------|:-----------|:--------|:----------|
| web      | 电脑支付    | Request | Response  |
| wap      | 手机网站支付 | Request | Response  |
| app      | APP 支付    | Request | Response  |
| pos      | 刷卡支付    | Request | Response  |
| scan     | 扫码支付    | Request | Response  |
| transfer | 账户转账    | Request | Response  |
| mini     | 小程序支付  | Request | Response  |
| barCode  | 条码当面支付 | Request | Response  |

注意，`easyswoole/pay` 支付宝支付组件的默认签名为 `RSA2` 公私钥签名，也支持公钥证书的签名方式。放置公私钥证书的时候切记核对。

## 电脑支付

::: tip
统一收单下单并支付页面接口
:::

```php
/**
 * 普通公钥方式生成密钥验签(签名和验签方式)
 */
// 设置支付配置
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
### 配置支付公共请求参数
// (必须)设置 支付宝分配给开发者的应用ID
$aliConfig->setAppId('2017082000295641');
// (必须)设置 请求网关(默认为 沙箱模式)
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::NORMAL);
// 设置 参数格式(默认为 'JSON'，可选参数)，不建议修改
//   $aliConfig->setFormat('JSON');
// 设置 return_url(默认为 null，可选参数)
//   $aliConfig->setReturnUrl(null);
// 设置 请求使用的编码格式，如utf-8、gbk、gb2312等(默认为 'utf-8')
//   $aliConfig->setCharset('utf-8');
// 设置 商户生成签名字符串所使用的签名算法类型，目前支持 RSA2 和 RSA，推荐使用 RSA2(默认为 'RSA2')
$aliConfig->setSignType('RSA2');
// 设置 调用的接口版本(默认为 '1.0')
//   $aliConfig->setApiVersion('1.0');
// 设置 支付宝服务器主动通知商户服务器里指定的页面http/https路径，即支付回调地址(默认为 null，可选参数)
//   $aliConfig->setNotifyUrl(null);
// 设置 应用授权参数(默认为 null，可选参数)，详细请看(https://opendocs.alipay.com/open/common/105193)
//   $aliConfig->setAppAuthToken(null);
// 设置 阿里应用公钥(支持 .pem 结尾的格式，默认为 PKCS1 格式)，用于支付回调时验证签名
$aliConfig->setPublicKey('阿里应用公钥字符串'); // 示例应用公钥字符串
// 设置 阿里应用私钥(支持 .pem 结尾的格式，默认为 PKCS1 格式)，用于生成签名
$aliConfig->setPrivateKey('阿里应用私钥字符串'); // 示例应用私钥


/**
 * 公钥证书方式生成密钥验签(签名和验签方式)
 */
/*
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
### 配置支付公共请求参数
// (必须)设置 支付宝分配给开发者的应用ID
$aliConfig->setAppId('2017082000295641');
// (必须)设置 请求网关(默认为 沙箱模式)
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::NORMAL);
// 设置 参数格式(默认为 'JSON'，可选参数)，不建议修改
//   $aliConfig->setFormat('JSON');
// 设置 return_url(默认为 null，可选参数)
//   $aliConfig->setReturnUrl(null);
// 设置 请求使用的编码格式，如utf-8、gbk、gb2312等(默认为 'utf-8')
//   $aliConfig->setCharset('utf-8');
// 设置 商户生成签名字符串所使用的签名算法类型，目前支持 RSA2 和 RSA，推荐使用 RSA2(默认为 'RSA2')
$aliConfig->setSignType('RSA2');
// 设置 调用的接口版本(默认为 '1.0')
//   $aliConfig->setApiVersion('1.0');
// 设置 支付宝服务器主动通知商户服务器里指定的页面http/https路径，即支付回调地址(默认为 null，可选参数)
//   $aliConfig->setNotifyUrl(null);
// 设置 应用授权参数(默认为 null，可选参数)，详细请看(https://opendocs.alipay.com/open/common/105193)
//   $aliConfig->setAppAuthToken(null);

// (必须)设置 使用公钥证书方式生密钥延签(签名和验签方式)
$aliConfig->setCertMode(true);
// (必须)设置 支付宝公钥文件路径
$aliConfig->setCertPath(__DIR__ . '/cert/alipayCertPublicKey_RSA2.crt'); // 示例支付宝公钥文件路径
// (必须)设置 支付宝根证书文件路径
$aliConfig->setRootCertPath(__DIR__ . '/cert/alipayRootCert.crt'); // 示例支付宝公钥根证书文件路径
// (必须)设置 阿里应用公钥证书文件路径
$aliConfig->setMerchantCertPath(__DIR__ . '/cert/appCertPublicKey_2016091800538780.crt');
// (必须)设置 阿里应用私钥(支持 .pem 结尾的格式，默认为 PKCS1 格式)，用于生成签名
$aliConfig->setPrivateKey('阿里应用私钥字符串');
*/

// 以上【普通公钥方式】 和 【公钥证书方式】 生成密钥验签(签名和验签方式) 这 2 种方式，用户可自行选择一种】


$pay = new \EasySwoole\Pay\Pay();


## (面向对象风格)设置请求参数 biz_content，组件自动帮你组装成对应的格式
$order = new \EasySwoole\Pay\AliPay\RequestBean\Web();
// (必须)设置 商户订单号(商户订单号。64 个字符以内的大小，仅支持字母、数字、下划线。需保证该参数在商户端不重复。)
$order->setOutTradeNo(time() . '123456'); // 示例订单号(仅供参考)
// (必须)设置 订单总金额
$order->setTotalAmount('0.01'); // 示例订单总金额，单位：元(仅供参考)
// (必须)设置 商品标题/交易标题/订单标题/订单关键字等。注意：不可使用特殊字符，如 /，=，& 等。
$order->setSubject('测试'); // 示例商品标题(仅供参考)
// (可选)设置 订单描述，默认为 null
//   $order->setBody(null);
// (可选)设置 在订单中设置支付宝服务器主动通知商户服务器里指定的页面http/https路径，即支付回调地址(默认为 null，可选参数)
//   $order->setNotifyUrl(null); // 等价于在配置中设置 支付回调地址，两者中只要设置一次即可
// (可选)设置 return_url(默认为 null，可选参数)
// $order->setReturnUrl(null); // 等价于在配置中设置 return_url，两者中只要设置一次即可
// 本库只预置了常用的请求参数，没预置的参数请求使用：$order->addProperty('其他字段','其他字段值');
// 支付其他可选参数（详细请看支付宝接口的可选参数，支付宝接口对应地址请看下文）

## (数组风格)设置请求参数 biz_content，组件自动帮你组装成对应的格式
/*
$order = new \EasySwoole\Pay\AliPay\RequestBean\Web([
    'out_trade_no' => time() . '123456', // 示例订单号(仅供参考)
    'total_amount' => '0.01', // 示例订单总金额，单位：元(仅供参考)
    'subject' => '测试', // 示例商品标题(仅供参考)
    '额外的字段键值' => '额外字段值', // 示例支付其他可选参数（详细请看支付宝接口的可选参数，支付宝接口对应地址请看下文）
], true);
*/


// 以上 2 种风格设置请求参数，用户可根据个人需要，选其一即可


// 获取构造请求参数对象
$res = $pay->aliPay($aliConfig)->web($order);
// 将所有请求参数转为数组
var_dump($res->toArray());

// 构造请求表单
$html = $this->buildPayHtml(\EasySwoole\Pay\AliPay\GateWay::NORMAL, $res->toArray());
file_put_contents('test.html', $html); // 该方法的实现请看下文	
```

#### 订单配置参数

**所有订单配置中，对于客观非必选参数，用户可以自行选择是否进行，也可以不进行配置，扩展包已经为您自动处理了，比如，**`product_code`** 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考 [这里](https://opendocs.alipay.com/apis/api_1/alipay.trade.page.pay)，查看「请求参数」一栏。

参数查询：https://opendocs.alipay.com/apis/api_1/alipay.trade.page.pay

生成支付的跳转html示例

```php
function buildPayHtml($endpoint, $payload)
{
    $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$endpoint."' method='POST'>";
    foreach ($payload as $key => $val) {
        $val = str_replace("'", '&apos;', $val);
        $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
    }
    $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
    $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";
    return $sHtml;
}
```



##  手机网站支付接口2.0

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::NORMAL);
$aliConfig->setAppId('2017082000295641');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');

$pay = new \EasySwoole\Pay\Pay();

$order = new \EasySwoole\Pay\AliPay\RequestBean\Wap();
$order->setSubject('测试');
$order->setOutTradeNo(time().'123456');
$order->setTotalAmount('0.01');

$res = $pay->aliPay($aliConfig)->wap($order);
var_dump($res->toArray());

$html = buildPayHtml(\EasySwoole\Pay\AliPay\GateWay::NORMAL,$res->toArray());
file_put_contents('test.html',$html);
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如，`product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/203/107090/)，查看「请求参数」一栏。

参数查询：https://docs.open.alipay.com/api_1/alipay.trade.wap.pay



## APP支付接口2.0

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');

$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\App();
$order->setSubject('测试');
$order->setOutTradeNo(time().'123456');
$order->setTotalAmount('0.01');
$aliPay = $pay->aliPay($aliConfig);

var_dump($aliPay->app($order)->toArray());
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如， `product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/204/105465/)，查看「请求参数」一栏。

参数查询：https://docs.open.alipay.com/api_1/alipay.trade.app.pay



## 刷卡支付

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\Pos();
$order->setSubject('测试');
$order->setTotalAmount('0.01');
$order->setOutTradeNo(time());
$order->setAuthCode('289756915257123456');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->pos($order)->toArray();
var_dump($data);
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如，`product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_1/alipay.trade.pay)，查看「请求参数」一栏。

参数查询：https://docs.open.alipay.com/api_1/alipay.trade.page.pay



## 扫码支付

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');

$pay = new \EasySwoole\Pay\Pay();

$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\Scan();
$order->setSubject('测试');
$order->setTotalAmount('0.01');
$order->setOutTradeNo(time());

$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->scan($order)->toArray();
$response = $aliPay->preQuest($data);
var_dump($response);
// qr_code 当前预下单请求生成的二维码码串，可以用二维码生成工具根据该码串值生成对应的二维码	 https://qr.alipay.com/bavh4wjlxf12tper3a
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如，`product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_1/alipay.trade.precreate)，查看「请求参数」一栏。

参考参数：https://docs.open.alipay.com/api_1/alipay.trade.precreate



## 单笔转账到支付宝账户接口

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');

$pay = new \EasySwoole\Pay\Pay();

$order = new \EasySwoole\Pay\AliPay\RequestBean\Transfer();
$order->setSubject('测试');
$order->setAmount('0.01');
/*
    收款方账户类型。可取值：
    1、ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。
    2、ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
*/
$order->setPayeeType('ALIPAY_LOGONID');
$order->setPayeeAccount('hcihsn8174@sandbox.com');

$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->transfer($order)->toArray();
$aliPay->preQuest($data);
var_dump($data);
```

> 本接口用的是老版本的 https://docs.open.alipay.com/309/alipay.fund.trans.toaccount.transfer

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如，`product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer)，查看「请求参数」一栏。

参数查询：https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer

## 小程序支付

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');

$pay = new \EasySwoole\Pay\Pay();

$order = new \EasySwoole\Pay\AliPay\RequestBean\MiniProgram();
$order->setSubject('测试');
$order->setOutTradeNo(time().'123456');
$order->setTotalAmount('0.01');
$order->setBuyerId('hcihsn8174@sandbox.com');

$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->miniProgram($order)->toArray();
var_dump($data);
```

#### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，比如，`product_code` 等参数。**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_1/alipay.trade.create/)，查看「请求参数」一栏。

小程序支付接入文档：<https://docs.alipay.com/mini/introduce/pay>。

参数查询：

## 订单查询

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\OrderFind();
$order->setOutTradeNo(time().'123456');
$aliPay = $pay->aliPay($aliConfig);

var_dump($aliPay->orderFind($order)->toArray());
```

官方参数查询：https://docs.open.alipay.com/api_1/alipay.trade.fastpay.refund.query

## 退款查询

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\RefundFind();
$order->setOutTradeNo('20150320010101001');
$order->setOutRequestNo(time().'2014112611001004680073956707');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->refundFind($order)->toArray();
var_dump($aliPay->preQuest($data));
```

官方参数查询：https://docs.open.alipay.com/api_1/alipay.trade.refund



## 查询转账订单接口

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\TransferFind();
$order->setOutBizNo('3142321423432');
// 二选一
//	$order->setOrderId('20160627110070001502260006780837');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->transferFind($order)->toArray();
var_dump($aliPay->preQuest($data));
```

官方参数查询：https://docs.open.alipay.com/api_28/alipay.fund.trans.order.query



## 交易撤销接口

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\Cancel();
$order->setOutTradeNo('20150320010101001');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->cancel($order)->toArray();
var_dump($aliPay->preQuest($data));
```

官方参数查询：https://docs.open.alipay.com/api_1/alipay.trade.cancel

## 交易关闭接口

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\Close();
$order->setOutTradeNo(time().'123456');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->close($order)->toArray();
var_dump($aliPay->preQuest($data));
```

官方参数查询：https://docs.open.alipay.com/api_1/alipay.trade.close

## 查询对账单下载地址

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();
$order = new \EasySwoole\Pay\AliPay\RequestBean\Download();
$order->setBillType('trade');
$order->setBillDate('2016-04-05');
$aliPay = $pay->aliPay($aliConfig);
$data = $aliPay->download($order)->toArray();
var_dump($aliPay->preQuest($data));
```

官方参数查询：https://docs.open.alipay.com/api_15/alipay.data.dataservice.bill.downloadurl.query



## 验证服务器数据

```php
$aliConfig = new \EasySwoole\Pay\AliPay\Config();
$aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::SANDBOX);
$aliConfig->setAppId('2016091800538339');
$aliConfig->setPublicKey('阿里公钥');
$aliConfig->setPrivateKey('阿里私钥');
$pay = new \EasySwoole\Pay\Pay();

$param = [];//伪代码,post数据
unset($param['sign_type']);//需要忽略sign_type组装
$order = new \EasySwoole\Pay\AliPay\RequestBean\NotifyRequest($param,true);
$aliPay = $pay->aliPay($aliConfig);
$result = $aliPay->verify($order);
var_dump($result);
```



## 服务器确认收到异步通知字符串获取

```php
\EasySwoole\Pay\AliPay::success();//成功响应
\EasySwoole\Pay\AliPay::fail();//失败响应
```








