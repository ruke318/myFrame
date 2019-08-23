### 瓦力部署测试222, 看回滚

# PHPCr

这个是我自己用来写`api`的,只有一些简单的功能
## 路由 (模仿laravel的样子, 但实际上...)


目前有的方法有 `get`, `post`, `put`, `delete`, `options`, `patch`, `controller`, `group`

 #### 普通请求

 ```php
 Route::get('/test', 'TestController@getIndex');
 Route::post('/test', function () {
    return 123;
 });
 //适应正则匹配
 Route::put('/test/{id:\d+}', 'TestController@putIndex');
......
```

#### controller

```php
Route::controller('/test', 'TestController');
//控制器中的方法 getXXX, postXXX, deleteXXX ... ...
```

#### group

```php
//App\Controllers\TestController\getIndex
Route::group(['namespace' => 'App\Controllers'], function () {
    Route::get('test', 'TestController@getIndex');
});

// host/v4/test
Route::group(['prefix' => 'v4'], function () {
    Route::post('test', 'App\Controllers\TestController@postIndex');
});

// 中间件
Route::group(['middleware' => 'TestMiddleware'], function () {
    Route::controller('/test', 'App\Controllers\TestController');
});

// 可以单个都加
Route::group(['middleware' => 'TestMiddleware', 'namespace' => 'App\Controllers', 'prefix' => 'prefix'], function () {
    Route::controller('test', 'TestController');
});

控制器的话, path 参数和Request被注入到控制器方法中
 Route::put('/test/{id:\d+}', 'TestController@putIndex');

use Lib\Request;

class TestController
{
    public function putIndex(Request $request, $id)
    {
        return success($request->id === $id);
    }
}
```

# 中间件(这样子和laravel一样, 但实现..)

`app\Middleware`

```php
namespace App\MiddleWare;

use Closure;

class TestMiddleWare
{
    public function handle($request, Closure $next) {
        //前置中间件
        $before = $request->get('before');
        if ($before) {
            $request->hh = 'tt';
        }

        $response = $next($request);
        // 后置操作
        $ret = json_decode($response);
        $ret->test = 'middleware1';
        return json_encode($ret);
    }
}

```

## config

读取配置文件的类

读取的文件是根目录下的`config`目录下的文件,返回的是数组

```php
<?php
return [
	//redis 配置
	'redis' => [
		'host' => '192.168.199.206',
		'port' => 5302,
		'pwd'	 => null
	]
];
```

#### 获取配置 (和laravel也很像...)

通过`Lib\Config`类操作

```php
// file.option
Config::get('redis');
['host'=>'127.0.0.1', 'port'=> 5302, ....]
```

可以通过`.`操作符获取层级中配置

```php
Config::get('redis.host');
'127.0.0.1'
```

#### 临时设置配置

```php
Config::set(key, value)

Config::set('redis', ['host'=>'10.10.10.1', 'port'=>'123','pwd'=>'pass']);

#也可以用`.`来标识层级关系

Config::set('redis.pwd', 'pass');
```

### ORM --由于速度太慢,换成了tp5的orm

使用了第三方的ORM的包, laravel用的也是这个包

```php
namespace App\Models;

use think\Model;

class Link extends Model
{
    protected $table = 'links';
}
```

### 使用

```php
Link::find(4);
```

### DB -- 由于速度原因,替换成tp5的Db

```php
namespace App\Controllers;

use App\Models\Link;
use Lib\Request;
use think\Db;

class TestController
{
    public function getIndex(Request $request, $id = null)
    {
        $info = LinK::find(26);
        return success($info);
    }

    public function getTest(Request $request) {
        $info = Db::table('links')->find(3);
        return success($info);
    }
}
```

## 更多东西后续补全,实现一个垃圾包
