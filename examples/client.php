<?php
include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';

use lyhiving\mmodel\Mcache;
use lyhiving\mmodel\Mmodel;

$options = [
    'driver' => 'rpc',
    'url' => 'http://localhost/mmodel/examples/server.php',
    'prefix' =>'cloud_'
];


$model = new Mmodel($options);
$model->quick('cms');
$data = $model->select(array('contentid' => 1));
var_dump($data);

