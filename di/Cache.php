<?php
namespace Di;

class Cache
{
    protected $_di;

    protected $_options;

    protected $_connect;

    public function __construct($options = null)
    {
        $this->_options = $options;
        $this->_di = Di::start();
    }

    public function setDI($di)
    {
        $this->_di = $di;
    }

    public function setOption($options) {
    	$this->_options = $options;
    }

    protected function _connect()
    {
        $options = $this->_options;
        if (isset($options['connect'])) {
            $service = $options['connect'];
        } else {
            $service = 'redis';
        }

        return $this->_di->get($service);
    }

    public function __call($func, $args) {
    	$connect = $this->_connect;
    	if (!is_object($connect)) {
    		$connect = $this->_connect();
        $this->_connect = $connect;
    	}
    	return $connect->$func(...$args);
    }
}
