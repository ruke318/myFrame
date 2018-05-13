<?php
namespace Lib;

class Config
{
    private static $config;
    private static $self;

    private function __construct($file)
    {
        self::$config = require_once ROOT . '/config/' . $file . '.php';
    }

    // 初始化Cinfig,保证只有一个Config
    private static function _init($file)
    {
        if (!self::$self) {
            self::$self = new self($file);
        }
    }

    /**
     * @desc 获取配置,层级之间可以使用`.`符号分割 eg: redis.host === ['redis']['host']
     * @param $key [string] 必须, 键值
     * @return [arr || string]
     */
    public static function get($key)
    {
        if (empty($key)) {
            return errorDie('请输入参数');
        }
        $keys = explode('.', $key);
        self::_init($keys[0]);
        array_shift($keys);
        return self::getValue(implode('.', $keys));
    }

    /**
     * @desc  暂时设置配置项的值,层级之间可以使用`.`符号分割 eg: redis.host === ['redis']['host']
     * @param [string] $key 项,必须
     * @param [mix] $value 值,必须
     */
    public static function set($key, $value)
    {
        if (!isset($key) || !isset($value)) {
            return errorDie('必须两个参数');
        }
        $keys = explode('.', $key);
        self::_init($keys[0]);
        array_shift($keys);
        self::setValue(implode('.', $keys), $value);
    }

    private static function getValue($key)
    {
        $arr = array_filter(explode('.', $key));
        $ret = self::$config;
        foreach ($arr as $a) {
            if (!array_key_exists($a, $ret)) {
                return errorDie("config['{$key}']" . '不存在');
            }
            $ret = $ret[$a];
        }
        return $ret;
    }

    private static function setValue($key, $value)
    {
        $arr = array_filter(explode('.', $key));
        $ret = &self::$config;
        foreach ($arr as $a) {
            if (!array_key_exists($a, $ret)) {
                return errorDie("config['{$key}']" . '不存在');
            }
            $ret = &$ret[$a];
        }
        $ret = $value;
    }
}