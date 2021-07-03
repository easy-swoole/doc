---
title: easyswoole链路追踪
meta:
  - name: description
    content: Easyswoole提供了一个基础的追踪组件，方便用户实现基础的服务器状态监控，与调用链记录。
  - name: keywords
    content: easyswoole链路追踪|swoole链路追踪
---

# Tracker

`EasySwoole` 提供了一个基础的追踪组件，方便用户实现基础的服务器状态监控，与调用链记录。

## 组件要求

- php: >=7.1.0
- ext-swoole: ^4.4.0
- easyswoole/component: ^2.0

## 安装方法

> composer require easyswoole/tracker

## 仓库地址

[easyswoole/tracker](https://github.com/easy-swoole/tracker)

## 调用链结构说明

`EasySwoole` 的调用链跟踪是一个以类似有序的树状链表的解构实现的，解构如下：

```c
struct Point{
    struct Point* nextPoint;
    struct Point[] subPoints;
    const END_SUCCESS = 'success';
    const END_FAIL = 'fail';
    const END_UNKNOWN = 'unknown';
    int startTime;
    mixed startArg;
    int endTime;
    string pointName;
    string endStatus = self::END_UNKNOWN;
    mixed endArg;
    string pointId;
    string parentId;
    int depth = 0;
    bool isNext
}
```

## 基本使用

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

require_once __DIR__ . '/vendor/autoload.php';

use EasySwoole\Tracker\Point;
use EasySwoole\Component\WaitGroup;
use EasySwoole\Tracker\PointContext;

/*
 * 假设我们的调用链是这样的
 * onRequest  ->> actionOne ->> actionOne call remote Api(1,2)  ->>  afterAction
 */

go(function (){
    /*
     * 创建入口
     */
    $onRequest = new Point('onRequest');
    //记录请求参数，并模拟access log
    \co::sleep(0.01);
    $onRequest->setStartArg([
        'requestArg' => 'requestArgxxxxxxxx',
        'accessLogId'=>'logIdxxxxxxxxxx'
    ]);
    //onRequest完成
    $onRequest->end();
    //进入 next actionOne
    $actionOne = $onRequest->next('actionOne');
        //action one 进入子环节调用
        $waitGroup = new WaitGroup();
        //sub pointOne
        $waitGroup->add();
        $subOne = $actionOne->appendChild('subOne');
        go(function ()use($subOne,$waitGroup){
                \co::sleep(0.1);
                $subOne->end();
                $waitGroup->done();
        });
        //sub pointTwo,并假设失败
        $waitGroup->add();
        $subTwo = $actionOne->appendChild('subTwo');
            go(function ()use($subTwo,$waitGroup){
                \co::sleep(1);
                $subTwo->end($subTwo::END_FAIL,['failMsg'=>'timeout']);
                $waitGroup->done();
            });
        $waitGroup->wait();
    $actionOne->end();
    //actionOne结束，进入afterAction
    $afterAction = $actionOne->next('afterAction');
    //模拟响应记录
    \co::sleep(0.01);
    $afterAction->end($afterAction::END_SUCCESS,['log'=>'success']);
    /*
     * 从入口开始打印调用链
     */
    echo Point::toString($onRequest);
});
// 以上代码等价于如下
go(function () {
    PointContext::getInstance()->createStart('onRequest')->next('actionOne')->next('afterAction');
    // 记录请求参数，并模拟access log
    \co::sleep(0.01);
    PointContext::getInstance()->find('onRequest')->setStartArg([
        'requestArg' => 'requestArgxxxxxxxx',
        'accessLogId' => 'logIdxxxxxxxxxx'
    ])->end();
    $subOne = PointContext::getInstance()->find('actionOne')->appendChild('subOne');
    $subTwo = PointContext::getInstance()->find('actionOne')->appendChild('subTwo');
    $waitGroup = new WaitGroup();
    $waitGroup->add();
    go(function () use ($subOne, $waitGroup) {
        \co::sleep(0.1);
        $subOne->end();
        $waitGroup->done();
    });
    // sub pointTwo，并假设失败
    $waitGroup->add();
    go(function () use ($subTwo, $waitGroup) {
        \co::sleep(1);
        $subTwo->end($subTwo::END_FAIL, ['failMsg' => 'timeout']);
        $waitGroup->done();
    });
    $waitGroup->wait();
    PointContext::getInstance()->find('actionOne')->end();
    // 模拟响应记录
    \co::sleep(0.01);
    PointContext::getInstance()->find('afterAction')->end(Point::END_SUCCESS, ['log' => 'success']);
    /*
     * 从入口开始打印调用链
     */
    echo Point::toString(PointContext::getInstance()->startPoint());
});
```

以上代码输出结果：

```bash
##
PointName:onRequest
ServiceName:default
Status:success
PointId:df56bbcf-c1ce-f536-ab8f-31f243721d76
ParentId:
Depth:0
IsNext:false
Start:1625313762.7221
StartArg:{"requestArg":"requestArgxxxxxxxx","accessLogId":"logIdxxxxxxxxxx"}
End:1625313762.7352
EndArg:null
ChildCount:0
Children:None
NextPoint:
	##
	PointName:actionOne
	ServiceName:default
	Status:success
	PointId:c341da3e-809c-5a6b-e8c6-ab8aba29e336
	ParentId:df56bbcf-c1ce-f536-ab8f-31f243721d76
	Depth:0
	IsNext:true
	Start:1625313762.7352
	StartArg:null
	End:1625313763.7381
	EndArg:null
	ChildCount:2
	Children:
		##
		PointName:subOne
		ServiceName:default
		Status:success
		PointId:4a66dc47-8c30-a4e4-bf8d-7b1fc334ce4b
		ParentId:c341da3e-809c-5a6b-e8c6-ab8aba29e336
		Depth:1
		IsNext:false
		Start:1625313762.7354
		StartArg:null
		End:1625313762.838
		EndArg:null
		ChildCount:0
		Children:None
		NextPoint:None
		##
		PointName:subTwo
		ServiceName:default
		Status:fail
		PointId:326ca214-155b-d9f9-ad7a-8d8cbd479cdf
		ParentId:c341da3e-809c-5a6b-e8c6-ab8aba29e336
		Depth:1
		IsNext:false
		Start:1625313762.7355
		StartArg:null
		End:1625313763.7381
		EndArg:{"failMsg":"timeout"}
		ChildCount:0
		Children:None
		NextPoint:None
	NextPoint:
		##
		PointName:afterAction
		ServiceName:default
		Status:success
		PointId:2f6d29b9-a100-fc69-2f64-f51a28a870eb
		ParentId:c341da3e-809c-5a6b-e8c6-ab8aba29e336
		Depth:0
		IsNext:true
		Start:1625313763.7382
		StartArg:null
		End:1625313763.7502
		EndArg:{"log":"success"}
		ChildCount:0
		Children:None
		NextPoint:None
```


::: warning 
 如果想以自己的格式记录到数据库，可以具体查看 `Point` 实现的方法，每个 `Point` 都有自己的 `Id`。
:::

## 进阶使用

### HTTP API 请求追踪

在 `EasySwoole` 全局事件（即项目根目录的 `EasySwooleEvent.php`）中注册 `Tracker`。

在 `EasySwoole 3.4.x` 中注册示例代码如下：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response): bool {
            $point = \EasySwoole\Tracker\PointContext::getInstance()->createStart('onRequest');
            $point->setStartArg([
                'uri' => $request->getUri()->__toString(),
                'get' => $request->getQueryParams()
            ]);
            return true;
        });

        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response): void {
            $point = \EasySwoole\Tracker\PointContext::getInstance()->startPoint();
            $point->end();
            echo \EasySwoole\Tracker\Point::toString($point);
            $array = \EasySwoole\Tracker\Point::toArray($point);
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}
```

如果 `EasySwoole` 框架版本低于 `3.4.x`时，请使用如下方式进行注册：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }

    public static function onRequest(Request $request, Response $response): bool
    {
        $point = \EasySwoole\Tracker\PointContext::getInstance()->createStart('onRequest');
        $point->setStartArg([
            'uri'=>$request->getUri()->__toString(),
            'get'=>$request->getQueryParams()
        ]);
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        $point = \EasySwoole\Tracker\PointContext::getInstance()->startPoint();
        $point->end();
        echo \EasySwoole\Tracker\Point::toString($point);
        $array = \EasySwoole\Tracker\Point::toArray($point);
    }
}
```

在 `App\HttpController\Index.php` 中：

```php
<?php
/**
 * This file is part of EasySwoole.
 *
 * @link https://www.easyswoole.com
 * @document https://www.easyswoole.com
 * @contact https://www.easyswoole.com/Preface/contact.html
 * @license https://github.com/easy-swoole/easyswoole/blob/3.x/LICENSE
 */

