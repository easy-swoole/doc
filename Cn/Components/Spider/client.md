---
title: Spider
meta:
  - name: description
    content: EasySwoole-Spider 可以方便用户快速搭建分布式多协程爬虫。
  - name: keywords
    content: swoole|swoole 拓展|swoole 框架|easyswoole|spider|爬虫
---

### Spider客户端

通过客户端可以向Spider服务添加要爬取的任务信息

```php
SpiderClient::getInstance()->addJob('https://xxxxx','其它信息’);
```

### 支持的方法

##### 添加job
````php
    public function addJob($url, $otherInfo)
````

##### 批量添加job
````php
    public function addJobs(array $jobsConfig)
````

````php
    $jobsConfig = [
      [
        'url' => xxx,
        'otherInfo => xxx
      ],
      [
        'url' => xxx,
        'otherInfo => xxx
      ],
      [
        'url' => xxx,
        'otherInfo => xxx
      ]
    ];
````




