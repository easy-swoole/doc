---
title: easyswoole swoole-协程fastcgi客户端
meta:
  - name: description
    content: easyswoole swoole-协程fastcgi客户端
  - name: keywords
    content: easyswoole swoole-协程fastcgi客户端|easyswoole|swoole|coroutine
---

# 协程 FastCGI 客户端
通过 FastCGI 客户端，可以直接与 PHP-FPM 服务进行交互，无需通过任何 HTTP 反向代理

## 快速调用
test.php
```php
<?php
echo 'Hello :'.$_POST['who'];
```

```php
<?php
Swoole\Coroutine::create(function (){
    echo \Swoole\Coroutine\FastCGI\Client::call(
        '127.0.0.1:9000', // FPM监听地址, 也可以是形如 unix://path/to/fpm.sock 的unixsocket地址
        '/tmp/test.php', // 想要执行的入口文件
        ['who' => 'EasySwoole'] // 附带的POST信息
    );
});
```

## PSR风格
```php
<?php
Swoole\Coroutine::create(function (){
    try {
        $client = new \Swoole\Coroutine\FastCGI\Client('127.0.0.1', 9000);
        $request = (new \Swoole\FastCGI\HttpRequest())
            ->withScriptFilename('/tmp/test.php')
            ->withMethod('POST')
            ->withBody(['who' => 'EasySwoole']);
        $response = $client->execute($request);
        echo "Result: {$response->getBody()}".PHP_EOL;
    } catch (\Swoole\Coroutine\FastCGI\Client\Exception $exception) {
        echo  $exception->getMessage();
    }
});
```

## 方法 

### call
作用：创建一个新的客户端连接，向FPM服务发起请求并接收响应 （fpm只支持短连接，通常情况下，创建持久化对象没有太大意义）      
方法原型：Client::call(string $url, string $path, $data = '', float $timeout = -1): string       
参数：
- $url 目标服务器地址+端口 或者 unixSock地址
- $path 执行的入口文件
- $data 附带post参数
- $timeout 超时时间

返回值：
- 返回服务器响应的body
- 发送错误抛出 `Swoole\Coroutine\FastCGI\Client\Exception`

### __construct
作用：构造方法 指定 fpm 服务器      
方法原型：__construct(string $host, int $port = 0)       
参数：
- $host 目标服务器地址
- $port 端口 unixSock 无需传入

### execute
作用：执行请求，返回响应        
方法原型：execute(Request $request, float $timeout = -1): Response
参数：
- $request Swoole\FastCGI\Request|Swoole\FastCGI\HttpRequest
- $timeout 超时时间 -1永不

## 请求及响应类

