<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 17:45
 * Desc: [文件描述]
 */

Route::group(['middleware' => 'TestMiddleware'], function () {
    Route::get('test/{id:\d+}', 'TestController@getIndex');
});
Route::controller('/test', 'TestController');

