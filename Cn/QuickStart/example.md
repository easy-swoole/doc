---
title: easyswoole基础开发示例
meta:
  - name: description
    content: easyswoole基础开发示例
  - name: keywords
    content: easyswoole基础开发示例
---

# 基础开始示例

## demo 地址

基础开发示例已经开源，源码地址：https://github.com/easy-swoole/demo/tree/3.x

## 安装

### 框架安装

- 我们先把当前的 `php` 环境安装好 `swoole` 拓展，安装 `swoole 扩展` 步骤可查看 [安装 Swoole](/QuickStart/installSwoole.md) 章节，然后执行 `php --ri swoole` 确保可以看到 `swoole` 拓展版本为 `4.4.23`
- 建立一个目录，名为 `Test` ，执行 `composer require easyswoole/easyswoole=3.4.x` 引入 `easyswoole`
- 执行 `php vendor/bin/easyswoole install` 进行安装，然后输入 `Y`、`Y`

### 组件引入

```bash
// 引入 IDE 代码提示组件
composer require easyswoole/swoole-ide-helper

// 引入 http-annotation 注解处理组件
composer require easyswoole/http-annotation 
```

### 命名空间注册

编辑 `Test` 根目录下的 `composer.json` 文件，如果自动加载中没有 `App` 命名空间，请在 `autoload.psr-4` 中加入 `"App\\": "App/"`，然后执行 `composer dumpautoload -o` 进行名称空间的更新。`composer.json` 文件大体结构如下：

```json
{
    "require": {
        "easyswoole/easyswoole": "3.x",
        "easyswoole/swoole-ide-helper": "^1.3",
        "easyswoole/http-annotation": "^2.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "App/"
        }
    }
}
```

### 安装后目录结构

```
Test                    项目部署目录
├─App                     应用目录
│  ├─HttpController      控制器目录(如果没有，请自行创建)
├─Log                     日志文件目录（启动后创建）
├─Temp                    临时文件目录（启动后创建）
├─vendor                  第三方类库目录
├─bootstrap.php           框架 bootstrap 事件
├─composer.json           Composer 架构
├─composer.lock           Composer 锁定
├─EasySwooleEvent.php     框架全局事件
├─easyswoole              框架管理脚本
├─dev.php                 开发配置文件
├─produce.php             生产配置文件
```

## 连接池实现

### 配置项

我们在 `dev.php` 配置文件中，加入以下配置信息，**注意：请根据自己的 `mysql` 服务器信息填写账户密码**。

```php
<?php

return [
    // ...... 这里省略
    'MAIN_SERVER' => [
        // ...... 这里省略
    ],
    // ...... 这里省略
    
    // 添加 MySQL 及对应的连接池配置
    /*################ MYSQL CONFIG ##################*/
    'MYSQL' => [
        'host'          => '127.0.0.1', // 数据库地址
        'port'          => 3306, // 数据库端口
        'user'          => 'root', // 数据库用户名
        'password'      => 'easyswoole', // 数据库用户密码
        'timeout'       => 45, // 数据库连接超时时间
        'charset'       => 'utf8', // 数据库字符编码
        'database'      => 'easyswoole_demo', // 数据库名
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => false, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 20, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
    ],
];
```

进行如上配置之后，我们需要在 `MySQL` 服务端创建一个名为 `easyswoole_demo` 的数据库，选择字符串编码为 `utf8`，字符排序规则为 `utf8_general_ci`，

### 引入数据库 ORM 库

执行以下命令用于实现数据库 ORM 库的引入。

```php
composer require easyswoole/orm=1.4.33
```

### 注册数据库连接池

编辑 `Test` 项目根目录下的 `EasySwooleEvent.php` 文件，在 `mainServerCreate` 事件函数中进行 `ORM` 的连接池的注册，内容如下：

