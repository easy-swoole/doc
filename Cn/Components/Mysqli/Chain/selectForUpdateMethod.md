# selectForUpdate

SELECT FOR UPDATE锁定(InnoDb)

## 传参说明

方法原型
```php
function selectForUpdate($isLock = true, string $option = null)
```

- $isLock   
- $option NOWAIT,WAIT 5,SKIP LOCKED
