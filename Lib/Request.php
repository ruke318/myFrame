<?php
namespace Lib;

class Request
{
    private $params;
    private $postParams;
    private $getParams;

    public function __construct() {
        $body = @file_get_contents('php://input');
        if ($body) {
            $body = json_decode($body, 1);
        }
        $this->params = array_merge($_REQUEST, empty($body) ? [] : $body);
        $this->postParams = array_merge($_POST, empty($body) ? [] : $body);
        $this->getParams = array_merge($_GET, empty($body) ? [] : $body);
    }

    public function __get($key) {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    public function __set($key, $value) {
        $this->params[$key] = $value;
    }

    public function get($key = null) {
        return empty($key) ? $this->getParams : (isset($this->getParams[$key]) ? $this->getParams[$key] : null);
    }

    public function post($key = null) {
        return empty($key) ? $this->postParams : (isset($this->postParams[$key]) ? $this->postParams[$key] : null);
    }

    public function all() {
        return isset($this->params) ? $this->params : [];
    }

    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isMethod($method) {
        return strtolower($method) === $this->method();
    }

    public function header($string = null){
        $headers = array();
        foreach($_SERVER as $key=>$value){
            if(substr($key, 0, 5)==='HTTP_'){
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }
        return $string ? $headers[$string] : $headers;
    }

    public function url() {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    public function log($path, $data, $append = true) {
        $path = trim($path);
        if (!file_exists(dirname(ROOT.'/logs/'.$path))) {
            mkdir(dirname(ROOT.'/logs/'.$path), 777, true);
        }
        if ($append)
            file_put_contents(ROOT.'/logs/'.$path, '['.date('Y-m-d H:i:s').'] '.strtoupper($this->method()).'- '.$data.PHP_EOL, FILE_APPEND);
        else
            file_put_contents(ROOT.'/logs/'.$path, '['.date('Y-m-d H:i:s').'] '.strtoupper($this->method()).'- '.$data.PHP_EOL);
    }

    public function showRoute()
    {
        return $this->isMethod('get');
    }
}