```php
<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        ###### 注册 mysql orm 连接池 ######
        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
        // 【可选操作】我们已经在 dev.php 中进行了配置
        # $config->setMaxObjectNum(20); // 配置连接池最大数量
        DbManager::getInstance()->addConnection(new Connection($config));
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

::: warnging 
  在 `initialize` 事件中注册数据库连接池，使用这个 `$config` 可同时配置连接池大小等。
  具体查看 [ORM 组件章节](/Components/Orm/install.md) 的使用。
:::

## 模型定义

### 管理员模型

#### 新增管理员用户表

运行如下 `sql` 脚本，创建管理员用户表 `admin_list`。

```sql
DROP TABLE IF EXISTS `admin_list`;
CREATE TABLE `admin_list`  (
  `adminId` int(11) NOT NULL AUTO_INCREMENT,
  `adminName` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `adminAccount` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `adminPassword` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `adminSession` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `adminLastLoginTime` int(11) NULL DEFAULT NULL,
  `adminLastLoginIp` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`adminId`) USING BTREE,
  UNIQUE INDEX `adminAccount`(`adminAccount`) USING BTREE,
  INDEX `adminSession`(`adminSession`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci;

INSERT INTO `admin_list` VALUES (1, '仙士可', 'xsk', 'e10adc3949ba59abbe56e057f20f883e', '', 1566279458, '192.168.159.1');
```

#### 新增 model 文件

新建 `App/Model/Admin/AdminModel.php` 文件，编辑内容如下：

```php
<?php

namespace App\Model\Admin;

use EasySwoole\ORM\AbstractModel;

/**
 * Class AdminModel
 * @property $adminId
 * @property $adminName
 * @property $adminAccount
 * @property $adminPassword
 * @property $adminSession
 * @property $adminLastLoginTime
 * @property $adminLastLoginIp
 */
class AdminModel extends AbstractModel
{
    protected $tableName = 'admin_list';

    protected $primaryKey = 'adminId';

    /**
     * @getAll
     * @keyword adminName
     * @param  int  page  1
     * @param  string  keyword
     * @param  int  pageSize  10
     * @return array[total,list]
     */
    public function getAll(int $page = 1, string $keyword = null, int $pageSize = 10): array
    {
        $where = [];
        if (!empty($keyword)) {
            $where['adminAccount'] = ['%' . $keyword . '%', 'like'];
        }
        $list = $this->limit($pageSize * ($page - 1), $pageSize)->order($this->primaryKey, 'DESC')->withTotalCount()->all($where);
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }

    /*
     * 登录成功后请返回更新后的bean
     */
    public function login(): ?AdminModel
    {
        $info = $this->get(['adminAccount' => $this->adminAccount, 'adminPassword' => $this->adminPassword]);
        return $info;
    }

    /*
     * 以account进行查询
     */
    public function accountExist($field = '*'): ?AdminModel
    {
        $info = $this->field($field)->get(['adminAccount' => $this->adminAccount]);
        return $info;
    }

    public function getOneBySession($field = '*'): ?AdminModel
    {
        $info = $this->field($field)->get(['adminSession' => $this->adminSession]);
        return $info;
    }

    public function logout()
    {
        return $this->update(['adminSession' => '']);
    }
}
```

针对上述类似 `: ?AdminModel`，不懂这种函数返回值类型声明的同学，请查看 [函数返回值类型声明](https://www.php.net/manual/zh/migration70.new-features.php)，属于 `PHP 7` 的新特性。

::: warning
  关于 `model` 的定义可查看 [ORM 模型定义章节](/Components/Orm/definitionModel.md)。
:::

::: warning
  关于 `IDE` 自动提示，只要你在类上面注释中加上 `@property $adminId`，`IDE` 就可以自动提示类的这个属性。
:::


### 普通用户模型

普通用户模型和管理员模型同理。

#### 建表

运行如下 `sql` 脚本，创建普通用户表 `user_list`。

```sql
DROP TABLE IF EXISTS `user_list`;
CREATE TABLE `user_list`  (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `userAccount` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `userPassword` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `phone` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `addTime` int(11) NULL DEFAULT NULL,
  `lastLoginIp` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `lastLoginTime` int(10) NULL DEFAULT NULL,
  `userSession` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `state` tinyint(2) NULL DEFAULT NULL,
  `money` int(10) NOT NULL DEFAULT 0 COMMENT '用户余额',
  `frozenMoney` int(10) NOT NULL DEFAULT 0 COMMENT '冻结余额',
  PRIMARY KEY (`userId`) USING BTREE,
  UNIQUE INDEX `pk_userAccount`(`userAccount`) USING BTREE,
  INDEX `userSession`(`userSession`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci;

INSERT INTO `user_list` VALUES (1, '仙士可', 'xsk', 'e10adc3949ba59abbe56e057f20f883e', '', 1566279458, '192.168.159.1', 1566279458, '', 1, 1, 1);
```

#### 新增 model 文件

新建 `App/Model/User/UserModel.php` 文件，编辑内容如下：

```php
<?php

namespace App\Model\User;

use EasySwoole\ORM\AbstractModel;

/**
 * Class UserModel
 * @property $userId
 * @property $userName
 * @property $userAccount
 * @property $userPassword
 * @property $phone
 * @property $money
 * @property $addTime
 * @property $lastLoginIp
 * @property $lastLoginTime
 * @property $userSession
 * @property $state
 */
class UserModel extends AbstractModel
{
    protected $tableName = 'user_list';

    protected $primaryKey = 'userId';

    const STATE_PROHIBIT = 0; // 禁用状态
    const STATE_NORMAL = 1; // 正常状态

    /**
     * @getAll
     * @keyword userName
     * @param  int  page  1
     * @param  string  keyword
     * @param  int  pageSize  10
     * @return array[total,list]
     */
    public function getAll(int $page = 1, string $keyword = null, int $pageSize = 10): array
    {
        $where = [];
        if (!empty($keyword)) {
            $where['userAccount'] = ['%' . $keyword . '%', 'like'];
        }
        $list = $this->limit($pageSize * ($page - 1), $pageSize)->order($this->primaryKey, 'DESC')->withTotalCount()->all($where);
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }

    public function getOneByPhone($field = '*'): ?UserModel
    {
        $info = $this->field($field)->get(['phone' => $this->phone]);
        return $info;
    }

    /*
     * 登录成功后请返回更新后的bean
     */
    public function login(): ?UserModel
    {
        $info = $this->get(['userAccount' => $this->userAccount, 'userPassword' => $this->userPassword]);
        return $info;
    }

    public function getOneBySession($field = '*'): ?UserModel
    {
        $info = $this->field($field)->get(['userSession' => $this->userSession]);
        return $info;
    }

    public function logout()
    {
        return $this->update(['userSession' => '']);
    }
}
```

### banner 模型

#### 建表

运行如下 `sql` 脚本，创建 `banner` 表 `banner_list`。

```sql
DROP TABLE IF EXISTS `banner_list`;
CREATE TABLE `banner_list`  (
  `bannerId` int(11) NOT NULL AUTO_INCREMENT,
  `bannerName` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `bannerImg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'banner图片',
  `bannerDescription` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `bannerUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '跳转地址',
  `state` tinyint(3) NULL DEFAULT NULL COMMENT '状态0隐藏 1正常',
  PRIMARY KEY (`bannerId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci;

INSERT INTO `banner_list` VALUES (1, '测试banner', 'asdadsasdasd.jpg', '测试的banner数据', 'www.php20.cn', 1);
```

#### 新增model文件

新建 `App/Model/Admin/BannerModel.php` 文件，编辑内容如下：

```php
<?php

namespace App\Model\Admin;

use EasySwoole\ORM\AbstractModel;

/**
 * Class BannerModel
 * @property $bannerId
 * @property $bannerImg
 * @property $bannerUrl
 * @property $state
 */
class BannerModel extends AbstractModel
{
    protected $tableName = 'banner_list';

    protected $primaryKey = 'bannerId';

    public function getAll(int $page = 1, int $state = 1, string $keyword = null, int $pageSize = 10): array
    {
        $where = [];
        if (!empty($keyword)) {
            $where['bannerUrl'] = ['%' . $keyword . '%', 'like'];
        }
        $where['state'] = $state;
        $list = $this->limit($pageSize * ($page - 1), $pageSize)->order($this->primaryKey, 'DESC')->withTotalCount()->all($where);
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }
}
```

## 控制器定义

### 全局基础控制器定义

新建 `App/Httpcontroller/BaseController.php` 文件，编辑内容如下：

```php
<?php

namespace App\HttpController;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\HttpAnnotation\AnnotationController;

class BaseController extends AnnotationController
{
    public function index()
    {
        $this->actionNotFound('index');
    }

    /**
     * 获取用户的真实IP
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP($headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1') {
            if (!empty($xri)) {  // 如果有 xri 则判定为前端有 NGINX 等代理
                $clientAddress = $xri[0];
            } elseif (!empty($xff)) {  // 如果不存在 xri 则继续判断 xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) $clientAddress = $list[0];
            }
        }
        return $clientAddress;
    }

    protected function input($name, $default = null)
    {
        $value = $this->request()->getRequestParam($name);
        return $value ?? $default;
    }
}
```

::: warning
  上述新增的基础控制器 (BaseController.php) 里面的方法用于获取用户 `ip`，以及获取 `api` 参数。
:::

::: warning
  上述新增的基础控制器 (BaseController.php) 继承了 `\EasySwoole\Http\AbstractInterface\AnnotationController` ，这个是注解支持控制器，具体使用可查看 [注解章节](/HttpServer/Annotation/install.md)
:::

### api 基础控制器定义

新建 `App/Httpcontroller/Api/ApiBase.php` 文件，编辑内容如下：

```php
<?php

namespace App\HttpController\Api;

use App\HttpController\BaseController;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

abstract class ApiBase extends BaseController
{
    public function index()
    {
        // TODO: Implement index() method.
        $this->actionNotFound('index');
    }

    protected function actionNotFound(?string $action): void
    {
        $this->writeJson(Status::CODE_NOT_FOUND);
    }

    public function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }
        return true;
    }

    protected function onException(\Throwable $throwable): void
    {
        if ($throwable instanceof ParamValidateError) {
            $msg = $throwable->getValidate()->getError()->getErrorRuleMsg();
            $this->writeJson(400, null, "{$msg}");
        } else {
            if (Core::getInstance()->runMode() == 'dev') {
                $this->writeJson(500, null, $throwable->getMessage());
            } else {
                Trigger::getInstance()->throwable($throwable);
                $this->writeJson(500, null, '系统内部错误，请稍后重试');
            }
        }
    }
}
```

::: warning
  上述 `api` 基类控制器 (ApiBase.php)，用于拦截注解异常，以及 `api` 异常时给用户返回一个 `json` 格式错误信息。
:::

### 公共基础控制器定义

新建 `App/Httpcontroller/Api/Common/CommonBase.php` 文件，编辑内容如下：

```php
<?php

namespace App\HttpController\Api\Common;
 
use App\HttpController\Api\ApiBase;

class CommonBase extends ApiBase
{
 
}
```

### 公共控制器

公共控制器放不需要登陆即可查看的控制器，例如 `banner` 列表查看：

新增 `App/HttpController/Api/Common/Banner.php` 文件，编辑内容如下：

```php
<?php

namespace App\HttpController\Api\Common;

use App\Model\Admin\BannerModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * Class Banner
 */
class Banner extends CommonBase
{

    /**
     * getOne
     * @Param(name="bannerId", alias="主键id", required="", integer="")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 14:03
     */
    public function getOne()
    {
        $param = $this->request()->getRequestParam();
        $model = new BannerModel();
        $bean = $model->get($param['bannerId']);
        if ($bean) {
            $this->writeJson(Status::CODE_OK, $bean, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], 'fail');
        }
    }

    /**
     * getAll
     * @Param(name="page", alias="页数", optional="", integer="")
     * @Param(name="limit", alias="每页总数", optional="", integer="")
     * @Param(name="keyword", alias="关键字", optional="", lengthMax="32")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @author Tioncico
     * Time: 14:02
     */
    public function getAll()
    {
        $param = $this->request()->getRequestParam();
        $page = $param['page'] ?? 1;
        $limit = $param['limit'] ?? 20;
        $model = new BannerModel();
        $data = $model->getAll($page, 1, $param['keyword'] ?? null, $limit);
        $this->writeJson(Status::CODE_OK, $data, 'success');
    }
}
```

::: warning
  可以看到，在 `getAll` 方法中，有着 `@Param(name="page", alias="页数", optional="", integer="")` 的注释，这个是注解支持写法，可以这样写也不可以不写，当写上这个注释之后，将会约束`page` 参数必须是 `int`，具体的验证机制可查看 [`validate` 验证器 章节](/Components/validate.md)
:::

::: warning
  使用 `php easyswoole server start` 命令启动框架服务之后，访问链接：`http://127.0.0.1:9501/api/common/banner/getAll` (示例访问地址) 即可看到如下结果：`{"code":200,"result":{"total":1,"list":[{"bannerId":1,"bannerName":"测试banner","bannerImg":"asdadsasdasd.jpg","bannerDescription":"测试的banner数据","bannerUrl":"www.php20.cn","state":1}]},"msg":"success"}` (需要有数据才能看到具体输出)。
:::

### 管理员基础控制器定义

新建 `App/HttpController/Api/Admin/AdminBase.php` 文件，编辑内容如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 5:39 PM
 */

namespace App\HttpController\Api\Admin;

use App\HttpController\Api\ApiBase;
use App\Model\Admin\AdminModel;
use EasySwoole\Http\Message\Status;

class AdminBase extends ApiBase
{
    // public 才会根据协程清除
    public $who;
    // session 的 cookie头
    protected $sessionKey = 'adminSession';
    // 白名单
    protected $whiteList = [];

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws \Throwable
     * @author yangzhenyu
     * Time: 13:49
     */
    public function onRequest(?string $action): ?bool
    {
        if (parent::onRequest($action)) {
            // 白名单判断
            if (in_array($action, $this->whiteList)) {
                return true;
            }
            // 获取登入信息
            if (!$this->getWho()) {
                $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入已过期');
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * getWho
     * @return null|AdminModel
     * @author yangzhenyu
     * Time: 13:51
     */
    public function getWho(): ?AdminModel
    {
        if ($this->who instanceof AdminModel) {
            return $this->who;
        }
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) {
            $sessionKey = $this->request()->getCookieParams($this->sessionKey);
        }
        if (empty($sessionKey)) {
            return null;
        }
        $adminModel = new AdminModel();
        $adminModel->adminSession = $sessionKey;
        $this->who = $adminModel->getOneBySession();
        return $this->who;
    }
}
```

### 管理员登录控制器

新建 `App/HttpController/Api/Admin/Auth.php` 文件，编辑内容如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 5:39 PM
 */

namespace App\HttpController\Api\Admin;

use App\Model\Admin\AdminModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Auth extends AdminBase
{
    protected $whiteList = ['login'];

    /**
     * login
     * 登陆,参数验证注解写法
     * @\EasySwoole\HttpAnnotation\AnnotationTag\Param(name="account", alias="帐号", required="", lengthMax="20")
     * @Param(name="password", alias="密码", required="", lengthMin="6", lengthMax="16")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 10:18
     */
    public function login()
    {
        $param = $this->request()->getRequestParam();
        $model = new AdminModel();
        $model->adminAccount = $param['account'];
        $model->adminPassword = md5($param['password']);

        if ($user = $model->login()) {
            $sessionHash = md5(time() . $user->adminId);
            $user->update([
                'adminLastLoginTime' => time(),
                'adminLastLoginIp'   => $this->clientRealIP(),
                'adminSession'       => $sessionHash
            ]);

            $rs = $user->toArray();
            unset($rs['adminPassword']);
            $rs['adminSession'] = $sessionHash;
            $this->response()->setCookie('adminSession', $sessionHash, time() + 3600, '/');
            $this->writeJson(Status::CODE_OK, $rs);
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, '', '密码错误');
        }
    }

    /**
     * logout
     * 退出登录,参数注解写法
     * @Param(name="adminSession", from={COOKIE}, required="")
     * @return bool
     * @author Tioncico
     * Time: 10:23
     */
    public function logout()
    {
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) {
            $sessionKey = $this->request()->getCookieParams('adminSession');
        }
        if (empty($sessionKey)) {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', '尚未登入');
            return false;
        }
        $result = $this->getWho()->logout();
        if ($result) {
            $this->writeJson(Status::CODE_OK, '', "登出成功");
        } else {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', 'fail');
        }
    }

    public function getInfo()
    {
        $this->writeJson(200, $this->getWho()->toArray(), 'success');
    }
}
```

::: warning
  使用 `php easyswoole server start` 命令启动框架服务之后，访问链接：`http://127.0.0.1:9501/Api/Admin/Auth/login?account=xsk&password=123456` (示例访问地址) 即可返回如下结果：``
:::

```json
{
  "code": 200,
  "result": {
    "adminId": 1,
    "adminName": "仙士可",
    "adminAccount": "xsk",
    "adminSession": "b27caf58312d5d4ffc9de42ebf322135",
    "adminLastLoginTime": 1615653249,
    "adminLastLoginIp": "192.168.65.1"
  },
  "msg": null
}
```

### 管理员用户管理控制器

新增 `App/httpController/Api/Admin/User.php` 文件，编辑内容如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 5:39 PM
 */

namespace App\HttpController\Api\Admin;

use App\Model\User\UserModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class User extends AdminBase
{
    /**
     * getAll
     * @Param(name="page", alias="页数", optional="", integer="")
     * @Param(name="limit", alias="每页总数", optional="", integer="")
     * @Param(name="keyword", alias="关键字", optional="", lengthMax="32")
     * @author Tioncico
     * Time: 14:01
     */
    function getAll()
    {
        $page = (int)$this->input('page', 1);
        $limit = (int)$this->input('limit', 20);
        $model = new UserModel();
        $data = $model->getAll($page, $this->input('keyword'), $limit);
        $this->writeJson(Status::CODE_OK, $data, 'success');
    }

    /**
     * getOne
     * @Param(name="userId", alias="用户id", required="", integer="")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 11:48
     */
    function getOne()
    {
        $param = $this->request()->getRequestParam();
        $model = new UserModel();
        $rs = $model->get($param['userId']);
        if ($rs) {
            $this->writeJson(Status::CODE_OK, $rs, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], 'fail');
        }
    }

    /**
     * add
     * @Param(name="userName", alias="用户昵称", optional="", lengthMax="32")
     * @Param(name="userAccount", alias="用户名", required="", lengthMax="32")
     * @Param(name="userPassword", alias="用户密码", required="", lengthMin="6",lengthMax="18")
     * @Param(name="phone", alias="手机号码", optional="", lengthMax="18",numeric="")
     * @Param(name="state", alias="用户状态", optional="", inArray="{0,1}")
     * @author Tioncico
     * Time: 11:48
     */
    function add()
    {
        $param = $this->request()->getRequestParam();
        $model = new UserModel($param);
        $model->userPassword = md5($param['userPassword']);
        $rs = $model->save();
        if ($rs) {
            $this->writeJson(Status::CODE_OK, $rs, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], $model->lastQueryResult()->getLastError());
        }
    }

    /**
     * update
     * @Param(name="userId", alias="用户id", required="", integer="")
     * @Param(name="userPassword", alias="会员密码", optional="", lengthMin="6",lengthMax="18")
     * @Param(name="userName", alias="会员名", optional="",  lengthMax="32")
     * @Param(name="state", alias="状态", optional="", inArray="{0,1}")
     * @Param(name="phone", alias="手机号", optional="",  lengthMax="18")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 11:54
     */
    function update()
    {
        $model = new UserModel();
        /**
         * @var $userInfo UserModel
         */
        $userInfo = $model->get($this->input('userId'));
        if (!$userInfo) {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], '未找到该会员');
        }
        $password = $this->input('userPassword');
        $update = [
            'userName' => $this->input('userName', $userInfo->userName),
            'userPassword' => $password ? md5($password) : $userInfo->userPassword,
            'state' => $this->input('state', $userInfo->state),
            'phone' => $this->input('phone', $userInfo->phone),
        ];

        $rs = $model->update($update);
        if ($rs) {
            $this->writeJson(Status::CODE_OK, $rs, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], $model->lastQueryResult()->getLastError());
        }
    }

    /**
     * delete
     * @Param(name="userId", alias="用户id", required="", integer="")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 14:02
     */
    function delete()
    {
        $param = $this->request()->getRequestParam();
        $model = new UserModel();
        $rs = $model->destroy($param['userId']);
        if ($rs) {
            $this->writeJson(Status::CODE_OK, $rs, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], '删除失败');
        }
    }
}
```

::: warnging
  后台管理员登录之后，可通过此文件的接口，去进行会员的增删改查操作 (即 CURD)。
:::

::: warning
  请求地址为：(示例访问地址) `http://127.0.0.1:9501/Api/Admin/User/getAll` (等方法)
:::

### 普通用户基础控制器定义

新增 `App/HttpController/Api/User/UserBase.php` 文件，编辑内容如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 5:39 PM
 */

namespace App\HttpController\Api\User;

use App\HttpController\Api\ApiBase;
use App\Model\User\UserModel;
use EasySwoole\Http\Message\Status;

class UserBase extends ApiBase
{
    protected $who;
    // session 的 cookie 头
    protected $sessionKey = 'userSession';
    // 白名单
    protected $whiteList = ['login', 'register'];

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws \Throwable
     * @author yangzhenyu
     * Time: 13:49
     */
    function onRequest(?string $action): ?bool
    {
        if (parent::onRequest($action)) {
            // 白名单判断
            if (in_array($action, $this->whiteList)) {
                return true;
            }
            // 获取登入信息
            if (!$data = $this->getWho()) {
                $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入已过期');
                return false;
            }
            // 刷新 cookie 存活
            $this->response()->setCookie($this->sessionKey, $data->userSession, time() + 3600, '/');

            return true;
        }
        return false;
    }

    /**
     * getWho
     * @author yangzhenyu
     * Time: 13:51
     */
    function getWho(): ?UserModel
    {
        if ($this->who instanceof UserModel) {
            return $this->who;
        }
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) {
            $sessionKey = $this->request()->getCookieParams($this->sessionKey);
        }
        if (empty($sessionKey)) {
            return null;
        }
        $userModel = new UserModel();
        $userModel->userSession = $sessionKey;
        $this->who = $userModel->getOneBySession();
        return $this->who;
    }
}
```

### 普通用户登录控制器

新增 `App/HttpController/Api/User/Auth.php` 文件，编辑内容如下：

```php
<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 5:39 PM
 */

