---
title: easyswoole图片验证码
meta:
  - name: description
    content: easyswoole图片验证码
  - name: keywords
    content: easyswoole图片验证码|swoole图片验证码
---
# EasySwoole 验证码组件  

EasySwoole提供了独立的 `验证码组件` ,几行代码即可实现输出一个验证码

## 组件要求

- php: >=7.1
- ext-gd: *
- easyswoole/spl: ^1.0

## 安装方法

> composer require easyswoole/verifycode=3.x

## 仓库地址
[easyswoole/verifycode=3.x](https://github.com/easy-swoole/verifyCode)


## 基本使用  


### 配置

生成验证码前需要传入Config的对象实例  
Config类实例化后会有默认配置,无需配置也可生成验证码图片

```php
<?php
// +----------------------------------------------------------------------
// | easySwoole [ use swoole easily just like echo "hello world" ]
// +----------------------------------------------------------------------
// | WebSite: https://www.easyswoole.com
// +----------------------------------------------------------------------
// | Welcome Join QQGroup 633921431
// +----------------------------------------------------------------------

namespace EasySwoole\VerifyCode;

use EasySwoole\Spl\SplBean;

/**
 * 验证码配置文件
 * Class VerifyCodeConf
 * @author  : evalor <master@evalor.cn>
 * @package Vendor\VerifyCode
 */
class Conf extends SplBean
{

    public $charset   = '1234567890AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz'; // 字母表
    public $useCurve  = false;         // 混淆曲线
    public $useNoise  = false;         // 随机噪点
    public $useFont   = null;          // 指定字体
    public $fontColor = null;          // 字体颜色
    public $backColor = null;          // 背景颜色
    public $imageL    = null;          // 图片宽度
    public $imageH    = null;          // 图片高度
    public $fonts     = [];            // 额外字体
    public $fontSize  = 25;            // 字体大小
    public $length    = 4;             // 生成位数
    public $mime      = MIME::PNG;     // 设置类型
    public $temp      = '/tmp';  // 设置缓存目录

    public function setTemp($temp){
        if (!is_dir($temp)) mkdir($temp,0755) && chmod($temp,0755);
        $this->temp = $temp;
    }

    /**
     * 设置图片格式
     * @param $MimeType
     * @author : evalor <master@evalor.cn>
     * @return Conf
     */
    public function setMimeType($MimeType)
    {
        $allowMime = [ MIME::PNG, MIME::GIF, MIME::JPG ];
        if (in_array($MimeType, $allowMime)) $this->mime = $MimeType;
        return $this;
    }

    /**
     * 设置字符集
     * @param string $charset
     * @return Conf
     */
    public function setCharset($charset)
    {
        is_string($charset) && $this->charset = $charset;
        return $this;
    }

    /**
     * 开启混淆曲线
     * @param bool $useCurve
     * @return Conf
     */
    public function setUseCurve($useCurve = true)
    {
        is_bool($useCurve) && $this->useCurve = $useCurve;
        return $this;
    }

    /**
     * 开启噪点生成
     * @param bool $useNoise
     * @return Conf
     */
    public function setUseNoise($useNoise = true)
    {
        is_bool($useNoise) && $this->useNoise = $useNoise;
        return $this;
    }

    /**
     * 使用自定义字体
     * @param string $useFont
     * @return Conf
     */
    public function setUseFont($useFont)
    {
        is_string($useFont) && $this->useFont = $useFont;
        return $this;
    }

    /**
     * 设置文字颜色
     * @param array|string $fontColor
     * @return Conf
     */
    public function setFontColor($fontColor)
    {
        if (is_string($fontColor)) $this->fontColor = $this->HEXToRGB($fontColor);
        if (is_array($fontColor)) $this->fontColor = $fontColor;
        return $this;
    }

    /**
     * 设置背景颜色
     * @param null $backColor
     * @return Conf
     */
    public function setBackColor($backColor)
    {
        if (is_string($backColor)) $this->backColor = $this->HEXToRGB($backColor);
        if (is_array($backColor)) $this->backColor = $backColor;
        return $this;
    }

    /**
     * 设置图片宽度
     * @param int|string $imageL
     * @return Conf
     */
    public function setImageWidth($imageL)
    {
        $this->imageL = intval($imageL);
        return $this;
    }

    /**
     * 设置图片高度
     * @param null $imageH
     * @return Conf
     */
    public function setImageHeight($imageH)
    {
        $this->imageH = intval($imageH);
        return $this;
    }

    /**
     * 设置字体集
     * @param array|string $fonts
     * @return Conf
     */
    public function setFonts($fonts)
    {
        if (is_string($fonts)) array_push($this->fonts, $fonts);
        if (is_array($fonts) && !empty($fonts)) {
            if (empty($this->fonts)) {
                $this->fonts = $fonts;
            } else {
                array_merge($this->fonts, $fonts);
            }
        }
        return $this;
    }

    /**
     * 设置字体尺寸
     * @param int $fontSize
     * @return Conf
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = intval($fontSize);
        return $this;
    }

    /**
     * 设置验证码长度
     * @param int $length
     * @return Conf
     */
    public function setLength($length)
    {
        $this->length = intval($length);
        return $this;
    }

    /**
     * 获取配置值
     * @param $name
     * @author : evalor <master@evalor.cn>
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 十六进制转RGB
     * @param $hexColor
     * @author : evalor <master@evalor.cn>
     * @return array
     */
    function HEXToRGB($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                hexdec(substr($color, 0, 2)),
                hexdec(substr($color, 2, 2)),
                hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                hexdec($r),
                hexdec($g),
                hexdec($b)
            );
        }
        return $rgb;
    }
}
```

### 验证码生成
 
 ::: warning 
  VerifyCode验证码操作类,如果不传入Config实例,则自动实例化一个
 :::
  
```php
$config = new Conf();
$code = new \EasySwoole\VerifyCode\VerifyCode($config);
$code->DrawCode();//生成验证码,返回一个Result对象
```

### 验证码结果类
验证码结果类,由VerifyCode验证码操作类调用 DrawCode() 方法时创建并返回  

```php
    /**
     * 获取验证码图片
     * @author : evalor <master@evalor.cn>
     * @return mixed
     */
    function getImageByte()
    {
        return $this->CaptchaByte;
    }

    /**
     * 返回图片Base64字符串
     * @author : evalor <master@evalor.cn>
     * @return string
     */
    function getImageBase64()
    {
        $base64Data = base64_encode($this->CaptchaByte);
        $Mime = $this->CaptchaMime;
        return "data:{$Mime};base64,{$base64Data}";
    }

    /**
     * 获取验证码内容
     * @author : evalor <master@evalor.cn>
     * @return mixed
     */
    function getImageCode()
    {
        return $this->CaptchaCode;
    }

    /**
     * 获取Mime信息
     * @author : evalor <master@evalor.cn>
     */
    function getImageMime()
    {
        return $this->CaptchaMime;
    }

    /**
     * 获取验证码文件路径
     * @author: eValor < master@evalor.cn >
     */
    function getImageFile()
    {
        return $this->CaptchaFile;
    }
```


### 使用示例
```php
<?php
/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 2018/11/12 0012
 * Time: 16:30
 */

namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\VerifyCode\Conf;

class VerifyCode extends Controller
{
    function index()
    {
        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $this->response()->withHeader('Content-Type','image/png');
        $this->response()->write($code->DrawCode()->getImageByte());
    }

    function getBase64(){
        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        $this->response()->write($code->DrawCode()->getImageBase64());
    }
}
```

## 进阶使用
生成二维码图片并返回
```php
<?php
/**
 *
 * User: luffyQAQ
 * Date: 2019/9/5 15:29
 * Email: <1769360227@qq.com>
 */

namespace App\HttpController\Api\Common;


use App\Service\Common\VerifyCodeService;
use EasySwoole\Http\Message\Status;
use EasySwoole\Utility\Random;
use EasySwoole\VerifyCode\Conf;

class VerifyCode extends CommonBase
{
    static $VERIFY_CODE_TTL = 120;
    static $VERIFY_CODE_LENGTH = 4;

    public function verifyCode()
    {
        $config = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($config);
        //获取随机数
        $random = Random::character(self::$VERIFY_CODE_LENGTH,'1234567890abcdefghijklmnopqrstuvwxyz');
        $code = $code->DrawCode($random);
        $time = time();
        $result = [
            'verifyCode' => $code->getImageBase64(),
            'verifyCodeTime' => $time,
        ];

        $this->response()->setCookie("verifyCodeHash", VerifyCodeService::getVerifyCodeHash($random, $time), $time + self::$VERIFY_CODE_TTL, '/');
        $this->response()->setCookie('verifyCodeTime', $time, $time + self::$VERIFY_CODE_TTL, '/');
        $this->writeJson(Status::CODE_OK, $result, 'success');

    }
}
```
::: warning 
  调用对应的路径接口，即可实现前台验证码显示
 :::