### Swoole\FastCGI\HttpRequest
```php
<?php
/**
 * This file is part of Swoole.
 *
 * @link     https://www.swoole.com
 * @contact  team@swoole.com
 * @license  https://github.com/swoole/library/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Swoole\FastCGI;

use InvalidArgumentException;

class HttpRequest extends Request
{
    protected $params = [
        'REQUEST_SCHEME' => 'http',
        'REQUEST_METHOD' => 'GET',
        'DOCUMENT_ROOT' => '',
        'SCRIPT_FILENAME' => '',
        'SCRIPT_NAME' => '',
        'DOCUMENT_URI' => '/',
        'REQUEST_URI' => '/',
        'QUERY_STRING' => '',
        'CONTENT_TYPE' => 'text/plain',
        'CONTENT_LENGTH' => '0',
        'GATEWAY_INTERFACE' => 'CGI/1.1',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'SERVER_SOFTWARE' => 'swoole/' . SWOOLE_VERSION,
        'REMOTE_ADDR' => 'unknown',
        'REMOTE_PORT' => '0',
        'SERVER_ADDR' => 'unknown',
        'SERVER_PORT' => '0',
        'SERVER_NAME' => 'Swoole',
        'REDIRECT_STATUS' => '200',
    ];

    public function getScheme(): ?string
    {
        return $this->params['REQUEST_SCHEME'] ?? null;
    }

    public function withScheme(string $scheme): self
    {
        $this->params['REQUEST_SCHEME'] = $scheme;
        return $this;
    }

    public function withoutScheme(): void
    {
        unset($this->params['REQUEST_SCHEME']);
    }

    public function getMethod(): ?string
    {
        return $this->params['REQUEST_METHOD'] ?? null;
    }

    public function withMethod(string $method): self
    {
        $this->params['REQUEST_METHOD'] = $method;
        return $this;
    }

    public function withoutMethod(): void
    {
        unset($this->params['REQUEST_METHOD']);
    }

    public function getDocumentRoot(): ?string
    {
        return $this->params['DOCUMENT_ROOT'] ?? null;
    }

    public function withDocumentRoot(string $documentRoot): self
    {
        $this->params['DOCUMENT_ROOT'] = $documentRoot;
        return $this;
    }

    public function withoutDocumentRoot(): void
    {
        unset($this->params['DOCUMENT_ROOT']);
    }

    public function getScriptFilename(): ?string
    {
        return $this->params['SCRIPT_FILENAME'] ?? null;
    }

    public function withScriptFilename(string $scriptFilename): self
    {
        $this->params['SCRIPT_FILENAME'] = $scriptFilename;
        return $this;
    }

    public function withoutScriptFilename(): void
    {
        unset($this->params['SCRIPT_FILENAME']);
    }

    public function getScriptName(): ?string
    {
        return $this->params['SCRIPT_NAME'] ?? null;
    }

    public function withScriptName(string $scriptName): self
    {
        $this->params['SCRIPT_NAME'] = $scriptName;
        return $this;
    }

    public function withoutScriptName(): void
    {
        unset($this->params['SCRIPT_NAME']);
    }

    public function withUri(string $uri): self
    {
        $info = parse_url($uri);
        return $this->withRequestUri($uri)
            ->withDocumentUri($info['path'] ?? '')
            ->withQueryString($info['query'] ?? '');
    }

    public function getDocumentUri(): ?string
    {
        return $this->params['DOCUMENT_URI'] ?? null;
    }

    public function withDocumentUri(string $documentUri): self
    {
        $this->params['DOCUMENT_URI'] = $documentUri;
        return $this;
    }

    public function withoutDocumentUri(): void
    {
        unset($this->params['DOCUMENT_URI']);
    }

    public function getRequestUri(): ?string
    {
        return $this->params['REQUEST_URI'] ?? null;
    }

    public function withRequestUri(string $requestUri): self
    {
        $this->params['REQUEST_URI'] = $requestUri;
        return $this;
    }

    public function withoutRequestUri(): void
    {
        unset($this->params['REQUEST_URI']);
    }

    public function withQuery($query): self
    {
        if (is_array($query)) {
            $query = http_build_query($query);
        }
        return $this->withQueryString($query);
    }

    public function getQueryString(): ?string
    {
        return $this->params['QUERY_STRING'] ?? null;
    }

    public function withQueryString(string $queryString): self
    {
        $this->params['QUERY_STRING'] = $queryString;
        return $this;
    }

    public function withoutQueryString(): void
    {
        unset($this->params['QUERY_STRING']);
    }

    public function getContentType(): ?string
    {
        return $this->params['CONTENT_TYPE'] ?? null;
    }

    public function withContentType(string $contentType): self
    {
        $this->params['CONTENT_TYPE'] = $contentType;
        return $this;
    }

    public function withoutContentType(): void
    {
        unset($this->params['CONTENT_TYPE']);
    }

    public function getContentLength(): ?int
    {
        return isset($this->params['CONTENT_LENGTH']) ? (int) $this->params['CONTENT_LENGTH'] : null;
    }

    public function withContentLength(int $contentLength): self
    {
        $this->params['CONTENT_LENGTH'] = (string) $contentLength;
        return $this;
    }

    public function withoutContentLength(): void
    {
        unset($this->params['CONTENT_LENGTH']);
    }

    public function getGatewayInterface(): ?string
    {
        return $this->params['GATEWAY_INTERFACE'] ?? null;
    }

    public function withGatewayInterface(string $gatewayInterface): self
    {
        $this->params['GATEWAY_INTERFACE'] = $gatewayInterface;
        return $this;
    }

    public function withoutGatewayInterface(): void
    {
        unset($this->params['GATEWAY_INTERFACE']);
    }

    public function getServerProtocol(): ?string
    {
        return $this->params['SERVER_PROTOCOL'] ?? null;
    }

    public function withServerProtocol(string $serverProtocol): self
    {
        $this->params['SERVER_PROTOCOL'] = $serverProtocol;
        return $this;
    }

    public function withoutServerProtocol(): void
    {
        unset($this->params['SERVER_PROTOCOL']);
    }

    public function withProtocolVersion(string $protocolVersion): self
    {
        if (!is_numeric($protocolVersion)) {
            throw new InvalidArgumentException('Protocol version must be numeric');
        }
        $this->params['SERVER_PROTOCOL'] = "HTTP/{$protocolVersion}";
        return $this;
    }

    public function getServerSoftware(): ?string
    {
        return $this->params['SERVER_SOFTWARE'] ?? null;
    }

    public function withServerSoftware(string $serverSoftware): self
    {
        $this->params['SERVER_SOFTWARE'] = $serverSoftware;
        return $this;
    }

    public function withoutServerSoftware(): void
    {
        unset($this->params['SERVER_SOFTWARE']);
    }

    public function getRemoteAddr(): ?string
    {
        return $this->params['REMOTE_ADDR'] ?? null;
    }

    public function withRemoteAddr(string $remoteAddr): self
    {
        $this->params['REMOTE_ADDR'] = $remoteAddr;
        return $this;
    }

    public function withoutRemoteAddr(): void
    {
        unset($this->params['REMOTE_ADDR']);
    }

    public function getRemotePort(): ?int
    {
        return isset($this->params['REMOTE_PORT']) ? (int) $this->params['REMOTE_PORT'] : null;
    }

    public function withRemotePort(int $remotePort): self
    {
        $this->params['REMOTE_PORT'] = (string) $remotePort;
        return $this;
    }

    public function withoutRemotePort(): void
    {
        unset($this->params['REMOTE_PORT']);
    }

    public function getServerAddr(): ?string
    {
        return $this->params['SERVER_ADDR'] ?? null;
    }

    public function withServerAddr(string $serverAddr): self
    {
        $this->params['SERVER_ADDR'] = $serverAddr;
        return $this;
    }

    public function withoutServerAddr(): void
    {
        unset($this->params['SERVER_ADDR']);
    }

    public function getServerPort(): ?int
    {
        return isset($this->params['SERVER_PORT']) ? (int) $this->params['SERVER_PORT'] : null;
    }

    public function withServerPort(int $serverPort): self
    {
        $this->params['SERVER_PORT'] = (string) $serverPort;
        return $this;
    }

    public function withoutServerPort(): void
    {
        unset($this->params['SERVER_PORT']);
    }

    public function getServerName(): ?string
    {
        return $this->params['SERVER_NAME'] ?? null;
    }

    public function withServerName(string $serverName): self
    {
        $this->params['SERVER_NAME'] = $serverName;
        return $this;
    }

    public function withoutServerName(): void
    {
        unset($this->params['SERVER_NAME']);
    }

    public function getRedirectStatus(): ?string
    {
        return $this->params['REDIRECT_STATUS'] ?? null;
    }

    public function withRedirectStatus(string $redirectStatus): self
    {
        $this->params['REDIRECT_STATUS'] = $redirectStatus;
        return $this;
    }

    public function withoutRedirectStatus(): void
    {
        unset($this->params['REDIRECT_STATUS']);
    }

    public function getHeader(string $name): ?string
    {
        return $this->params[static::convertHeaderNameToParamName($name)] ?? null;
    }

    public function withHeader(string $name, string $value): self
    {
        $this->params[static::convertHeaderNameToParamName($name)] = $value;
        return $this;
    }

    public function withoutHeader(string $name): void
    {
        unset($this->params[static::convertHeaderNameToParamName($name)]);
    }

    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->params as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headers[static::convertParamNameToHeaderName($name)] = $value;
            }
        }
        return $headers;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }
        return $this;
    }

    /** @return $this */
    public function withBody($body): Message
    {
        if (is_array($body)) {
            $body = http_build_query($body);
            $this->withContentType('application/x-www-form-urlencoded');
        }
        parent::withBody($body);
        return $this->withContentLength(strlen($body));
    }

    protected static function convertHeaderNameToParamName(string $name)
    {
        return 'HTTP_' . str_replace('-', '_', strtoupper($name));
    }

    protected static function convertParamNameToHeaderName(string $name)
    {
        return ucwords(str_replace('_', '-', substr($name, strlen('HTTP_'))), '-');
    }
}
```

