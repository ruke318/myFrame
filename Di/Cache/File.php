<?php
namespace Di\Cache;

class File implements CacheInterface
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
        return 'file'.$key;
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