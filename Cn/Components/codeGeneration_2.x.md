---
title: easyswoole 自动生成代码组件
meta:
  - name: description
    content: EasySwoole提供了一个一键生成业务通用代码的组件。
  - name: keywords
    content: easyswoole自动生成代码|swoole 自动生成代码
---

# code-generation 2.x (代码生成组件)
`EasySwoole` 代码生成组件，使用命令行就可以一键生成业务通用代码，支持生成的代码如下：
- 一键生成 `项目初始化 baseController、baseModel、baseUnitTest`；
- 一键生成 `表 Model，自带属性注释`；
- 一键生成 `表 curd 控制器，自带 5 个 curd 方法`；
- 一键生成 `控制器单元测试用例，测试 5 个 curd 方法`。

## 组件版本说明
`code-generation` 组件当前最新稳定版本为 `2.x`，`code-generation 2.x` 相对于 `1.x` 版本，兼容了 `EasySwoole` 的最新注解组件，支持了新的 `EasySwoole` 文档自动生成，以及最新的 `phpunit` 版本，同时完善了 `Model->getList` 返回参数，以及控制器 `getOne、getList` 的 `response` 参数注解。

> 注：`code-generation 1.x` 版本组件文档详见 [code-generation 1.x](/Components/codeGeneration.md)

## 组件要求
- easyswoole/trigger: ^1.0
- easyswoole/socket: ^1.0
- easyswoole/orm: ^1.4
- nette/php-generator: ^3.2
- easyswoole/http-annotation: ^1.4
- php-curl-class/php-curl-class: ^8.5
- easyswoole/command: ^1.1

## 安装方法
> composer require easyswoole/code-generation=2.x

## 仓库地址
[easyswoole/code-generation 2.x](https://github.com/easy-swoole/code-generation)

## 基本使用
配置文件 `dev.php` 配置如下：
```php
<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3
        ],
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 15
        ]
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,
    'MYSQL' => [
        'host'          => '127.0.0.1',
        'port'          => 3306,
        'user'          => 'easyswoole',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'password'      => 'easyswoole100%',
        'database'      => 'easyswoole',
        'maxObjectNum'  => 20,
        'minObjectNum'  => 5,
        'getObjectTimeout'  => 3.0,
    ]
];
```

在项目根目录新建一个 `TestCodeGeneration.php`，代码内容如下：
```php
<?php

include __DIR__ . "/vendor/autoload.php";

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

go(function () {
    // 生成基础类
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Controller\ControllerGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelGeneration();
    $generation->generate();

    // 获取 dev.php 中的 MYSQL 配置文件，配置文件具体说明请看：https://www.easyswoole.com/QuickStart/config.html
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));

    // 获取数据库连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);

    // 指定表名(这里以 user_list 表作为示例表，表结构请看下文)
    $tableName = 'user_list';
    $codeGeneration = new EasySwoole\CodeGeneration\CodeGeneration($tableName, $connection);

    // 生成 model (默认生成模型的路径前缀为 App\Model 目录，例如：如下生成的模型文件为 User模型【\App\Model\User\UserModel.php】 和 基础模型【\App\Model\User\BaseModel.php】)
    $codeGeneration->generationModel("\\User");

    // 生成 controller (默认生成控制器的路径前缀为 App\HttpController 目录，例如：如下生成的控制器文件为 User控制器【\App\Model\Api\User\User.php】 和 基础控制器【\App\HttpController\Api\Base.php】)
    $codeGeneration->generationController("\\Api\\User", null);

    // 生成 unitTest (默认生成 unitTest 的路径前缀为项目根目录的 UnitTest 目录，例如：如下生成的单元测试用例为 UserTest【UnitTest\Api\User\UserTest.php】 和 基础单元测试用例【UnitTest\BaseTest.php】)
    $codeGeneration->generationUnitTest("\\Api\\User", null);
});
\Swoole\Timer::clearAll();
```

在配置好数据库和数据库配置文件之后，然后在项目根目录运行 `TestCodeGeneration.php`(`php TestCodeGeneration.php`)，即可看到自动生成的模型、控制器和单元测试用例。

::: warning
`EasySwoole\CodeGeneration\CodeGeneration` 方法可自行查看，代码很简单。
::: 

### 示例表结构(`user_list`)
```sql
CREATE TABLE `user_list` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 命令行使用
由于命令行特殊的特性，命令行功能支持并不完善，如果想要体验全部功能，请使用 `\EasySwoole\CodeGeneration\CodeGeneration` 生成，或参考 `\EasySwoole\CodeGeneration\CodeGeneration` 代码生成。

### 注册命令

配置数据库配置文件 `dev.php`，配置文件内容详见上文。

在 `EasySwoole` 项目根目录中的 `bootstrap.php`(即 `bootstrap事件`) 中使用 `Di` 注入配置项:
```php
<?php
// 全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

## 1 直接注入 mysql 连接
// 获取 dev.php 中的 MYSQL 配置文件，配置文件具体说明请看：https://www.easyswoole.com/QuickStart/config.html
$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
// 获取连接
$connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection', $connection);

## 2 直接注入 mysql 配置对象
//   $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//   \EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection', $mysqlConfig);

## 3 直接注入 mysql 配置项
//   \EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));


// 以上 3 种注入 mysql 连接的方式均可，用户可以任选 1 种进行注入 mysql


// 注入执行目录项，后面的为默认值，initClass 不能通过注入改变目录
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.modelBaseNameSpace', "App\\Model");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.controllerBaseNameSpace', "App\\HttpController");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.unitTestBaseNameSpace', "UnitTest");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.rootPath', getcwd());
```

然后即可使用命令实现自动生成代码，具体使用命令如下（使用如下命令前提：必须先在 `bootstrap事件` 中使用 `Di` 注册上述说明的配置项）：
```
php ./vendor/bin/code-generator
 ______                          _____                              _
