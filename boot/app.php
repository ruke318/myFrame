<?php

use think\Db;

header("charset=utf-8");
ini_set('always_populate_raw_post_data', '-1');
date_default_timezone_set('Asia/Shanghai');

// composer 启动文件
include ROOT . '/vendor/autoload.php';

// 自己的调试
DeBug::register();

// 想容器中注册服务
Di\Di::register([
    'file' => \Di\Cache\File::class,
    'redis' => \Di\Cache\Redis::class,
    'cache' => \Di\Cache::class
]);

// tp的orm
Db::setConfig(\Lib\Config::get('database'));

// 引入路由文件
Route::group(['namespace' => 'App\Controllers'], function () {
    include ROOT . '/route/web.php';
});

// 分发路由
Route::dispatch();
