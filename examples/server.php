<?php
include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';

use Hprose\Http\Server;
use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mdb;

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

$server = new Server();
$db = Mdb::get_instance($options);
$db->set_cache($cache);
$server->addInstanceMethods($db);
$server->setCrossDomainEnabled();
$server->start();