|  ____|                        / ____|                            | |
| |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
|  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
| |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
|______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                         __/ |
                        |___/

Code auto generation tool
Usage:
  ./vendor/bin/code-generator ACTION [--opts ...]
Actions:
  init  initialization
  all   specify build
Options:
  --tableName       specify table name
  --modelPath       specify model path
  --controllerPath  specify controller path
  --unitTestPath    specify unit-test path

php ./vendor/bin/code-generator all --tableName="tableName" --modelPath="生成的模型所在目录" [--controllerPath="生成的控制器所在目录"] [--unitTestPath="生成的单元测试用例所在目录"]

// 生成基础模型(BaseModel)、基础控制器(BaseController)、基础单元测试用例(BaseUnitTest)
php ./vendor/bin/code-generator init

// 自动生成指定的模型、控制器、单元测试用例和 基础模型、基础控制器、基础单元测试用例
php vendor/bin/code-generator all --tableName="user_list" --modelPath="\\User" --controllerPath="\\Api\\User" --unitTestPath="\\User"
```

执行结果示例如下：
```bash
[root@localhost es-code-generation]# php ./vendor/bin/code-generator init
┌────────────┬─────────────────────────────────────────────────────────────────────┐
│ className  │                              filePath                               │
├────────────┼─────────────────────────────────────────────────────────────────────┤
│ Model      │ /www/wwwroot/easyswoole-code-generation/App/Model/BaseModel.php     │
├────────────┼─────────────────────────────────────────────────────────────────────┤
│ Controller │ /www/wwwroot/easyswoole-code-generation/App/HttpController/Base.php │
├────────────┼─────────────────────────────────────────────────────────────────────┤
│ UnitTest   │ /www/wwwroot/easyswoole-code-generation/UnitTest/BaseTest.php       │
└────────────┴─────────────────────────────────────────────────────────────────────┘

[root@localhost es-code-generation]# php ./vendor/bin/code-generator all --tableName="user_list" --modelPath="\\User" --controllerPath="\\Api\\User" --unitTestPath="\\User"
┌────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ className  │                                   filePath                                   │
├────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Model      │ /www/wwwroot/easyswoole-code-generation/App/Model/User/UserModel.php         │
├────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Controller │ /www/wwwroot/easyswoole-code-generation/App/HttpController/Api/User/User.php │
├────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ UnitTest   │ /www/wwwroot/easyswoole-code-generation/UnitTest/User/UserTest.php           │
└────────────┴──────────────────────────────────────────────────────────────────────────────┘

