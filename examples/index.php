<?php
include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';
use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Redis\Config;

if (0) {
    CacheManager::setDefaultConfig(new ConfigurationOption([
        'path' => __DIR__ . '/.cachemeta',
    ]));
    $InstanceCache = CacheManager::getInstance('files');
} else {
    $InstanceCache = CacheManager::getInstance('redis', new Config([
        'host' => '127.0.0.1', //Redis server
        'port' => 6379, //Redis port
        'password' => 'root', //Redis password
        'database' => 0, //Redis db
    ]));
}

$cache = new Mcache($InstanceCache);

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
$model->set_cache($cache);
$model->quick('cms');
$data = $model->select(array('contentid' => 1));

var_dump($model->select(array('contentid' => 1)));
