---
title: easyswoole Content detection
meta:
  - name: description
    content: easyswoole Content detection
  - name: keywords
    content: swoole|easyswoole|Content detection|Sensitive words|detect
---

## Supported methods

#### detect

Test content
````php
public function detect(string $content, float $timeout = 3.0) : array
````

#### append

Add word, first parameter: word, second parameter: other information of word
````php
public function append(string $word, array $otherInfo=[], float $timeout = 3.0)
````

#### remove

Remove words

````php
public function remove(string $word, float $timeout = 3.0)
````