---
title: easyswoole实现waitGroup
meta:
  - name: description
    content: easyswoole实现waitGroup
  - name: keywords
    content: easyswoole实现waitGroup|swoole实现waitGroup
---

# waitgroup

示例代码：

```php
<?php
go(function (){
    $ret = [];

    $wait = new \EasySwoole\Component\WaitGroup();

    $wait->add();
    go(function ()use($wait,&$ret){
        \co::sleep(0.1);
        $ret[] = time();
        $wait->done();
    });

    $wait->add();
    go(function ()use($wait,&$ret){
        \co::sleep(2);
        $ret[] = time();
        $wait->done();
    });

    $wait->wait();

    var_dump($ret);
});
```
