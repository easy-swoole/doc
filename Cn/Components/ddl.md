---
title: easyswoole数据库DDL定义
meta:
  - name: description
    content: 数据库模式定义语言DDL(Data Definition Language)，是用于描述数据库中要存储的现实世界实体的语言。easyswoole提供了一个DDL库，方便用户用于定义一个数据库表结构。
  - name: keywords
    content: easyswoole ddl

---

# DDL

数据库模式定义语言DDL(Data Definition Language)，是用于描述数据库中要存储的现实世界实体的语言。Easyswoole提供了一个DDL库，方便用户用于定义一个数据库表结构。

## 组件要求

- easyswoole / spl：^ 1.2

## 安装方法

> composer require easyswoole/ddl

## 仓库地址

[easyswoole/ddl](https://github.com/easy-swoole/ddl)

## 基本使用

### 创建表(CreateTable)

```php
use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;

$scoreSql = DDLBuilder::create('score', function (CreateTable $table) {
    $table->setIfNotExists()->setTableComment('成绩表');          //设置表名称
    $table->setTableCharset(Character::UTF8MB4_GENERAL_CI);     //设置表字符集
    $table->setTableEngine(Engine::INNODB);                     //设置表引擎
    $table->int('id')->setIsUnsigned()->setIsAutoIncrement()->setIsPrimaryKey()->setColumnComment('自增ID');
    $table->int('stu_id')->setIsUnsigned()->setColumnComment('学生id');
    $table->int('course_id')->setIsUnsigned()->setZeroFill()->setColumnComment('课程id');
    $table->float('score', 3, 1)->setColumnComment('成绩');
    $table->int('created_at', 10)->setColumnComment('创建时间');
    $table->foreign(null,'stu_id','student','stu_id')
        ->setOnDelete(Foreign::CASCADE)->setOnUpdate(Foreign::CASCADE);
});
echo $scoreSql;

//结果如下：

CREATE TABLE IF NOT EXISTS `score` (
  `id` int UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '自增ID',
  `stu_id` int UNSIGNED NOT NULL COMMENT '学生id',
  `course_id` int UNSIGNED ZEROFILL NOT NULL COMMENT '课程id',
  `score` float(3,1) NOT NULL COMMENT '成绩',
  `created_at` int(10) NOT NULL COMMENT '创建时间',
  FOREIGN KEY (`stu_id`) REFERENCES `student` (`stu_id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = INNODB DEFAULT COLLATE = 'utf8mb4_general_ci' COMMENT = '成绩表';

```

### 修改表(AlterTable)

```php
use EasySwoole\DDL\Blueprint\Alter\Table as AlterTable;
use EasySwoole\DDL\DDLBuilder;

$alterStuScoreSql = DDLBuilder::alter('score', function (AlterTable $table) {
    $table->setRenameTable('student_score')->setTableComment('学生成绩表');
    $table->modifyIndex('ind_score')->normal('ind_score', 'score')->setIndexComment('学生成绩--普通索引');
    $table->modifyForeign('fk_stu_id')->foreign('fk_stu_id', 'stu_id', 'student_info', 'stu_id');
});
echo $alterStuScoreSql;

//结果如下：

ALTER TABLE `score` RENAME TO `student_score`;
ALTER TABLE `student_score` 
COMMENT = '学生成绩表',
DROP INDEX `ind_score`,
ADD INDEX `ind_score` (`score`) COMMENT '学生成绩--普通索引';
ALTER TABLE `student_score` DROP FOREIGN KEY `fk_stu_id`;
ALTER TABLE `student_score` ADD CONSTRAINT `fk_stu_id` FOREIGN KEY (`stu_id`) REFERENCES `student_info` (`stu_id`);

```

### 删除表(DropTable)

```php
use EasySwoole\DDL\DDLBuilder;

$dropStuScoreSql = DDLBuilder::drop('student_score');
echo $dropStuScoreSql;

//结果如下：

DROP TABLE `student`;
```