namespace App\HttpController\Api\Admin;

use App\Model\Admin\AdminModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Auth extends AdminBase
{
    protected $whiteList = ['login'];

    /**
     * login
     * 登陆,参数验证注解写法
     * @\EasySwoole\HttpAnnotation\AnnotationTag\Param(name="account", alias="帐号", required="", lengthMax="20")
     * @Param(name="password", alias="密码", required="", lengthMin="6", lengthMax="16")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 10:18
     */
    public function login()
    {
        $param = $this->request()->getRequestParam();
        $model = new AdminModel();
        $model->adminAccount = $param['account'];
        $model->adminPassword = md5($param['password']);

        if ($user = $model->login()) {
            $sessionHash = md5(time() . $user->adminId);
            $user->update([
                'adminLastLoginTime' => time(),
                'adminLastLoginIp' => $this->clientRealIP(),
                'adminSession' => $sessionHash
            ]);

            $rs = $user->toArray();
            unset($rs['adminPassword']);
            $rs['adminSession'] = $sessionHash;
            $this->response()->setCookie('adminSession', $sessionHash, time() + 3600, '/');
            $this->writeJson(Status::CODE_OK, $rs);
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, '', '密码错误');
        }
    }

    /**
     * logout
     * 退出登录,参数注解写法
     * @Param(name="adminSession", from={COOKIE}, required="")
     * @return bool
     * @author Tioncico
     * Time: 10:23
     */
    public function logout()
    {
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) {
            $sessionKey = $this->request()->getCookieParams('adminSession');
        }
        if (empty($sessionKey)) {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', '尚未登入');
            return false;
        }
        $result = $this->getWho()->logout();
        if ($result) {
            $this->writeJson(Status::CODE_OK, '', "登出成功");
        } else {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', 'fail');
        }
    }

    public function getInfo()
    {
        $this->writeJson(200, $this->getWho()->toArray(), 'success');
    }
}
```

访问 `http://127.0.0.1:9501/Api/User/Auth/login?userAccount=xsk&userPassword=123456` 即可登录成功。

::: tip
  管理员登录：(示例访问地址) `http://127.0.0.1:9501/Api/Admin/Auth/login?account=xsk&password=123456` 
  
  公共请求 banner：(示例访问地址) `http://127.0.0.1:9501/Api/Common/Banner/getAll`
  
  会员登录：(示例访问地址) `http://127.0.0.1:9501/Api/User/Auth/login?userAccount=xsk&userPassword=123456`
:::
