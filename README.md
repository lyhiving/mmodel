# Minimum model for php

php 小模型。

## 快速实现CRUD操作的小模型

普通版CRUD + 远程CRUD 用于快速开始项目


## 安装

使用 Composer


```bash
composer require lyhiving/mmodel
```

或

```json
{
    "require": {
            "lyhiving/mmodel": "2.*"
    }
}
```

## 用法

1. 本地使用

```php
<?php

//引入autoload.php 
use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;

//如果想使用文件缓存，请确保缓存文件的安全
// $cache = new Mcache('files', ['path' => __DIR__ . '/.cachemeta']);

//使用redis缓存
$cache = new Mcache('redis', [
    'host' => 'localhost', //Redis server
    'port' => 6379, //Redis port
    'password' => 'root', //Redis password
    'database' => 0 //Redis db
]);

//配置参数
$options = [
    // 'driver' => 'mysql',
    'host' => 'localhost',
    // 'port' => '3306',
    'username' => 'root',
    'password' => 'root',
    'dbname' => 'test',
    'prefix' => 'cloud_',
    // 'pconnect' => 1,
    'charset' => 'utf8mb4',
];

$model = new Mmodel($options);
//设置缓存
$model->set_cache($cache);
$model->quick('cms');
$data = $model->select(array('contentid' => 1));
var_dump($data);

```

2. 远程使用

服务端：

```php
<?php
//引入autoload.php 

use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;
use Hprose\Http\Server;

//缓存
$cache = new Mcache('redis', [
    'host' => 'localhost',
    'port' => 6379,
    'password' => 'root',
    'database' => 0
]);

//配置
$options = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => '3306',
    'username' => 'root',
    'password' => 'root',
    'dbname' => 'test',
    'prefix' => 'cloud_',
    'pconnect' => 1,
    'charset' => 'utf8mb4',
];

$model = new Mmodel($options);
$model->set_cache($cache); 

$server = new Server();
$server->addInstanceMethods($model->db);
$server->setCrossDomainEnabled();
$server->start();
```

客户端：

```php
<?php
//引入autoload.php 

use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;

$options = [
    'driver' => 'rpc',
    'url' => 'http://localhost/mmodel/examples/server.php',
    'prefix' =>'cloud_'    //如果有设置前缀，一定要在配置注明
];


$model = new Mmodel($options);
$model->quick('cms');
$data = $model->select(array('contentid' => 1));
var_dump($data);
```

## 关于远程调用的加密

目前先这样用，远程的地址复杂点就是了。

TODO: 看情况加入渐变验证组件。

## 关于缓存

缓存实际上使用了 [phpfastcache](https://github.com/PHPSocialNetwork/phpfastcache)， 一个很不错的缓存包。但没用使用它的最新版本，原因是最新版本要求PHP7.4，这个对于部分程序还是不是非常友好。

实际上如果非常轻便的开发，甚至连缓存都可以不用。

使用缓存的时候注意，如果更新了表结构需要清除缓存。如果不清，默认15分钟后缓存失效。
 
## 关于hprose

目前[hprose](https://github.com/hprose/hprose-php)的作者估计想升级到hprose3.0。但3.0使用的compsoser要求安装ext_hprose扩展，这个对于部分同学部署起来并不友好。因此我限制使用2.0.40的版本，该版本不需要安装相关的扩展。

啰嗦一句，其实[Andot](https://github.com/hprose) 弄这个hprose很久的了，我也很早的时候就用上了。多久？那个时候还叫PHPRPC的时候 :) 

hprose支持很多语言，性能很很帅，可惜文档就是不给力。作者也是维护多语言版本，生态没弄好。希望hprose会好起来。