```


## 独立使用

### 生成器流程说明
- 通过 `\EasySwoole\ORM\Utility\TableObjectGeneration`，传入 `\EasySwoole\ORM\Db\Connection` 连接对象，通过 `generationTable` 方法获取表结构对象；
- 实例化类生成器配置，配置命名空间、生成文件路径、类名等(详情看下面)；
- 实例化生成器对象，调用 `generate`方法生成。

### 生成器基础配置项
- `extendClass` 继承类，默认为`\EasySwoole\ORM\AbstractModel::class`
- directory 生成路径,生成路径默认为 `rootPath+namespace`对应路径,namespace路径将自动通过`composer.json->(autoload/autoload-dev)['psr-4']` 配置目录生成,如果没有则默认为根目录
- namespace 命名空间配置.
- className 类名
- rootPath 项目根目录,默认为执行目录.

### 获取数据表结构
所有生成器都依赖于数据表结构对象`EasySwoole\ORM\Utility\Schema\Table`
```php
<?php
$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//获取连接
$connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
$tableName = 'user_list';
//获取数据表结构对象
$tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
$schemaInfo = $tableObjectGeneration->generationTable();

```

### Model生成
#### Model配置项说明
- extendClass 继承类,默认为`\EasySwoole\ORM\AbstractModel::class`
- directory 生成路径,生成路径默认为 `rootPath+namespace`对应路径,namespace路径将自动通过`composer.json->(autoload/autoload-dev)['psr-4']` 配置目录生成,如果没有则默认为根目录
- namespace 命名空间配置.默认为 `App\Model`
- className 类名,Model配置无效,强制为`realTableName+fileSuffix `
- rootPath 项目根目录,默认为执行目录.
- tablePre 表前缀,如果有配置,es_user 表=> UserModel
- table 表结构对象
- realTableName 真实表名,通过下划线形式转为大驼峰,自动转化.用于生成最后的类名和文件名.
- fileSuffix 文件后缀,默认为`Model`,用于生成最后的类名和文件名.
- ignoreString 默认为\['list', 'log'\], //生成时忽略表名存在的字符,例如user_list将生成=>UserModel

#### Model生成示例:
```php
<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:26
 */
include "./vendor/autoload.php";

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

go(function () {
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
    //获取连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
    $tableName = 'user_list';
    //获取数据表结构对象
    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
    $schemaInfo = $tableObjectGeneration->generationTable();

    $tablePre = '';//表前缀
    $path = "App\\Model";
    $extendClass = \EasySwoole\ORM\AbstractModel::class;
    $modelConfig = new \EasySwoole\CodeGeneration\ModelGeneration\ModelConfig($schemaInfo, $tablePre, "{$path}", $extendClass);
    $modelConfig->setRootPath(EASYSWOOLE_ROOT);//设置项目运行目录,默认为当前执行脚本目录.
    $modelConfig->setIgnoreString(['list', 'log']);//生成时忽略表名存在的字符,例如user_list将生成=>UserModel

    $modelGeneration = new \EasySwoole\CodeGeneration\ModelGeneration\ModelGeneration($modelConfig);
    $result = $modelGeneration->generate();
    var_dump($result);//生成成功返回生成文件路径,否则返回false
});
\Swoole\Timer::clearAll();
```

#### Model方法
Model方法默认生成一个`GetList`方法,用于获取列表.

```php
<?php
public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): array
{
    $list = $this
        ->withTotalCount()
        ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
        ->field($field)
        ->page($page, $pageSize)
        ->all();
    $total = $this->lastQueryResult()->getTotalCount();;
    return ['total' => $total, 'list' => $list];
}
```
::: warning
可参考`EasySwoole\CodeGeneration\ModelGeneration\Method\GetList`自定义其他方法.再进行注入即可.
```php
<?php
$modelGeneration = new \EasySwoole\CodeGeneration\ModelGeneration\ModelGeneration($modelConfig);
// 注入GetList方法
$modelGeneration->addGenerationMethod(new \EasySwoole\CodeGeneration\ModelGeneration\Method\GetList($modelGeneration));
```
:::


### Controller生成
#### Controller 配置项说明
Controller配置项继承与Model配置项
- modelClass Model类类名(包含命名空间),Controller生成依赖于Model,所以需要传入Model类类名
- authSessionName 权限验证session参数名,比如在需要用户登录的控制器方法中,都需要传入session字段名用于验权,controller将在生成方法时自动生成验证这个session参数的注解,默认为空
- extendClass 继承类,默认为`\EasySwoole\HttpAnnotation\AnnotationController`
- directory 生成路径,生成路径默认为 `rootPath+namespace`对应路径,namespace路径将自动通过`composer.json->(autoload/autoload-dev)['psr-4']` 配置目录生成,如果没有则默认为根目录
- namespace 命名空间配置.默认为 `App\\HttpController`
- className 类名,Model配置无效,强制为`realTableName+fileSuffix `
- fileSuffix 文件后缀,默认为空,用于生成最后的类名和文件名.
- ignoreString 默认为\['list', 'log'\], //生成时忽略表名存在的字符,例如user_list将生成=>User


#### controller生成示例
```php
<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:26
 */
