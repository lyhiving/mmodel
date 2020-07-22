<?php

include __DIR__ . '/../autoload.php';
// include __DIR__ . '/../vendor/autoload.php';

use lyhiving\mmodel\Mmodel;

//init template 

$template = Mmodel::template([
    'dir' => __DIR__ . '/templates/',
    'compile_dir' => __DIR__ . '/templates_compile/', //[可选] 指定缓存沐浴露
    'name' => 'xfile', //[可选] 指定风格目录，默认default
    'ext'  => '.html',  //[可选] 后缀
    'compile_check' => true, //[可选] 是否编译检测
    'compile_force' => false, //[可选] 是否每次强制编译
],'test');

$template->assign('time', time());

$template->assign(['time2'=>microtime(true)]);

$template->assign('data',[['id'=>1,'name'=>'name1'],['id'=>2,'name'=>'name2']]);

$template->display('demo');

