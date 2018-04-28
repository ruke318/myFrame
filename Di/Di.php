<?php
namespace Di;

class Di
{
    protected $_service = [];

    private static $di;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private static function _init()
    {
        if (!self::$di) {
            self::$di = new self;
        }
        return self::$di;
    }

    public static function start()
    {
        return self::_init();
    }

    public static function register($arr)
    {
        foreach ($arr as $name => $class) {
            self::set($name, $class);
        }
    }

    public static function set($name, $definition)
    {
        $di = self::_init();
        if (isset($di->_service[$name])) {
            throw new \Exception('`' . $name . '` service is exists!');
        }
        $di->_service[$name] = $definition;
    }

    public static function get($name)
    {
        $di = self::_init();
        if (isset($di->_service[$name])) {
            $definition = $di->_service[$name];
        } else {
            throw new \Exception('`' . $name . "` service is not exists");
        }

        if (is_callable($definition)) {
            $instance = call_user_func($definition);
        } else if (class_exists($definition)) {
            $instance = new $definition;
        }

        return $instance;
    }
}