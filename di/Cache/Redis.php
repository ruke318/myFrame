<?php
namespace Di\Cache;

class Redis implements CacheInterface
{
    protected $_di;

    protected $_options;

    public function __construct($options = null)
    {
        $this->_options = $options;
    }

    public function setDI($di)
    {
        $this->_di = $di;
    }

    public function get($key)
    {
        return 'redis'.$key;
    }

    public function add($key, $value, $min)
    {
        // code
    }

    public function forget($key)
    {
        // code
    }

    public function has($key)
    {

    }
}