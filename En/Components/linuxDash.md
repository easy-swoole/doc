## LinuxDash
Linux dash encapsulates many commands to get Linux information directly.

install:  
```bash
composer require easyswoole/linux-dash 
```  


## example
```php

$run = new \Swoole\Coroutine\Scheduler();
$run->add(function () {
    //Get IP address network card buffer information
    $data = LinuxDash::arpCache();
    var_dump($data);
    //Get current bandwidth data
    $data = LinuxDash::bandWidth();
    var_dump($data);
    //Get CPU process occupation ranking information
    $data = LinuxDash::cpuIntensiveProcesses();
    var_dump($data);
    //Get partition information
    $data = LinuxDash::diskPartitions();
    var_dump($data);
    //Get current memory usage information
    $data = LinuxDash::currentRam();
    var_dump($data);
    //Get CPU Information
    $data = LinuxDash::cpuInfo();
    var_dump($data);
    //Get current system information
    $data = LinuxDash::generalInfo();
    var_dump($data);
    //Get the current disk IO statistics
    $data = LinuxDash::ioStats();
    var_dump($data);
    //Get IP address
    $data = LinuxDash::ipAddresses();
    var_dump($data);
    //CPU load information
    $data = LinuxDash::loadAvg();
    var_dump($data);
    //Get memory details
    $data = LinuxDash::memoryInfo();
    var_dump($data);
    //Get the ranking information of process memory usage
    $data = LinuxDash::ramIntensiveProcesses();
    var_dump($data);
    //Get swap space information
    $data = LinuxDash::swap();
    var_dump($data);
    //Get current user name information
    $data = LinuxDash::userAccounts();
    var_dump($data);

});
$run->start();
```

> Note that the MAC environment is not compatible. But you can use docker to test
