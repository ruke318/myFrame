<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 11:27
 * Desc: [文件描述]
 */
class Bug
{

    public static function register() {
        error_reporting(0);
        set_exception_handler([__CLASS__, 'exception']);
        set_error_handler([__CLASS__, 'error']);
        register_shutdown_function([__CLASS__, 'fatal']);
    }

    public static function error (...$e) {
        self::report($e);
    }

    public static function fatal () {
        $e = error_get_last();
        //发送警报
        self::report((object) $e);
    }

    public static function exception ($e) {
        self::report($e);
    }

    public static function report($e) {
        if (empty((array) $e)) {
            return;
        }
        if (is_array($e)) {
            $ret = $e[1].' in '.$e[2].':'.$e[3].'['.$e[0].']';
        } else {
            if ($e instanceof Exception) {
                $ret = $e->getMessage().' in '.$e->getFile().':'.$e->getLine().'['.$e->getCode().']';
            } else {
                $ret = $e->message.' in '.$e->file.':'.$e->line.'['.$e->type.']';
            }
        }
        return error($ret);
    }
}

Bug::register();