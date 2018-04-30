<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 17:43
 * Desc: [文件描述]
 */
class Action
{

    function __construct () {
        $str = $_SERVER['PHP_SELF'];
        if (strpos($str, '/index.php') !== false) {
            $str = explode('index.php', $str)[1];
        }
        $ret = explode('/', $str);
        $ret = array_filter($ret, function ($item) {
            return !empty($item) && $item != 'index.php';
        });
        if (empty($ret)) {
            echo '<h1 style="font-size:90px;color:#679ad1;height:80vh;display:flex;justify-content:center;align-items:center">PHPCr</h1>';die;
        }
        if (count($ret) < 2) {
            return error('不存在的路由', -1, 404);
        }
        $fun = ucfirst(array_pop($ret));
        $ctr = array_pop($ret);
        $url = implode('\\', $ret);
        $class = 'Ctr\\'.($url ? $url.'\\' : '').ucfirst($ctr).'Controller';
        $string = new Fun\Str;
        $fun = $string->method().$fun;
        // file_exists
        if (!file_exists(ROOT.'\\'.$class.'.php')) {
            $string->log(date('Y-m-d').'_error.log', $class.'类不存在!');
            return error($class.'类不存在', -1, 404);
        } else {
            if (!method_exists($class, $fun)) {
                $string->log(date('Y-m-d').'_error.log', $class.'\\'.$fun.'方法不存在');
                return error($class.'\\'.$fun.'方法不存在', -1, 404);
            }
        }
        $t = new $class();
        $t->$fun();
    }
}

new Action;