## Bridge
After version 3.3.5, easysoole added a new 'bridge' module. The 'bridge' module starts after the 'mainservercreate' event, and a user-defined process will be created. Listening to 'socket' is used to process external data interaction
### onStart
When the bridge process starts, OnStart event registration is provided:
```php
public static function mainServerCreate(EventRegister $register)
{
    Bridge::getInstance()->setOnStart(function () {
        echo "process id:" . getmypid();
    });
    // TODO: Implement mainServerCreate() method.
}
```
::: warning
This event can only be registered in the 'mainservercreate' event
:::

### Bridge data interaction
After your 'easyswoole' service has been started, when you need to obtain the internal running data of 'easyswoole', such as' custom process / task information ',' connection pool information ', the created' swoole table 'cannot be obtained directly outside. We can use the bridge to conduct' unixsock 'data interaction, send the corresponding commands and implement the interaction interface, You can obtain the 'easyswole' internal running data externally
For example, easyswoole config is configured as swoole table by default. We can dynamically obtain the configuration of easyswoole service and dynamic configuration through external commands:
#### Implement config interaction class
```php
<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/11 0011
 * Time: 10:35
 */

namespace App\Bridge;

use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\EasySwoole\Core;

class Config
{
    /**
     * Register the command callback. Note that it cannot be consistent with the default command of bridgecommand. Otherwise, the original callback of the system will be overwritten
     * initCommand
     * @param BridgeCommand $command
     * @author Tioncico
     * Time: 10:36
     */
    static function initCommand(BridgeCommand $command)
    {
        $command->set(401, [Config::class, 'info']);
        $command->set(402, [Config::class, 'set']);
    }

    /**
     * Get config configuration information
     * info
     * @param Package $package
     * @param Package $response
     * @return bool
     * @author Tioncico
     * Time: 10:39
     */
    static function info(Package $package,Package $response)
    {
        $data = $package->getArgs();
        if (empty($data['key'])){
            $configArray =GlobalConfig::getInstance()->toArray();
            $configArray['mode'] = Core::getInstance()->isDev() ? 'develop' : 'produce';
        }else{
            $configArray = GlobalConfig::getInstance()->getConf($data['key']);
            $configArray = [$data['key']=>$configArray];
        }
        $response->setArgs($configArray);
        return true;
    }

    /**
     * Set config configuration information
     * set
     * @param Package $package
     * @param Package $response
     * @return bool
     * @author Tioncico
     * Time: 10:39
     */
    static function set(Package $package,Package $response){
        $data = $package->getArgs();
        if (empty($data['key'])){
            $response->setArgs( "config key can not be null");
            return false;
        }
        $key = $data['key'];
        $value = $data['value']??null;
        GlobalConfig::getInstance()->setConf($key,$value);
        $response->setArgs([$key=>$value]);
        return true;
    }
}
```
#### Register interaction class to bridge:
```php
public static function mainServerCreate(EventRegister $register)
{
    \App\Bridge\Config::initCommand(Bridge::getInstance()->onCommand());
    // TODO: Implement mainServerCreate() method.
}
```
::: warning
You can only register in the `mainservercreate` event 
:::

#### External script interaction
After starting easyswoole, run the following code to dynamically add the configuration item 'a = > 2' in the easyswoole service, and obtain the current configuration through the '401' command

```php
<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/11 0011
 * Time: 10:53
 */

include "./vendor/autoload.php";
//Initialize easyswoole framework service
\EasySwoole\EasySwoole\Core::getInstance()->initialize();

//::: warning 
//After version 3.3.7, the initialize event call changes to:`EasySwoole\EasySwoole\Core::getInstance()->initialize()->globalInitialize();`
//:::

//Start a scheduler
$run = new \Swoole\Coroutine\Scheduler();
$run->add(function (){
    $package = new \EasySwoole\EasySwoole\Bridge\Package();
    $package->setCommand(402);
    $package->setArgs(['key' => 'a','value'=>2]);
    $package = \EasySwoole\EasySwoole\Bridge\Bridge::getInstance()->send($package);
    var_dump($package);
    $package = new \EasySwoole\EasySwoole\Bridge\Package();
    $package->setCommand(401);
    $package->setArgs(['key' => 'a']);
    $package = \EasySwoole\EasySwoole\Bridge\Bridge::getInstance()->send($package);
    var_dump($package);
});
//Execution scheduler
$run->start();
```

::: warning 
This script can be put into the custom command to realize interaction with `easyswoole service` through the custom command. The specific code can be viewed in the source code: https://github.com/easy-swoole/easyswoole/blob/3.x/src/Command/DefaultCommand/Config.php
:::


