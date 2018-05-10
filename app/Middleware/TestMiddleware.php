<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/5/2
 * Time: 16:00
 * Desc: [文件描述]
 */
namespace App\MiddleWare;

use Closure;

class TestMiddleWare
{
    public function handle($request, Closure $next)
    {
        $password = $request->header('password');
        if ('pass' != $password) {
            return error('miss password this request');
        }
        $response = $next($request);
        $ret = json_decode($response);
        $ret->data->info->id = (int)$request->id;
        return json_encode($ret);
    }
}