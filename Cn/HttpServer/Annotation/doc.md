---
title: easyswoole注解控制器 - 注解自动生成文档
meta:
  - name: description
    content: easyswoole注解控制器 - 注解自动生成文档
  - name: keywords
    content:  easyswoole注解控制器 - 注解自动生成文档
---
# 注解文档

`Easyswoole`允许通过注解控制器的注解标签，生成文档。

## 控制器输出文档
```
namespace App\HttpController;


use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\Utility\AnnotationDoc;

class Index extends AnnotationController
{
    function index()
    {
        $doc = new AnnotationDoc();
        $string = $doc->scan2Html(EASYSWOOLE_ROOT.'/App/HttpController');
        $this->response()->withAddedHeader('Content-type',"text/html;charset=utf-8");
        $this->response()->write($string);
    }
}
```
例如在以上的代码中，我们就是直接扫描`Easyswoole`默认的控制器目录下的全部注解并输出对应文档，用户可以自己去做文档权限控制，或者是对应的目录限制。

## 生成离线文档

在项目根目录下执行如下命令：
```
php easyswoole doc --dir=App/HttpController

// 或者执行如下命令
php vendor/bin/annotation-doc --dir=App/HttpController
```
即可生成对应的离线文档。具体使用可查看 [基础命令章节 - 生成 API 文档](/QuickStart/command.html#生成%20API%20文档)

> 注意，仅当有@Api标记的控制器方法才会被渲染到文档中。

## 注意事项

有的同学在注解`@ApiSuccess`中,写入了`plainText`和`jsonArray`,会导致注解失败.有以下注意事项:

执行`composer info`需保证
`easyswoole/annotation`版本`>=2.0.3`

`plaintText`用法为：

`MyAnnotation(myProperty=r"{"code":200}")`
也就是格式为
`r"{RAW}"`
字母r+双引号。

`json Array`用法为：

`@PropertyTag(input={"code":2,"result":[{"name":1}]})`
可以直接解析为完整的数组。

## 注解示例1
```
namespace App\HttpController;


use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * Class Api
 * @package App\HttpController
 * @ApiGroup(groupName="Api")
 * @ApiGroupDescription("这是我的API全局描述")
 * @ApiGroupAuth(name="userToken",from={POST},required="",description="用户登录后，服务端返回的token,用于API鉴权")
 */
class UserApi extends AnnotationController
{
    /**
     * @Api(name="update",path="/userApi/update")
     * @ApiDescription("更新用户基础资料")
     * @ApiRequestExample("curl http://127.0.0.1:9501/userApi/update?name=es&age=11")
     * @Param(name="userId",description="用户id")
     * @Param(name="account",description="用户account")
     * @ApiSuccess({"code":200,"result":{"userId":2,"account":"zyf","username":"es","phone":"xxxx","avatar":null,"createTime":1595837009,"isDelete":null,"deleteTime":null,"user_token":"2-bc429ab40a7a2ebc-1596008468"},"msg":"登录成功"})
     * @ApiFail({"code":400,"result":null,"msg":"字段非法"})
     */
    function update()
    {

    }
}
```
        
## 注解示例2
```

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFailParam;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup as ApiGroupTag;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

/**
 * Class ControllerA
 * @package EasySwoole\HttpAnnotation\Tests\TestController
 * @ApiGroupTag(groupName="GroupA")
 * @ApiGroupDescription("GroupA desc")
 * @ApiGroupAuth(name="groupParamA",required="")
 * @ApiGroupAuth(name="groupParamB",required="")
 */
class Annotation extends AnnotationController
{

    /**
     * @Di(key="di")
     */
    public $di;
    /**
     * @Context(key="context")
     */
    public $context;

    /**
     * @Api(path="/apiGroup/func",name="func")
     * @ApiAuth(name="apiAuth1")
     * @ApiAuth(name="apiAuth2")
     * @ApiDescription("func desc")
     * @ApiFail("func fail example1")
     * @ApiFail("func fail example2")
     * @ApiFailParam(name="failParam1")
     * @ApiFailParam(name="failParam2")
     * @ApiRequestExample("func request example1")
     * @ApiRequestExample("func request example2")
     * @ApiSuccess("func success example1")
     * @ApiSuccess("func success example2")
     * @ApiSuccessParam(name="successParam1")
     * @ApiSuccessParam(name="successParam2")
     * @CircuitBreaker(timeout=5.0)
     * @InjectParamsContext(key="requestData")
     * @Method(allow={POST,GET})
     * @Param(name="param1")
     * @Param(name="param2")
     */
    function func()
    {

    }
}
```