namespace App\HttpController;

use EasySwoole\Component\WaitGroup;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Tracker\PointContext;

class Index extends Controller
{
    protected function onRequest(?string $action): ?bool
    {
        /*
         * 调用关系  HttpRequest->OnRequest
         */
        $point = PointContext::getInstance()->next('ControllerOnRequest');
        // 假设这里进行了权限验证，并模拟数据库耗时
        \co::sleep(0.01);
        $point->setEndArg([
            'userId'=>'xxxxxxxxxxx'
        ]);
        $point->end();
        return true;
    }

    function index()
    {
        // 模拟调用第三方Api，调用关系  OnRequest->sub(subApi1,subApi2)
        $actionPoint = PointContext::getInstance()->next('indexAction');
        $wait = new WaitGroup();
        $subApi = $actionPoint->appendChild('subOne');
        $wait->add();
        go(function ()use($wait,$subApi){
            \co::sleep(1);
            $subApi->end();
            $wait->done();
        });

        $subApi = $actionPoint->appendChild('subTwo');
        $wait->add();
        go(function ()use($wait,$subApi){
            \co::sleep(0.3);
            $subApi->end($subApi::END_FAIL);
            $wait->done();
        });

        $wait->wait();

        $actionPoint->end();
        $this->response()->write('hello world');
    }
}
```

以上每次请求会输出如下格式：

```bash
##
PointName:onRequest
ServiceName:default
Status:success
PointId:2ea751d4-13a7-8a27-932e-6671da6d6586
ParentId:
Depth:0
IsNext:false
Start:1625315058.3513
StartArg:{"uri":"http://192.168.1.107:9501/","get":[]}
End:1625315059.3694
EndArg:null
ChildCount:0
Children:None
NextPoint:
	##
	PointName:ControllerOnRequest
	ServiceName:default
	Status:success
	PointId:13a0ccda-18ef-c90c-d9db-6e3a1cc70511
	ParentId:2ea751d4-13a7-8a27-932e-6671da6d6586
	Depth:0
	IsNext:true
	Start:1625315058.3535
	StartArg:null
	End:1625315058.3656
	EndArg:{"userId":"xxxxxxxxxxx"}
	ChildCount:0
	Children:None
	NextPoint:
		##
		PointName:indexAction
		ServiceName:default
		Status:success
		PointId:a0295b8f-c02c-7ef3-afae-da5dce2764d0
		ParentId:13a0ccda-18ef-c90c-d9db-6e3a1cc70511
		Depth:0
		IsNext:true
		Start:1625315058.3656
		StartArg:null
		End:1625315059.3694
		EndArg:null
		ChildCount:2
		Children:
			##
			PointName:subOne
			ServiceName:default
			Status:success
			PointId:d06855e1-0571-c829-121e-3467f7309598
			ParentId:a0295b8f-c02c-7ef3-afae-da5dce2764d0
			Depth:1
			IsNext:false
			Start:1625315058.3658
			StartArg:null
			End:1625315059.3694
			EndArg:null
			ChildCount:0
			Children:None
			NextPoint:None
			##
			PointName:subTwo
			ServiceName:default
			Status:fail
			PointId:b47b32d6-f96f-9a00-1244-e16faab3d790
			ParentId:a0295b8f-c02c-7ef3-afae-da5dce2764d0
			Depth:1
			IsNext:false
			Start:1625315058.3658
			StartArg:null
			End:1625315058.6685
			EndArg:null
			ChildCount:0
			Children:None
			NextPoint:None
		NextPoint:None
