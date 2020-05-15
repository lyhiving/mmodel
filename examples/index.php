<?php
include __DIR__ . '/../autoload.php';

use lyhiving\mmodel\Mmodel;


function cache_load($key, $force=true){
    return false;
}

function cache_write($key, $value){
    return true;
}

$options = [
    // 'driver' => 'mysql',
    'host' => 'localhost',
    // 'port' => '3306',
    'username' => 'root',
    'password' => 'root',
    'dbname' => 'test',
    'prefix' => 'cloud_',
    // 'pconnect' => 1,
    'charset' => 'utf8mb4'
];


$model = new Mmodel($options);
$model->quick('ewei_shop_cms');
$data = $model->select(array('contentid'=>1));

var_dump($model->select(array('contentid'=>1)));