---
title: easyswoole安装教程
meta:
  - name: description
    content: easyswoole安装教程
  - name: keywords
    content: easyswoole安装教程|swoole框架安装教程
---


# 框架安装

- [GitHub](https://github.com/easy-swoole/easyswoole)  喜欢记得给我们点个 ***star***

::: danger 
注意事项，请看完再进行安装
:::

- 框架使用 `Composer` 作为依赖管理工具，在开始安装框架前，请确保已经按上一章节的要求配置好环境并安装好了`Composer` 工具
- 关于 Composer 的安装可以参照 [Composer中国全量镜像](https://pkg.phpcomposer.com/#how-to-install-composer) 的安装教程
- 目前推荐的镜像为阿里云或者梯子拉取源站
- 在安装过程中，会释放框架的文件到项目目录，请保证项目目录有可写入权限
- 安装完成之后，不会自动生成App目录，请自行根据Hello World章节配置


## 切换阿里云镜像
````
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
````

删除镜像
```
composer config -g --unset repos.packagist
```

## Composer 安装

按下面的步骤进行手动安装

```bash
composer require easyswoole/easyswoole=3.x
php vendor/easyswoole/easyswoole/bin/easyswoole install
```


或者
```bash
composer require easyswoole/easyswoole=3.x
php vendor/bin/easyswoole install
```
如果执行成功，则会有如下界面出现
```bash
  php vendor/easyswoole/easyswoole/bin/easyswoole install
  ______                          _____                              _        
 |  ____|                        / ____|                            | |       
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___ 
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |                                                
                         |___/                                                
  EasySwooleEvent.php has already existed. do you want to replace it? [ Y/N (default) ] : n
  index.php has already existed. do you want to replace it? [ Y/N (default) ] : n
  dev.php has already existed. do you want to replace it? [ Y/N (default) ] : n
  produce.php has already existed. do you want to replace it? [ Y/N (default) ] : n
```
::: danger 
新版安装注意事项
:::

- 新版的easyswoole安装会默认提供App命名空间，还有index控制器
- 在这里面需要填写n，不需要覆盖，已经有的 EasySwooleEvent.php，index.php dev.php produce.php
- 当提示exec函数被禁用时,请自己手动执行 `composer dump-autoload` 命令更新命名空间

### 按照报错
当执行安装脚本，出现类似以下错误时：
```
dir=$(cd "${0%[/\\]*}" > /dev/null; cd '../easyswoole/easyswoole/bin' && pwd)

if [ -d /proc/cygdrive ]; then
    case $(which php) in
        $(readlink -n /proc/cygdrive)/*)
            # We are in Cygwin using Windows php, so the path must be translated
            dir=$(cygpath -m "$dir");
            ;;
    esac
fi

"${dir}/easyswoole" "$@"
```

请检查环境是否为宝塔等其他集成面板，或者是php.ini配置项中禁用了```symlink```与```readlink```函数，请关闭这两个函数的禁用，并删除vender目录，重新执行
```composer require```或者是```composer install```或者是```composer update```

## 启动框架

中途没有报错的话，执行：
```bash
# 启动框架
php easyswoole start
```
此时可以访问 `http://localhost:9501` 看到框架的欢迎页面，表示框架已经安装成功

### 可能的问题
- not controller class match
   - composer.json注册 App 这个名称空间了吗？
   - 执行过``` composer dump-autoload ```了吗？
   - 存在Index控制器，且文件大小写，路径都对了吗？

- task socket listen fail
   - 注意，在部分环境下，例如win10的docker环境中，不可把虚拟机共享目录作为EasySwoole的Temp目录，否则会因为权限不足无法创建socket，产生报错：listen xxxxxx.sock fail,为此可以手动在dev.php配置文件里把Temp目录改为其他路径即可,如：'/Tmp'


## 其他

- QQ交流群
    - VIP群 579434607 （本群需要付费599元）
    - EasySwoole官方一群 633921431(已满)
    - EasySwoole官方二群 709134628(已满)
    - EasySwoole官方三群 932625047(已满)
    - Easyswoole官方四群 779897753 
    
- 商业支持：
    - QQ 291323003
    - EMAIL admin@fosuss.com
        
- 作者微信

     ![](/Images/authWx.png)
    
<script>
        if(localStorage.getItem('isNew') != 1){
            localStorage.setItem('isNew',1);
            layer.confirm('是否给EasySwoole点个赞',{offset:'c'},function (index) {
                 layer.msg('感谢您的支持',{offset:'c'});
                     setTimeout(function () {
                         window.open('https://github.com/easy-swoole/easyswoole');
                  },1500);
             });              
        }
</script>