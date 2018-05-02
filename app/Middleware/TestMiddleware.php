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
    public function handle($request, Closure $next, $type) {
        //前置中间件
        $before = $request->get('before');
        if ($before) {
            $request->hh = 'tt';
        }

        $response = $next($request);

        $ret = json_decode($response);
        $ret->test = 'middleware1';
        return json_encode($ret);
    }
}