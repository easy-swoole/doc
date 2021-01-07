---
title: Spider
meta:
  - name: description
    content: EasySwoole-Spider It is convenient for users to quickly build distributed multi process Crawlers。
  - name: keywords
    content:  swoole | swoole expand | swoole framework | easyswoole | spider | crawler
---

### Spider Client

The client can add task information to spider service for crawling

```php
SpiderClient::getInstance()->addJob('https://xxxxx','Other info’);
```

### Supported methods

##### Add job
````php
    public function addJob($url, $otherInfo)
````

##### Batch add job
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




