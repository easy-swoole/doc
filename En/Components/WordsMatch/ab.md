---
title: easyswoole Content detection
meta:
  - name: description
    content: easyswoole Content detection
  - name: keywords
    content: swoole|easyswoole|Content detection|Sensitive words|detect
---

# Pressure test results
This component is tested with 15000 and 130000 level thesaurus respectively, and the service starts three processes by default.

### Computer configuration
```
MacBook Air (13-inch, 2017)
处理器 1.8 GHz Intel Core i5
内存 8 GB 1600 MHz DDR3
```

### 15000 words

##### Concurrent 10 total requests 100

```
10 100
Concurrency Level:      10
Time taken for tests:   0.067 seconds
Complete requests:      100
Failed requests:        0
Non-2xx responses:      100
Total transferred:      17300 bytes
HTML transferred:       2600 bytes
Requests per second:    1492.49 [#/sec] (mean)
Time per request:       6.700 [ms] (mean)
Time per request:       0.670 [ms] (mean, across all concurrent requests)
Transfer rate:          252.15 [Kbytes/sec] received
```

##### Concurrent 100 total requests 1000

```
Concurrency Level:      100
Time taken for tests:   0.239 seconds
Complete requests:      1000
Failed requests:        0
Non-2xx responses:      1000
Total transferred:      173000 bytes
HTML transferred:       26000 bytes
Requests per second:    4189.17 [#/sec] (mean)
Time per request:       23.871 [ms] (mean)
Time per request:       0.239 [ms] (mean, across all concurrent requests)
Transfer rate:          707.74 [Kbytes/sec] received
```

### 130000 words

##### Concurrent 10 total requests 100

```
Concurrency Level:      10
Time taken for tests:   0.057 seconds
Complete requests:      100
Failed requests:        0
Non-2xx responses:      100
Total transferred:      17300 bytes
HTML transferred:       2600 bytes
Requests per second:    1751.71 [#/sec] (mean)
Time per request:       5.709 [ms] (mean)
Time per request:       0.571 [ms] (mean, across all concurrent requests)
Transfer rate:          295.94 [Kbytes/sec] received
```

##### Concurrent 100 total requests 1000

```
Concurrency Level:      100
Time taken for tests:   0.225 seconds
Complete requests:      1000
Failed requests:        0
Non-2xx responses:      1000
Total transferred:      173000 bytes
HTML transferred:       26000 bytes
Requests per second:    4444.84 [#/sec] (mean)
Time per request:       22.498 [ms] (mean)
Time per request:       0.225 [ms] (mean, across all concurrent requests)
Transfer rate:          750.93 [Kbytes/sec] received
```

