# EasySwoole Framework version update record
> The latest update record of the EasySwoole framework only describes the update record after October 24th, 2020. For details of the previous update record, please refer to the document of the old version or github record.


## Version 3.4.2 - December 24th, 2020

### Optimized

- Optimized the `task` component, more flexible configuration can be used.
- Optimized the `crontab` component, it can avoid the task not being executed in extreme situations.
- Optimized the `http-dispatcher` component, you can register routes more flexibly.


## Version 3.4.1 - November 18th, 2020

### Added

- Added constant `SysConst::EXECUTE_COMMAND`, the constant can get the executed `command` inside the main frame.
- Added check that the `install` command check whether the symlink and readlink functions are disabled.

### Fixed

- Fixed bug that the `table` information is not cleaned up when `worker` exits abnormally.

### Removed

- Removed the config function in the `command` and `bridge` component, because the configuration does not use `swoole-table`.


## Version 3.4.0 - October 24th, 2020

v`3.4.x` is not compatible with v`3.3.x`, 3.4.x has made relatively large adjustments.

To upgrade from v`3.3.x` to v`3.4.x`, you need to re-execute the `php vendor/bin/easyswoole install` command to complete the upgrade.


### Added

- Added method `Core::getInstance()->runMode();`. You can use the method to modify the configuration file used when the framework is running. The default configuration file used when the framework is running is `dev.php`. You can also dynamically modify this configuration file in the startup command.

### Changed

- [The basic management command](/QuickStart/command.md) of the framework has been changed.

- When upgrading the framework to v3.4.x, the user-defined command needs to be [adjusted](https://github.com/easy-swoole/command). (For versions lower than v3.4.x)

- `config` is changed from the previously used `swoole-table` to `splArray`, users can adjust `config` by themselves.

- The use of `onRequest` and `afterRequest` global events is changed, it needs to be registered in the `initialize` event and use, and the usage is as follows:
```php
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, callback);
\EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, callback);
```
The `callback` parameter in the above function is the callback function, and the parameters that need to be injected in the callback function are as follows:
```php
function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response){}
```
The `onRequest` event needs to return `bool` to determine whether the program continues with the `dispatcher`.

### Removed

- Removed global events `onRequest` and `afterRequest` in `EasySwooleEvent`.

- Removed method `Core::getInstance()->isDev();`.

- Removed  `Core::getInstance()->globalInitialize();`, you can call `EasySwooleEvent::initialize()` by yourself.

