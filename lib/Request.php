<?php
namespace Lib;

class Request
{
    /**
     * @var array
     * 所有参数
     */
    private $params;

    /**
     * @var array
     * post参数
     */
    private $postParams;

    /**
     * @var array
     * get参数
     */
    private $getParams;

    /**
     * Request constructor.
     * 初始化绑定参数
     */
    public function __construct()
    {
        $body = @file_get_contents('php://input');
        if ($body) {
            $data = json_decode($body, 1);
            if (!$data) {
                parse_str($body, $data);
            }
            $body = $data;
        }
        $this->params = array_merge($_REQUEST, empty($body) ? [] : $body);
        $this->postParams = array_merge($_POST, empty($body) ? [] : $body);
        $this->getParams = array_merge($_GET, empty($body) ? [] : $body);
    }

    /**
     * @param $key
     * @return mixed|null
     * 设置 __get 返回params中参数
     */
    public function __get($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     * 添加一个值
     */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     * 获取get参数, 参数为空获取所有get参数
     */
    public function get($key = null)
    {
        return empty($key) ? $this->getParams : (isset($this->getParams[$key]) ? $this->getParams[$key] : null);
    }

    /**
     * @param null $key
     * @return array|mixed|null
     * 获取 post 参数
     */
    public function post($key = null)
    {
        return empty($key) ? $this->postParams : (isset($this->postParams[$key]) ? $this->postParams[$key] : null);
    }

    /**
     * @return array
     * 获取所有参数
     */
    public function all()
    {
        return isset($this->params) ? $this->params : [];
    }

    /**
     * @return string
     * 返回当前请求方式
     */
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @param $method
     * @return bool
     * 是否 method
     */
    public function isMethod($method)
    {
        return strtolower($method) === $this->method();
    }

    /**
     * @param null $string
     * @return array|mixed
     * 获取header参数
     */
    public function header($string = null)
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }
        return !empty($string) ? (array_key_exists($string, $headers) ? $headers[$string] : null) : $headers;
    }

    /**
     * @return string
     * 获取当前uri
     */
    public function url()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @param $path
     * @param $data
     * @param bool $append
     * 记录日志
     */
    public function log($path, $data, $append = true)
    {
        $path = trim($path);
        if (!file_exists(dirname(ROOT . '/logs/' . $path))) {
            mkdir(dirname(ROOT . '/logs/' . $path), 777, true);
        }
        if ($append)
            file_put_contents(ROOT . '/logs/' . $path, '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($this->method()) . '- ' . $data . PHP_EOL, FILE_APPEND);
        else
            file_put_contents(ROOT . '/logs/' . $path, '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($this->method()) . '- ' . $data . PHP_EOL);
    }

    /**
     * @return null|string
     * 返回客户端ip
     */
    public function ip()
    {
        $realip = null;
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        return $realip;
    }

}