include "./vendor/autoload.php";
\EasySwoole\EasySwoole\Core::getInstance()->initialize();

go(function () {
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
    //获取连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
    $tableName = 'user_list';
    //获取数据表结构对象
    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
    $schemaInfo = $tableObjectGeneration->generationTable();


    $tablePre = '';//表前缀
    $path = "App\\HttpController";
    $extendClass = \EasySwoole\HttpAnnotation\AnnotationController::class;
    $modelClass = \App\Model\UserModel::class;//$modelGeneration->getConfig()->getNamespace() . '\\' . $modelGeneration->getClassName();
    $controllerConfig = new \EasySwoole\CodeGeneration\ControllerGeneration\ControllerConfig($modelClass, $schemaInfo, $tablePre, "{$path}", $extendClass);
    $controllerConfig->setRootPath(EASYSWOOLE_ROOT);
    $controllerGeneration = new \EasySwoole\CodeGeneration\ControllerGeneration\ControllerGeneration($controllerConfig);
    $result = $controllerGeneration->generate();
    var_dump($result);
});
\Swoole\Timer::clearAll();
```
#### Controller方法.
Controller支持了5个方法,`Add`,`Delete`,`GetList`,`GetOne`,`Update`.
自定义其他方法可参考Model方法自定义.

### unitTest
单元测试生成器生成.生成后的文件为作者本人自定义风格代码,需要依赖于`BaseUnitTest`
```php
<?php

namespace UnitTest;

use Curl\Curl;
use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\TestCase;

/**
 * BaseTest
 * Class BaseTest
 * Create With ClassGeneration
 */
class BaseTest extends TestCase
{
	public static $isInit = 0;

	/** @var Curl */
	public $curl;
	public $apiBase = 'http://127.0.0.1:9501';
	public $modelName;


	public function request($action, $data = [], $modelName = null)
	{
		$modelName = $modelName ?? $this->modelName;
		$url = $this->apiBase . '/' . $modelName . '/' . $action;
		$curl = $this->curl;
		$curl->post($url, $data);
		if ($curl->response) {
		//            var_dump($curl->response);
		} else {
		    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "
";
		}
		$this->assertTrue(!!$curl->response);
		$this->assertEquals(200, $curl->response->code, $curl->response->msg);
		return $curl->response;
	}


	public function setUp()
	{
		if (self::$isInit == 1) {
		    return true;
		}
		require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
		defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', dirname(__FILE__, 2));
		require_once dirname(__FILE__, 2) . '/EasySwooleEvent.php';
		Core::getInstance()->initialize()->globalInitialize();
		self::$isInit = 1;
		$this->curl = new Curl();
	}
}
```

#### unitTest配置项说明
unitTest配置项继承于Model配置项
- modelClass Model类类名(包含命名空间),UnitTest生成依赖于Model,所以需要传入Model类类名
- ControllerClass ControllerClass类类名(包含命名空间),UnitTest生成依赖于ControllerClass,所以需要传入ControllerClass类类名
- extendClass 继承类,默认为`\PHPUnit\Framework\TestCase`
- directory 生成路径,生成路径默认为 `rootPath+namespace`对应路径,namespace路径将自动通过`composer.json->(autoload/autoload-dev)['psr-4']` 配置目录生成,如果没有则默认为根目录
- namespace 命名空间配置.默认为 `UnitTest`
- className 类名,Model配置无效,强制为`realTableName+fileSuffix `
- fileSuffix 文件后缀,默认为`Test`,用于生成最后的类名和文件名.
- ignoreString 默认为\['list', 'log'\], //生成时忽略表名存在的字符,例如user_list将生成=>UserTest


#### unitTest生成示例
```php
<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:26
 */
include "./vendor/autoload.php";
\EasySwoole\EasySwoole\Core::getInstance()->initialize();