### Swoole\FastCGI\HttpResponse

```php
<?php
/**
 * This file is part of Swoole.
 *
 * @link     https://www.swoole.com
 * @contact  team@swoole.com
 * @license  https://github.com/swoole/library/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Swoole\FastCGI;

use Swoole\Http\Status;

class HttpResponse extends Response
{
    /** @var int */
    protected $statusCode;

    /** @var string */
    protected $reasonPhrase;

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $headersMap = [];

    /** @var array */
    protected $setCookieHeaderLines = [];

    public function __construct(array $records = [])
    {
        parent::__construct($records);
        $body = (string) $this->getBody();
        if (strlen($body) === 0) {
            return;
        }
        $array = explode("\r\n\r\n", $body, 2); // An array that contains the HTTP headers and the body.
        if (count($array) != 2) {
            $this->withStatusCode(Status::BAD_GATEWAY)->withReasonPhrase('Invalid FastCGI Response')->withError($body);
            return;
        }
        $headers = explode("\r\n", $array[0]);
        $body = $array[1];
        foreach ($headers as $header) {
            $array = explode(':', $header, 2); // An array that contains the name and the value of an HTTP header.
            if (count($array) != 2) {
                continue; // Invalid HTTP header? Ignore it!
            }
            $name = trim($array[0]);
            $value = trim($array[1]);
            if (strcasecmp($name, 'Status') === 0) {
                $array = explode(' ', $value, 2); // An array that contains the status code (and the reason phrase).
                $statusCode = $array[0];
                $reasonPhrase = $array[1] ?? null;
            } elseif (strcasecmp($name, 'Set-Cookie') === 0) {
                $this->withSetCookieHeaderLine($value);
            } else {
                $this->withHeader($name, $value);
            }
        }
        $statusCode = (int) ($statusCode ?? Status::OK);
        $reasonPhrase = (string) ($reasonPhrase ?? Status::getReasonPhrase($statusCode));
        $this->withStatusCode($statusCode)->withReasonPhrase($reasonPhrase);
        $this->withBody($body);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withReasonPhrase(string $reasonPhrase): self
    {
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    public function getHeader(string $name): ?string
    {
        $name = $this->headersMap[strtolower($name)] ?? null;
        return $name ? $this->headers[$name] : null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        $this->headersMap[strtolower($name)] = $name;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }
        return $this;
    }

    public function getSetCookieHeaderLines(): array
    {
        return $this->setCookieHeaderLines;
    }

    public function withSetCookieHeaderLine(string $value): self
    {
        $this->setCookieHeaderLines[] = $value;
        return $this;
    }
}
```
