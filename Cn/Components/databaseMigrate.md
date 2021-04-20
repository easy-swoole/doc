---
title: easyswoole数据库版本迁移工具
meta:
  - name: description
    content: 参照Laravel开发的easyswoole数据库版本迁移工具。
  - name: keywords
    content: easyswoole database migrate db-migrate

---

# db-migrate

参照Laravel开发的easyswoole数据库版本迁移工具。

## 组件要求

- php: >=7.1.0
- easyswoole/command: ^1.1
- easyswoole/component: ^2.0
- easyswoole/ddl: ^1.0
- easyswoole/mysqli: ^2.2
- easyswoole/spl: ^1.0
- easyswoole/utility: ^1.0

## 安装方法

> composer require easyswoole/db-migrate

## 仓库地址

[easyswoole/db-migrate](https://github.com/easy-swoole/database-migrate)

## 基本使用

在全局 `boostrap` 事件中注册 `MigrateCommand` 并添加配置信息

> bootstrap.php

```php
\EasySwoole\Command\CommandManager::getInstance()->addCommand(new \EasySwoole\DatabaseMigrate\MigrateCommand());
$config = new \EasySwoole\DatabaseMigrate\Config\Config();
// 数据地址
$config->setHost("127.0.0.1");
// 数据库端口
$config->setPort(3306);
// 数据库用户名
$config->setUser("root");
// 数据库密码
$config->setPassword("123456");
// 数据库库名
$config->setDatabase("easyswoole");
// 数据库超时时长
$config->setTimeout(5.0);
// 数据库字符集
$config->setCharset("utf8mb4");
//===========可选配置修改项，以下参数均有默认值===========
// 迁移记录的数据库表名
$config->setMigrateTable("migrations");
// 迁移文件目录的绝对路径
$config->setMigratePath(EASYSWOOLE_ROOT . '/Database/Migrates/');
// 迁移模板文件的绝对路径
$config->setMigrateTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/migrate._php');
// 迁移模板类的类名
$config->setMigrateTemplateClassName("MigratorClassName");
// 迁移模板类的表名
$config->setMigrateTemplateTableName("MigratorTableName");
// 迁移模板创建表的模板文件的绝对路径
$config->setMigrateCreateTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/migrate_create._php');
// 迁移模板修改表的模板文件的绝对路径
$config->setMigrateAlterTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/migrate_alter._php');
// 迁移模板删除表的模板文件的绝对路径
$config->setMigrateDropTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/migrate_drop._php');
// 数据填充目录绝对路径
$config->setSeederPath(EASYSWOOLE_ROOT . '/Database/Seeds/');
// 数据填充模板类的类名
$config->setSeederTemplateClassName("SeederClassName");
// 数据填充模板文件的绝对路径
$config->setSeederTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/seeder._php');
// 逆向生成迁移文件的模板文件绝对路径
$config->setMigrateGenerateTemplate(EASYSWOOLE_ROOT . '/vendor/easyswoole/db-migrate/src/Resource/migrate_generate._php');
// 逆向生成迁移模板SQL语句的DDL代码块
$config->setMigrateTemplateDdlSyntax("DDLSyntax");
\EasySwoole\DatabaseMigrate\MigrateManager::getInstance($config);
```

::: tip   
如果不自定义`setMigratePath`、`setSeederPath`配置项，所有迁移命令必须在项目根目录(固定目录)下执行   
:::

执行 `php easyswoole migrate -h`

```text
php easyswoole migrate -h
Database migrate tool

Usage:
  easyswoole migrate ACTION [--opts ...]

Actions:
  create    Create the migration repository
  generate  Generate migration repository for existing tables
  run       Run all migrations
  rollback  Rollback the last database migration
  reset     Rollback all database migrations
  seed      Data filling tool
  status    Show the status of each migration

Options:
  -h, --help  Get help
```

### create
> 创建一个迁移模板
>
> 当需要新建表、修改表、删除表时，create命令可以创建一个简单的迁移模板文件

可用操作选项：

- `--alter`：生成一个用于修改表的迁移模板
    - 示例：`php easyswoole migrate create --alter=TableName`
- `--create`：生成一个用于新建表的迁移模板
    - 示例：`php easyswoole migrate create --create=TableName`
- `--drop`：生成一个用于删除表的迁移模板
    - 示例：`php easyswoole migrate create --drop=TableName`
- `--table`：生成一个基础的迁移模板
    - 示例：`php easyswoole migrate create --table=TableName`  等同于 `php easyswoole migrate create TableName`

操作会在迁移文件目录生成一个类似文件名为`2021_04_08_082914_user.php`的文件，代码类似如下，对应操作使用的是 [easyswoole/ddl](https://github.com/easy-swoole/ddl) 组件方法

```php
<?php

use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\Blueprint\Alter\Table as AlterTable;
use EasySwoole\DDL\Blueprint\Drop\Table as DropTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;

/**
 * migrate create
 * Class User
 */
class User
{
    /**
     * migrate run
     * @return string
     */
    public function up()
    {
        return DDLBuilder::create('User',function (CreateTable $table){
            $table->setIfNotExists(true);
        });
    }

    /**
     * migrate rollback
     * @return string
     */
    public function down()
    {
        return DDLBuilder::dropIfExists('User');
    }
}
```

### generate
> 对已存在的表生成适配当前迁移工具的迁移模板
>
> 对于已经启动的项目没有做版本迁移，generate命令可以对已存在的表逆向生成迁移文件

对已存在的表生成适配当前迁移工具的迁移模板

可用操作选项：

- `--tables`：指定要生成迁移模板的表，多个表用 ',' 隔开
    - 示例：`php easyswoole migrate generate --tables=table1,table2`
- `--ignore`：指定要忽略生成迁移模板的表，多个表用 ',' 隔开
    - 示例：`php easyswoole migrate generate --ignore=table1,table2`

### run
> 对所有未迁移的文件执行迁移操作

### rollback
> 回滚迁移记录，默认回滚上一次的迁移，指定操作相关参数可以从status命令中查看

可用操作选项：

- `--batch`：指定要回滚的批次号
    - 示例：`php easyswoole migrate rollback --batch=2`
- `--id`：指定要回滚的迁移ID
    - 示例：`php easyswoole migrate rollback --id=2`

### reset
> 根据迁移表的记录，一次性回滚所有迁移

### seed
> 数据填充工具
>
> 不加操作项即为执行填充数据操作，添加操作项即为创建填充模板
>
> 生成模板文件之后，方法内的操作可以使用 [easyswoole/mysqli](https://github.com/easy-swoole/mysqli) 或者 [easyswoole/orm](https://github.com/easy-swoole/orm) 做数据填充，

可用操作选项：

- `--class`：指定要填充的class name，也就是文件名 ==（请保证填充工具文件名与类名完全相同）==
    - 示例：`php easyswoole migrate seed --class=UserTable`
- `--create`：创建一个数据填充模板
    - 示例：`php easyswoole migrate seed --create=UserTable`

### status
> 迁移状态
>
> 展示成功迁移的数据，即为迁移表内的数据