go(function () {
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
    //获取连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
    $tableName = 'user_list';
    //获取数据表结构对象
    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
    $schemaInfo = $tableObjectGeneration->generationTable();

    $path = "UnitTest";
    $modelClass = \App\Model\UserModel::class;
    $controllerClass= \App\HttpController\User::class;
    $extendClass = \PHPUnit\Framework\TestCase::class;
    $tablePre = '';//表前缀
    $controllerConfig = new \EasySwoole\CodeGeneration\UnitTest\UnitTestConfig($modelClass, $controllerClass, $schemaInfo, $tablePre, "{$path}", $extendClass);
    $controllerConfig->setRootPath(EASYSWOOLE_ROOT);
    $unitTestGeneration = new \EasySwoole\CodeGeneration\UnitTest\UnitTestGeneration($controllerConfig);
    $result = $unitTestGeneration->generate();
    var_dump($result);
});
\Swoole\Timer::clearAll();
```

#### UnitTest方法.
UnitTest支持了5个方法,`Add`,`Delete`,`GetList`,`GetOne`,`Update`.
自定义其他方法可参考Model方法自定义.


### 初始化类
为了方便开发,提供了Controller,Model,UnitTest的初始化类.

#### Controller
生成方法:

```php
<?php
$generation = new \EasySwoole\CodeGeneration\InitBaseClass\Controller\ControllerGeneration();
    $generation->generate();
```
类内容:
```php
<?php

namespace App\HttpController;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;
use EasySwoole\Http\Message\Status;

/**
 * Base
 * Class Base
 * Create With ClassGeneration
 */
class Base extends AnnotationController
{
	public function index()
	{
		$this->actionNotFound('index');
	}


	public function clientRealIP($headerName = 'x-real-ip')
	{
		$server = ServerManager::getInstance()->getSwooleServer();
		$client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
		$clientAddress = $client['remote_ip'];
		$xri = $this->request()->getHeader($headerName);
		$xff = $this->request()->getHeader('x-forwarded-for');
		if ($clientAddress === '127.0.0.1') {
		    if (!empty($xri)) {  // 如果有xri 则判定为前端有NGINX等代理
		        $clientAddress = $xri[0];
		    } elseif (!empty($xff)) {  // 如果不存在xri 则继续判断xff
		        $list = explode(',', $xff[0]);
		        if (isset($list[0])) $clientAddress = $list[0];
		    }
		}
		return $clientAddress;
	}


	public function onException(\Throwable $throwable): void
	{
		if ($throwable instanceof ParamValidateError) {
		    $this->writeJson(Status::CODE_BAD_REQUEST,[], $throwable->getValidate()->getError()->__toString());
		}  else {
		    Trigger::getInstance()->throwable($throwable);
		    $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, $throwable->getMessage());
		}
	}
}

```

### Model
生成方法:
```php
<?php
$generation = new \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelGeneration();
$generation->generate();
```
类内容:
```php
<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\DbManager;

/**
 * BaseModel
 * Class BaseModel
 * Create With ClassGeneration
 */
class BaseModel extends AbstractModel
{
	public static function transaction(callable $callable)
	{
		try {
		    DbManager::getInstance()->startTransaction();
		    $result = $callable();
		    DbManager::getInstance()->commit();
		    return $result;
		} catch (\Throwable $throwable) {
		    DbManager::getInstance()->rollback();
		    throw $throwable;;
		}
	}
}
```

### UnitTest
生成方法:
```php
<?php
$generation = new \EasySwoole\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration();
$generation->generate();
```
类内容:
```php
<?php

namespace UnitTest;

use Curl\Curl;
use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\TestCase;

/**
 * BaseTest
 * Class BaseTest
 * Create With ClassGeneration
 */
class BaseTest extends TestCase
{
	public static $isInit = 0;

	/** @var Curl */
	public $curl;
	public $apiBase = 'http://127.0.0.1:9501';
	public $modelName;


	public function request($action, $data = [], $modelName = null)
	{
		$modelName = $modelName ?? $this->modelName;
		$url = $this->apiBase . '/' . $modelName . '/' . $action;
		$curl = $this->curl;
		$curl->post($url, $data);
		if ($curl->response) {
		//            var_dump($curl->response);
		} else {
		    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "
";
		}
		$this->assertTrue(!!$curl->response);
		$this->assertEquals(200, $curl->response->code, $curl->response->msg);
		return $curl->response;
	}


	public function setUp()
	{
		if (self::$isInit == 1) {
		    return true;
		}
		require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
		defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', dirname(__FILE__, 2));
		require_once dirname(__FILE__, 2) . '/EasySwooleEvent.php';
		Core::getInstance()->initialize()->globalInitialize();
		self::$isInit = 1;
		$this->curl = new Curl();
	}
}
```
