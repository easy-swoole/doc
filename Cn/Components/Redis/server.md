---
title: easyswoole redis server操作方法
meta:
  - name: description
    content: easyswoole redis server操作方法
  - name: keywords
    content: easyswoole redis server操作方法|swoole redis server操作方法
---
# Redis 服务器
Redis 服务器命令主要是用于管理 redis 服务。

## 操作方法

方法列表  
| 方法名称                | 参数                                | 说明                                         | 备注 |
|---------------------|-----------------------------------|--------------------------------------------|----|
| bgRewriteAof        |                                   | 手动触发重写操作。                                  |    |
| bgSave              |                                   | 在后台异步保存当前数据库的数据到磁盘                         |    |
| clientKill          | $data                             | 关闭客户端连接                                    |    |
| clientList          |                                   | 获取连接到服务器的客户端连接列表                           |    |
| clientGetName       |                                   | 获取连接的名称                                    |    |
| clientPause         | $timeout                          | 在指定时间内终止运行来自客户端的命令                         |    |
| clientSetName       | $connectionName                   | 设置当前连接的名称                                  |    |
| command             |                                   | 获取 Redis 命令详情数组                            |    |
| commandCount        |                                   | 获取 Redis 命令总数                              |    |
| commandGetKeys      | \.\.\.$data                       | 获取给定命令的所有键                                 |    |
| time                |                                   | 返回当前redis服务器时间                             |    |
| commandInfo         | $commandName, \.\.\.$commandNames | 获取指定 Redis 命令描述的数组                         |    |
| configGet           | $parameter                        | 获取指定配置参数的值                                 |    |
| configRewrite       |                                   | 对启动 Redis 服务器时所指定的 redis\.conf 配置文件进行改写    |    |
| configSet           | $parameter, $value                | 修改 redis 配置参数,无需重启                         |    |
| configResetStat     |                                   | 重置 INFO 命令中的某些统计数据                         |    |
| dBSize              |                                   | 返回当前数据库的 key 的数量                           |    |
| debugObject         | $key                              | 获取 key 的调试信息                               |    |
| debugSegfault       |                                   | 让 Redis 服务崩溃                               |    |
| flushAll            |                                   | 删除所有数据库的所有key                              |    |
| flushDb             |                                   | 删除当前数据库的所有key                              |    |
| info                | $section = null                   | 获取 Redis 服务器的各种信息和统计数值                     |    |
| lastSave            |                                   | 返回最近一次 Redis 成功将数据保存到磁盘上的时间，以 UNIX 时间戳格式表示 |    |
| monitor             | callable $callback                | redis监视器,实时打印出 Redis 服务器接收到的命令             |    |
| isMonitorStop       |                                   | 判断是否开启监视器                                  |    |
| setMonitorStop      | bool $monitorStop                 | 设置停止监视器                                    |    |
| role                |                                   | 返回主从实例所属的角色                                |    |
| save                |                                   | 同步保存数据到硬盘                                  |    |
| shutdown            |                                   | 异步保存数据到硬盘，并关闭服务器                           |    |
| slowLog             | $subCommand, \.\.\.$argument      | 管理 redis 的慢日志                              |    |
| SYNC                |                                   | 用于复制功能\(replication\)的内部命令                 |    |



## 基本使用
```php
<?php  
go(function () {
    $redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
    $redisConfig->setAuth('easyswoole');
    $redis = new \EasySwoole\Redis\Redis($redisConfig);

    $data = $redis->bgRewriteAof();
    var_dump($data);
    \Swoole\Coroutine::sleep(1);
    $data = $redis->bgSave();
    var_dump($data);
    $data = $redis->clientList();
    var_dump($data);
    $data = $redis->clientSetName('test');
    var_dump($data);
    $data = $redis->clientGetName();
    var_dump($data);
    $data = $redis->clientPause(1);
    var_dump($data);
    $data = $redis->command();
    var_dump($data);
    $data = $redis->commandCount();
    var_dump($data);
    $data = $redis->commandGetKeys('MSET', 'a', 'b', 'c', 'd');
    var_dump($data);
    $data = $redis->time();
    var_dump($data);
    $data = $redis->commandInfo('get', 'set');
    var_dump($data);
    $data = $redis->configGet('*max-*-entries*');
    var_dump($data);

    $data = $redis->configSet('appendonly', 'yes');
    var_dump($data);
    $data = $redis->configRewrite();
    var_dump($data);
    $data = $redis->configResetStat();
    var_dump($data);
    $data = $redis->dBSize();
    var_dump($data);
    $redis->set('a', 1);
    $data = $redis->debugObject('a');
    var_dump($data);
    $data = $redis->flushAll();
    var_dump($data);
    $data = $redis->flushDb();
    var_dump($data);
    $data = $redis->info();
    var_dump($data);
    $data = $redis->lastSave();
    var_dump($data);
    go(function () {
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
        $redisConfig->setAuth('easyswoole');
        $redis = new \EasySwoole\Redis\Redis($redisConfig);
        $redis->monitor(function ( \EasySwoole\Redis\Redis $redis, $data) {
            $this->assertIsString($data);
            $redis->set('a', 1);
            $redis->setMonitorStop(true);
        });
    });

    go(function () {
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
        $redisConfig->setAuth('easyswoole');
        $redis = new \EasySwoole\Redis\Redis($redisConfig);
        \Swoole\Coroutine::sleep(1);
        $redis->set('a', 1);
    });

    $data = $redis->save();
    var_dump($data);
    $data = $redis->clientKill($data[0]['addr']);
    var_dump($data);
    $data = $redis->slowLog('get', 'a');
    var_dump($data, $redis->getErrorMsg());
    var_dump($data);
});
```
