---
title: easyswoole trigger
meta:
  - name: description
    content: easyswoole trigger
  - name: keywords
    content: easyswoole trigger
---
# Trigger

`\EasySwoole\EasySwoole\Trigger`trigger,Used to proactively trigger errors or exceptions without interrupting program execution. 

For example, in the onexception of the controller, we can record the error exception, and then output other contents to prevent the system terminal from running and the user from discovering the real error.
````php
use EasySwoole\EasySwoole\Trigger;
//Record the error exception log with the level of exception
Trigger::getInstance()->throwable($throwable);
//Record the error information with the level of fatalerror
Trigger::getInstance()->error($throwable->getMessage().'666');

Trigger::getInstance()->onError()->set('myHook',function (){
    //Add callback function when error occurs
});
Trigger::getInstance()->onException()->set('myHook',function (){
    
});
````

# Online real-time warning
In some important online services, we hope that when there is an error, it can be warned and handled in real time. Let's take SMS or email notification as an example.

## Early warning processing class
```
<?php


namespace App\Utility;


use App\Model\EventNotifyModel;
use App\Model\EventNotifyPhoneModel;
use App\Utility\Sms\Sms;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Table;

class EventNotify
{
    use Singleton;

    private $evenTable;

    function __construct()
    {
        $this->evenTable = new Table(2048);
        $this->evenTable->column('expire',Table::TYPE_INT,8);
        $this->evenTable->column('count',Table::TYPE_INT,8);
        $this->evenTable->create();
    }

    function notifyException(\Throwable $throwable)
    {
        $class = get_class($throwable);
        //Exception in root directory, with msg as key
        if($class == 'Exception'){
            $key = substr(md5($throwable->getMessage()),8,16);
        }else{
            $key = substr(md5($class),8,16);
        }
        $this->onNotify($key,$throwable->getMessage());
    }

    function notify(string $msg)
    {
        $key = md5($msg);
        $this->onNotify($key,$msg);
    }

    private function onNotify(string $key,string $msg)
    {
        $info = $this->evenTable->get($key);
        //The same message will not be recorded in ten minutes
        $this->evenTable->set($key,[
            "expire"=>time() + 10 * 60
        ]);
        if(!empty($info)){
            return;
        }
        try{
            EventNotifyPhoneModel::create()->chunk(function (EventNotifyPhoneModel $model)use($msg){
                Sms::send($model->phone,$msg);
            });
            EventNotifyModel::create([
                'msg'=>$msg,
                'time'=>time()
            ])->save();
        }catch (\Throwable $throwable){
            //Avoid dead circulation
            Trigger::getInstance()->error($throwable->getMessage());
        }
    }
}
```
Note that this is incomplete code. The following three classes need to be implemented by themselves
```
use App\Model\EventNotifyModel;
use App\Model\EventNotifyPhoneModel;
use App\Utility\Sms\Sms;
```
target:
- Record the mobile number of the notification
- Text messaging
- Abnormal information record


## Callback takeover registration

Register in the event `mainServerCreate` of easyswoole Global
```
<?php

class EasySwooleEvent implements Event
{
    public static function mainServerCreate(EventRegister $register)
    {
        //Instantiate exception notifier in advance and register callback
        EventNotify::getInstance();
        Trigger::getInstance()->onException()->set('notify',function (\Throwable $throwable){
            EventNotify::getInstance()->notifyException($throwable);
        });

        Trigger::getInstance()->onError()->set('notify',function ($msg){
            EventNotify::getInstance()->notify($msg);
        });
    }
}
```

## Effect
Subsequently, trigger will be triggered in any business logic where trigger is called. The effect is as follows：
![](/Images/trigger_sms.jpg)

> The easyswoole HTTP request callback has automatically caught exceptions, and unhandled exceptions will be caught by trigger.
