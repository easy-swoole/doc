---
title: Spider
meta:
  - name: description
    content: EasySwoole-Spider It is convenient for users to quickly build distributed multi process Crawlers。
  - name: keywords
    content:  swoole | swoole expand | swoole framework | easyswoole | spider | crawler
---

## Spider

Spider component can facilitate users to quickly build distributed multi process crawlers. Users only need to care about product and consumer, and product recommends [querylist](http://www.querylist.cc/) for DOM parsing

## Install
```
composer require easyswoole/spider
```

## Rapid use

Take Baidu search for example, according to search keywords, climb out specific data in the first few pages of each search result
`It is purely for teaching purpose. If you offend your company, please inform us in time and we will adjust it in time`

#### Product

```php
<?php
namespace App\Spider;

use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Spider\Config\ProductConfig;
use EasySwoole\Spider\Hole\ProductAbstract;
use EasySwoole\Spider\ProductResult;
use QL\QueryList;
use EasySwoole\FastCache\Cache;

class ProductTest extends ProductAbstract
{

    public function product():ProductResult
    {
        // TODO: Implement product() method.
        // Get url
        $httpClient = new HttpClient($this->productConfig->getUrl());
        $httpClient->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.116 Safari/537.36');
        $body = $httpClient->get()->getBody();

        $rules = [
            'search_result' => ['.c-container .t', 'text', 'a']
        ];
        $searchResult = QueryList::rules($rules)->html($body)->query()->getData();

        $data = [];
        foreach ($searchResult as $result) {
            $item = [
                'href' => QueryList::html($result['search_result'])->find('a')->attr('href'),
                'text' => QueryList::html($result['search_result'])->find('a')->text()
            ];
            $data[] = $item;
        }

        $productJobOtherInfo = $this->productConfig->getOtherInfo();

        // Next tasks
        $productJobConfigs = [];
        if ($productJobOtherInfo['page'] === 1) {
            for($i=1;$i<5;$i++) {
                $pn = $i*10;
                $productJobConfig = [
                    'url' => "https://www.baidu.com/s?wd={$productJobOtherInfo['word']}&pn={$pn}",
                    'otherInfo' => [
                        'word' => $productJobOtherInfo['word'],
                        'page' => $i+1
                    ]
                ];
                $productJobConfigs[] = $productJobConfig;
            }

            $word = Cache::getInstance()->deQueue(self::SEARCH_WORDS);
            if (!empty($word)) {
                $productJobConfigs[] = [
                    'url' => "https://www.baidu.com/s?wd={$word}&pn=0",
                    'otherInfo' => [
                        'word' => $word,
                        'page' => 1
                    ]
                ];
            }

        }

        $result = new ProductResult();
        $result->setProductJobConfigs($productJobConfigs)->setConsumeData($data);
        return $result;
    }

}
```

### Consume

I have directly saved the file here, and can customize it according to the demand
```php
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

        file_put_contents('baidu.txt', $items, FILE_APPEND);
    }
}
```

### Register spider components

```php
public static function mainServerCreate(EventRegister $register)
{
        $spiderConfig = [
            'product' => ProductTest::class, // Must
            'consume' => ConsumeTest::class, // Must
            'queueType' => SpiderConfig::QUEUE_TYPE_FAST_CACHE, // The default communication type is fast cache, which does not support distributed. If you need distributed, you can use SpiderConfig::QUEUE_TYPE_REDIS，Or implement communication queue by yourself
            'queue' => 'Custom queues are not required if they are built in by components',
            'queueConfig' => 'Custom queue configuration, currently only SpiderConfig::QUEUE_TYPE_REDIS need',
            'maxCurrency' => 128 // Maximum concurrent number of coroutine
        ];
        SpiderServer::getInstance()
            ->setSpiderConfig($spiderConfig)
            ->attachProcess(ServerManager::getInstance()->getSwooleServer());
}
```

### Delivery task
````php
$words = [
    'php',
    'java',
    'go'
];

foreach ($words as $word) {
    Cache::getInstance()->enQueue('SEARCH_WORDS', $word);
}

$wd = Cache::getInstance()->deQueue('SEARCH_WORDS');

SpiderClient::getInstance()->addJob(
                'https://www.baidu.com/s?wd=php&pn=0',
                [
                    'page' => 1,
                    'word' => $wd
                ]
);
````