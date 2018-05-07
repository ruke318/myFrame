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

# config

读取配置文件的类

读取的文件是根目录下的`config.php`,返回的是数组

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

#### 获取配置

通过`Face\Config`类操作

```php
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

#### request

对于将`request`的一些参数,放置到了`BaseController`中,这个实际上是实例化了类`Fun\String`

```php
$this->req 就能访问
```

通过`__get`和`__set`魔术方法来将参数直接绑定到对象上

> 例如

```
 GET http://host/user/list?name=test&status=3
```

得到

```php
$this->req->name  === test
$this->req->status === 3
//但是,对于特殊参数,比如已经在类中存在的,我们需要另一种方式

//get 参数
$this->req->get('name') === test
//所有get参数
$this->req->get() === ['name'=>'test', 'status'=>'3']

//post 参数,包括表单和body体参数
$this->req->post($key) === 获取指定key的post参数
//所有post参数
$this->req->post()

//获取get,post参数合集
$this->req->all();

//获取header参数
$this->req->header($key)  //不传key获取所有header参数

//获取当前url
$this->req->url()

//获取当前请求的事件
$this->req->method()

//判断当前事件
$this->req->isMethod($method)

//写日志
$this->req->log($filename, $data, $append)
```

# func.php

函数文件

目前只有两个函数

```php
  function success($data = [], $msg = 'success', $code = 1, $http_code = 200) {
    $data = ['data' => $data, 'msg'=>$msg, 'code'=>$code];
    header("Content-type:application/json;charset=utf-8");
    http_response_code($http_code);
    echo json_encode($data,JSON_UNESCAPED_UNICODE);die;
  }

  function error($msg = 'request error', $code = -1, $http_code = 200, $data = []) {
    $data = ['data'=>$data, 'msg'=>$msg, 'code'=>$code];
    http_response_code($http_code);
    echo json_encode($data,JSON_UNESCAPED_UNICODE);die;
  }
```
# RedisDB

将系统自带的`Redis`封装成了单例模式

```php
use Face\RedisDB;

class UserController
{
  function getIndex()
  {
    $redis = RedisDB::start();
  }
}
```

# DB

数据库操作类,目前只有`mysql`

全部的操作基于`PDO`类的预处理,下面是一些简单操作,mysql的初始配置在`config.php`中的`mysql`

```php
  'mysql' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'pwd' => 'root',
    'dbname' => 'test'
  ]
```

可以通过`Face\Config`类来修改,或者`DB`的`DB::setConfig`修改

```php
Config::set('mysql', array)

DB::setConfig(array)

//都是需要在使用前修改
```


### 新增-- insert

返回值是最后一条的id
```php
// 添加一条数据
DB::table('cate')->insert(['name'=>'test', 'isTop'=>-1]);

//添加多条数据

DB::table('cate')->insert([
    ['name'=>'test1', 'isTop'=>1],
    ['name'=>'test2', 'isTop'=>-1],
    ...
  ])
// 注意每条数据结构必须相同
```

### 删除-- delete

```php
DB::table('cate')->where('id', '>', 1)->delete();
//不允许无条件删除,要全表删除可使用query
```

### 修改-- update

```php
DB::table('cate')->where('id', 1)->update(['name'=>'edit']);
//同样不允许无条件更新
```

### 过滤-- create

主要是为了方便修改和插入时可直接将整个数组放进去,过滤掉不需要的数据

```php
DB::table('cate')->create()->insert($this->req->all());
//这个是会进行过滤的
```

### 条件-- where, orWhere

```php
#当where或orWhere传入三个参数时,拼接的sql
where `title` like '%t%'
# orWhere
or `content` like '%t%'
DB::table('cate')->where('title', 'like', '%t%')->orWhere('content', 'like', '%t%')->get();

#当where或orWhere只有2个参数时
where id = 2
DB::table('cate')->where('id', 2)->delete();

可以满足的操作
where('title', 'like', '%t') not like
where('title', '<>', 'a')
where('id', 'in', '(1,2,3)') not in

# where 和 orWhere暂时还不全,这需要慢慢完善,待续
```

### 设置查询字段--  select

```php
//不这是默认为*
DB::table('cate')->select('id', 'name')->get();

```

### 查询多条-- get

```php
DB::table('cate')->get();
```

### 查询单条-- first

```php
DB::table('cate')->groupBy('id desc')->first();
```

### 获取多条中某个字段的值 -- pluck

```php
DB::table('cate')->pluck('name');
```

### 获取单条中某字段的数据--   value

```php
DB::table('cate')->value('name');
```

### 直接执行sql--  query

```php
DB::query('select * from cate where id > 4');
#支持预处理
DB::query('select * from cate where id > ?', [4]);
```

### limit 
```
DB::table('posts')->where('id', '>', 1)->limit(8)->get(); //取前8条

DB::table('posts')->where('id', '>', 1)->limit(4, 8)->get(); //跳过4条取前8条

```

### page-- page
```
DB::table('posts')->where('id', '>', 1)->page();

DB::table('posts')->where('id', '>', 1)->page($pageSize, $page);

这个方法较为特殊,虽然page可以传参数,但是不建议传参
调用参数 > URL query参数 > 默认值

也就是说我们可以在请求的url中设置分页参数

http://host/test/a?page=1&pageSize=12

等同于

DB::table('posts')->where('id', '>', 1)->page(12, 1);
```

### 其他

```php
# 排序 -orderBy

DB::table('cate')->orderBy('addTime')->orderBy('name')->get();
DB::table('cate')->orderBy('addTime desc')->get();

# 分组 -groupBy

DB::table('cate')->groupBy('cateId')->get();
```


## 更多东西后续补全,实现一个垃圾包