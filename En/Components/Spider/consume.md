---
title: Spider
meta:
  - name: description
    content: EasySwoole-Spider It is convenient for users to quickly build distributed multi process Crawlers。
  - name: keywords
    content:  swoole | swoole expand | swoole framework | easyswoole | spider | crawler
---

## Consume

The data produced by product will be delivered to the consumer task data in jobdata

````php
<?php
namespace App\Spider;

use EasySwoole\Spider\ConsumeJob;
use EasySwoole\Spider\Hole\ConsumeAbstract;

class ConsumeTest extends ConsumeAbstract
{

    public function consume()
    {
        // TODO: Implement consume() method.
        $data = $this->getJobData();

        $items = '';
        foreach ($data as $item) {
            $items .= implode("\t", $item)."\n";
        }

        file_put_contents('xx.txt', $items, FILE_APPEND);
    }
}
````
