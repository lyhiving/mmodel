<?php
include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';
use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;

//use files cache 
// $cache = new Mcache('files', ['path' => __DIR__ . '/.cachemeta']);

//use redis cache
$cache = new Mcache('redis', [
    'host' => 'localhost', //Redis server
    'port' => 6379, //Redis port
    'password' => 'root', //Redis password
    'database' => 0 //Redis db
]);

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
var_dump($data);
var_dump($model->select(array('contentid' => 1)));
