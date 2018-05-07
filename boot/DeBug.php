<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 11:27
 * Desc: [文件描述]
 */
class DeBug
{

    /**
     * 注册 debug
     */
    public static function register() {
        error_reporting(0);
        set_exception_handler([__CLASS__, 'exception']);
        set_error_handler([__CLASS__, 'error']);
        register_shutdown_function([__CLASS__, 'fatal']);
    }

    /**
     * @param mixed ...$e
     * 截取 错误 处理
     */
    public static function error (...$e) {
        $error = [
            'message' => $e[1],
            'file' => $e[2],
            'line' => $e[3],
            'type' => $e[0]
        ];
        self::report($error);
    }

    /**
     * 截取程序中断错误
     */
    public static function fatal () {
        $e = error_get_last();
        //发送警报
        self::report((object) $e);
    }

    /**
     * @param $e
     * 获取异常错误
     */
    public static function exception ($e) {
        self::report($e);
    }

    /**
     * @param $e
     * 报告错误
     */
    public static function report($e) {
        if (is_array($e)) {
            $ret = $e['message'].' in '.$e['file'].':'.$e['line'].'['.$e['type'].']';
        } else {
            $ret = $e->getMessage().' in '.$e->getFile().':'.$e->getLine().'['.$e->getCode().']';
        }
        if (!json_encode($ret)) {
            echo $ret;die;
        } else {
            echo errorDie($ret);die;
        }
    }
}
