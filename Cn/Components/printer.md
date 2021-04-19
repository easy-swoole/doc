---
title: easysowole云打印机SDK
meta:
  - name: description
    content: Easyswoole云打印机SDK
  - name: keywords
    content: easysowole云打印机SDK|php 云打印机SDK|easyswoole printer
---

# EasySwoole 云打印 (Printer) 组件

`EasySwoole` 提供了全协程支持的云打印机 `SDK`，易于使用的操作接口和风格，轻松推送海量任务至云打印机。

> 目前仅支持易联云，欢迎 `fork` 本项目贡献您的力量

## 组件要求

- php: >= 7.1
- ext-swoole: >= 4.4.23
- easyswoole/spl: ^1.4
- easyswoole/http-client: ^1.5
- psr/simple-cache: 1.0
- easyswoole/utility: ^1.2
- ext-json: *

## 安装方法

> composer require easyswoole/easy-printer

## 仓库地址

[easyswoole/easy-printer](https://github.com/easy-swoole/easy-printer)

## 基本使用

```php
<?php

use EasySwoole\EasyPrinter\Commands\YiLinkCloud\PrintText;
use EasySwoole\EasyPrinter\EasyPrinter;
use EasySwoole\Utility\FileSystem;
use EasySwoole\Utility\File;

require_once __DIR__ . '/vendor/autoload.php';

class CacheConfig
{
    protected $driver;
    protected $dir;
    protected $prefix;

    public function setDriver(string $driver)
    {
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setDir(string $dir)
    {
        $this->dir = $dir;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}

/**
 * 文件缓存
 * Class FileDriver
 */
class FileDriver implements \Psr\SimpleCache\CacheInterface
{
    /** @var string $dir */
    protected $dir;
    /** @var FileSystem $fileSystem */
    protected $fileSystem;
    /** @var string $prefix */
    protected $prefix;

    public function __construct($dir, $prefix)
    {
        if (empty($dir)) {
            $this->dir = sys_get_temp_dir();
        }
        if (empty($prefix)) {
            $this->prefix = 'easyswoole_cache:';
        }
        $this->fileSystem = new FileSystem();
        File::createDirectory($this->dir);
    }

    /**
     * @return string
     */
    protected function getPrefix(): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . $this->prefix;
    }

    /**
     * 获取缓存的 key
     * @param string $key
     * @return string
     */
    public function getCacheKey(string $key)
    {
        return $this->getPrefix() . $key . '.cache';
    }

    /**
     * 设置缓存
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $file = $this->getCacheKey($key);
        var_dump($file);
        $data = serialize($value);
        $this->fileSystem->put($file, $data);
        if ($ttl < time()) {
            $ttl = $this->getTtlTime($ttl);
        }
        return touch($file, $ttl);
    }

    /**
     * 获取缓存
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @throws Exception
     */
    public function get($key, $default = null)
    {
        $file = $this->getCacheKey($key);
        if ($this->fileSystem->missing($file)) {
            return $default;
        }
        if ($this->fileSystem->lastModified($file) < time()) {
            return $default;
        }
        return unserialize($this->fileSystem->get($file));
    }

    /**
     * 获取缓存过期时间
     * @param null $ttl
     * @return float|int|null
     */
    public function getTtlTime($ttl = null)
    {
        // 如果不设置时间 默认 100 年
        if (is_null($ttl)) {
            $ttl = 3600 * 24 * 30 * 12 * 100;
        }
        $ttl = $ttl + time();
        return $ttl;
    }

    /**
     * 删除缓存
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $file = $this->getCacheKey($key);
        return $this->fileSystem->delete($file);
    }

    /**
     * 清空缓存
     * @return bool|void
     */
    public function clear()
    {
        $files = glob($this->getPrefix() . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }
            unlink($file);
        }
    }

    /**
     * 批量读取缓存
     * @param iterable $keys
     * @param null $default
     * @return array|iterable
     * @throws Exception
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $result = [];
        foreach ($keys as $i => $key) {
            var_dump($key);
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * 批量设置缓存
     * @param iterable $values
     * @param null $ttl
     * @return bool>
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        $ttl = $this->getTtlTime($ttl);
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    /**
     * 批量删除缓存
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $index => $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * 缓存是否存在
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $file = $this->getCacheKey($key);
        return file_exists($file);
    }
}

class Cache implements \Psr\SimpleCache\CacheInterface
{
    protected $driver;

    public function __construct(CacheConfig $cacheConfig)
    {
        $driver = $cacheConfig->getDriver() ?: FileDriver::class;
        $this->driver = new $driver($cacheConfig->getDir(), $cacheConfig->getPrefix());
    }

    public function __call($name, $arguments)
    {
        return $this->driver->{$name}(...$arguments);
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setMultiple($values, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function has($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function get($key, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function deleteMultiple($keys)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function clear()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}


go(function () {
    $cacheConfig = new CacheConfig();
    $cache = new Cache($cacheConfig); // Cache 需要实现 \Psr\SimpleCache\CacheInterface 接口，示例仅实现了文件缓存

    $clientId = '您的易联云应用ID';
    $clientSecret = '您的易联云应用秘钥';
    $driver = EasyPrinter::yiLinkCloud($clientId, $clientSecret, $cache);

    // 新建一条命令
    $PrintCommand = new PrintText();
    $PrintCommand->setMachineCode('打印机编号');
    $PrintCommand->setContent('欢迎使用EasyPrinter!');
    $PrintCommand->setOriginId(md5(microtime()));

    try {
        $response = $driver->sendCommand($PrintCommand);
        var_dump($response);
    } catch (Throwable $throwable) {

    }
});
```

> 上述 `Cache` 参考 [Cache 实现](https://github.com/ice-leng/easyswoole-skeleton/blob/master/src/Component/Cache/SimpleCache.php)仅仅实现了文件缓存，开发者若想使用其他缓存实现，可以自行实现 [PSR-16 CacheInterface 接口]，或使用 [`easyswoole-cache` 组件](https://github.com/easy-swoole/cache)。

## 目前已支持的指令

| 服务商 | 说明                | Command                   |
| :----- | :------------------ | :------------------------ |
| 易联云 | 终端授权 (永久授权) | AuthorizePrinter          |
| 易联云 | 获取请求令牌        | GetAccessToken            |
| 易联云 | 获取机型打印宽度    | GetPrinterInfo            |
| 易联云 | 获取终端状态        | GetPrinterStatus          |
| 易联云 | 添加应用菜单        | PrinterAddMenu            |
| 易联云 | 取消所有未打印订单  | PrinterCancelAll          |
| 易联云 | 取消单条未打印订单  | PrinterCancelOne          |
| 易联云 | 取消LOGO            | PrinterDeleteIcon         |
| 易联云 | 删除终端授权        | PrinterDeletePrinter      |
| 易联云 | 删除内置语音        | PrinterDeleteVoice        |
| 易联云 | 获取订单列表        | PrinterGetOrderPagingList |
| 易联云 | 获取订单状态        | PrinterGetOrderStatus     |
| 易联云 | 获取机型软硬件版本  | PrinterGetVersion         |
| 易联云 | 设置打印方式        | PrinterSetBtnPrinter      |
| 易联云 | 设置LOGO            | PrinterSetIcon            |
| 易联云 | 接单拒单设置        | PrinterSetIfGetOrder      |
| 易联云 | 设置推送URL         | PrinterSetPushUrl         |
| 易联云 | 声音调节            | PrinterSetSound           |
| 易联云 | 设置内置语音        | PrinterSetVoice           |
| 易联云 | 关机重启            | PrinterShutdownRestart    |
| 易联云 | 打印图片            | PrintPicture              |
| 易联云 | 打印文字            | PrintText                 |