```

### Api 调用链记录

```
$array = \EasySwoole\Tracker\Point::toArray($point);
```

可以把一个入口点转为一个数组。例如我们可以在 `MYSQL` 数据库中存储以下关键结构：

```sql
CREATE TABLE `api_tracker_point_list` (
  `pointd` varchar(18) NOT NULL,
  `pointName` varchar(45) DEFAULT NULL,
  `parentId` varchar(18) DEFAULT NULL,
  `depth` int(11) NOT NULL DEFAULT '0',
  `isNext` int(11) NOT NULL DEFAULT '0',
  `startTime` varchar(14) NOT NULL,
  `endTime` varchar(14) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`pointd`),
  UNIQUE KEY `trackerId_UNIQUE` (`pointd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

::: warning 
 其余请求参数可以自己记录。
:::

核心字段在 `pointId`、`parentId`、`isNext`、`status` 这四个字段，例如，我想得到哪次调用链超时，那么就是直接

```
where status = fail
```

如果想看哪次调用耗时多少，那么可以

```
where spendTime > 3
```

::: warning 
 `spendTime` 是通过 `startTime` 和 `endTime` 计算得出
:::

## 相关知识链接

`EasySwoole` 之链路追踪 [简单demo](https://www.umdzz.cn/article/45/easyswoole)
