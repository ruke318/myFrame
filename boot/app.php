<?php
use Illuminate\Database\Capsule\Manager as Capsule;

header("charset=utf-8");
ini_set('always_populate_raw_post_data', '-1');
date_default_timezone_set('Asia/Shanghai');

// autoload
include ROOT.'/vendor/autoload.php';

spl_autoload_register(function ($func) {
    require $func . '.php';
});

//debug register
DeBug::register();

//add service to di
Di\Di::register([
    'file' => \Di\Cache\File::class,
    'redis' => \Di\Cache\Redis::class,
    'cache' => \Di\Cache::class
]);

// Eloquent ORM
$capsule = new Capsule;
$capsule->addConnection(\Lib\Config::get('database'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

//引入路由文件
include ROOT.'/route/web.php';

//分发路由
Route::dispatch();