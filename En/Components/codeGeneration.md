# code-generation
The easyswoole code generation component can use the command line to generate business common code with one key code. The supporting codes are as follows:
- One click build project initialization baseController,baseModel,baseUnitTest.
- One key generation table model, with attribute annotation
- One click generate table curd controller with 5 curd methods
- Generate controller unit test cases with one key and test 5 curd methods


## install
```bash
composer require easyswoole/code-generation
```

## use
```php
<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:26
 */
include "./vendor/autoload.php";
\EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();

go(function () {
    //Generate base class

    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Controller\ControllerGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelGeneration();
    $generation->generate();
    

    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
    //Get connection
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
    $tableName = 'user_list';

    $codeGeneration = new EasySwoole\CodeGeneration\CodeGeneration($tableName, $connection);
    //Build model
    $codeGeneration->generationModel("\\User");
    //Generate controller
    $codeGeneration->generationController("\\Api\\User", null);
    //Generate unitTest
    $codeGeneration->generationUnitTest("\\User", null);
});

```
::: warning
`EasySwoole\CodeGeneration\CodeGeneration` The method can be viewed by itself, and the code is very simple.  
::: 


## Command line use.
Due to the command line features, the command line function support is not perfect. If you need to experience all the functions, please use the `EasySwoole\CodeGeneration\CodeGeneration` or `EasySwoole\CodeGeneration\CodeGeneration`.
### Registration command
Inject configuration item in ` bootstrap event ` di:
```php
<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-21
 * Time: 11:20
 */

\EasySwoole\EasySwoole\Core::getInstance()->initialize();
$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//Get connection
$connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
//Inject MySQL connection
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',$connection);
//Inject MySQL configuration object directly
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',$mysqlConfig);
//Inject MySQL configuration item directly
//\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));

//Inject the execution directory entry. The following is the default value. Initclass cannot change the entry through injection
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.modelBaseNameSpace',"App\\Model");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.controllerBaseNameSpace',"App\\HttpController");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.unitTestBaseNameSpace',"UnitTest");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.rootPath',getcwd());

```
Can be generated using the command.  
```bash
php ./bin/code-generator 
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

php ./bin/code-generator all tableName modelPath [controllerPath] [unitTestPath]
php ./bin/code-generator init

php ./bin/code-generator all user_list \\User \\Api\\\User \\User

```


## Independent use
### Generator process description
- adopt `\EasySwoole\ORM\Utility\TableObjectGeneration`,afferent `\EasySwoole\ORM\Db\Connection`Connecting objects through`generationTable`Method to get the table structure object
- Instantiate class generator configuration, configure namespace, generate file path, class name, etc. (see below for details).
- Instantiate the generator object and call the 'generate' method to generate.

### Generator base configuration item
- extendClass Inherited class, default is`\EasySwoole\ORM\AbstractModel::class`
- directory Build path, the default is `rootPath+namespace` corresponding path ,namespace path will pass automatically`composer.json->(autoload/autoload-dev)['psr-4']` configure directory generation. If not, it defaults to the root directory
- namespace Namespace configuration.
- className Class name
- rootPath Project root directory, default to execution directory.

### Get data table structure
All generators depend on data table structure objects `EasySwoole\ORM\Utility\Schema\Table`
```php
<?php
$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//Get connection
$connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
$tableName = 'user_list';
//Get data table structure object
$tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
$schemaInfo = $tableObjectGeneration->generationTable();

```

### Model generation
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
\EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();

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
//注入GetList方法
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
\EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();

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
\EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();

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
