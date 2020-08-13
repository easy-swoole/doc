---
title: easyswoole 内容检测
meta:
  - name: description
    content: easyswoole 内容检测
  - name: keywords
    content: swoole|easyswoole|内容检测|敏感词|检测
---

## 支持的方法

#### detect

检测内容
````php
public function detect(string $content, float $timeout = 3.0) : array
````

#### append

添加词，第一个参数：词，第二个参数：词的其它信息
````php
public function append(string $word, array $otherInfo=[], float $timeout = 3.0)
````

#### remove

移除词

````php
public function remove(string $word, float $timeout = 3.0)
````