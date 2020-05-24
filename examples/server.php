<?php
include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';

use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;
use Hprose\Http\Server;

$cache = new Mcache('redis', [
    'host' => 'localhost',
    'port' => 6379,
    'password' => 'root',
    'database' => 0
]);

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