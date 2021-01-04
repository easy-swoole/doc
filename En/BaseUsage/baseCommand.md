## Basic Command
Easyswoole comes with a series of commands, as follows:
- install       install easySwoole
- start         start easySwoole
- stop          stop easySwoole(Using in a daemon)
- reload        hotreload easySwoole(Using in a daemon)
- restart       restart easySwoole(Using in a daemon)
- help          help information
- phpunit       Start the process unit test
- config        easyswoole configuration management
- process       easyswoole custom process/task process management
- status        easyswole service running status
- task          easyswoole task process status
- crontab       easyswoole crontab management

### install   
`easyswoole` install command,The command is automatically created`App/HttpController/Index.php`,And automatically`composer dump-autoload `(If exec function is disabled, execution will fail, and manual execution is required)
### start        
start `easyswoole` service
```bash
php easyswoole start d  Daemons start
php easyswoole start d produce  Production environment, the daemons start, and the production environment will introduce the product.php configuration file,
```
### stop          
stop `easyswoole` service
The command will get /Temp/pid.pid PID,For closing`easyswole`process,Service cannot be stopped by this command when the file is expired / deleted / expired.
```bash
php easyswoole stop Stop the `easyswoole` service (running tasks will wait for the end of running before stopping)
php easyswoole stop force  Force stop of `easyswoole` service (kill process directly)
php easyswoole stop produce Stop production environment `easyswoole` service
```

### reload        
Hot restart `easyswoole` service
::: warning
 Note that reload is required in the daemons mode, otherwise control + C or the terminal will exit the process when it is disconnected. This is a hot restart, which can be used to update the files (business logic) loaded after the worker starts. The main process (such as configuration files) will not be restarted. HTTP custom routing configuration will not be updated, restart is required;
:::
### restart 
Force restart of the 'easyswoole' service
The command `php easyswoole stop force`+ `php easyswoole start d `combination
### help       
Help command.
```bash
php easyswoole help stop  View stop command help
```   
### phpunit       
Start the unit test of 'easyswoole', which will bring the collaboration environment:
```bash
php easyswoole phpunit ./Tests
```
### config        
Dynamic management config command.
```bash
php easyswoole config show [key]  View configuration item information, key support. Separator
php easyswoole config set key value Set a configuration dynamically. Key supports. Separator
```
### process    
View / manage custom processes (including task processes)
```bash
php easyswoole process kill PID [-p] [-d]  -pThe delegate kills the process and restarts it through the process ID
php easyswoole process kill PID [-f] [-p] [-d] -f On behalf of force kill process and restart
php easyswoole process kill GroupName [-f] [-d]  Without -p, it means to kill a process group and restart it
php easyswoole process killAll [-d] Kill all processes and restart
php easyswoole process killAll -f [-d] Force kill all processes and restart
php easyswoole process show Show current process list
php easyswoole process show -d -d stands for personalized display of memory information
```
### status        
View service running status
```bash
[root@localhost easyswoole-git]# php easyswoole status
start_time                    2020-04-04 11:08:39
connection_num                0
accept_count                  0
close_count                   0
worker_num                    8
idle_worker_num               8
tasking_num                   0
request_count                 0
worker_request_count          0
worker_dispatch_count         0
coroutine_num                 2
```
### task          
View task process status
```bash
[root@localhost easyswoole-git]# php easyswoole task status
#┌─────────┬─────────┬──────┬───────┬─────────────┐
#│ running │ success │ fail │  pid  │ workerIndex │
#├─────────┼─────────┼──────┼───────┼─────────────┤
#│ 0       │ 4       │ 0    │ 28241 │ 0           │
#│ 0       │ 3       │ 0    │ 28242 │ 1           │
#│ 0       │ 3       │ 0    │ 28243 │ 2           │
#│ 0       │ 2       │ 0    │ 28244 │ 3           │
#└─────────┴─────────┴──────┴───────┴─────────────┘

```
### crontab       
```bash
php easyswoole crontab show  View the current scheduled task list
php easyswoole crontab stop taskName Pause a scheduled task
php easyswoole crontab resume taskName  Continue running a scheduled task
php easyswoole crontab run taskName  Perform a scheduled task immediately


```
