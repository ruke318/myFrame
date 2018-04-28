<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 11:33
 * Desc: [文件描述]
 */

class Route
{
    /**
     * @var array
     * 存放所有路由
     */
    private static $routes = [];

    /**
     * @var array
     * 允许的请求方式
     */
    private static $allowMethods = ['get', 'post', 'put', 'delete', 'patch', 'options'];

    /**
     * @param $method 请求方式
     * @param $path url
     * @param $call 回调
     * 添加路由
     */
    private static function addRoute($method, $path, $call)
    {
        $paths = explode('/', trim($path, '/'));
        $match = [];
        $paths = array_map(function ($item, $index) use ($path, &$match) {
            preg_match('/^\{(\S+)\}$/', $item, $ret);
            $ret = array_filter($ret);
            if ($ret) {
                $regx = '\S+';
                $list = explode(':', $ret[1]);
                $param = $list[0];
                if (isset($list[1])) {
                    $regx = $list[1];
                    if (empty($regx)) {
                        return error('check this route :`' . $path . '`');
                    }
                }
                $match[$param] = $index;
                return $regx;
            }
            return $item;
        }, $paths, array_keys($paths));
        $path = '/' . implode('/', $paths);
        if (!empty(self::$routes) && isset(self::$routes[$method]) && array_key_exists($path, self::$routes[$method] ?: [])) {
            return error('[' . $method . '] `' . $path . '` has been exists !');
        } else {
            self::$routes[$method][$path] = ['route' => $call, 'match' => $match];
        }
    }

    /**
     * @param $path url
     * @param $controller 对应控制器
     * 添加 controller 类型路由
     */
    public static function controller($path, $controller)
    {
        if (!class_exists($controller)) {
            return error('class `' . $controller . '` is not exists');
        }
        // get all public methods
        $funcs = get_class_methods($controller);
        //将每一个方法名字转成蛇形命名，获取第一个请求方式
        foreach ($funcs as $func) {
            $snakeFunc = strtolower(preg_replace('/([A-Z])/', '_$1', $func));
            $list = explode('_', $snakeFunc);
            $method = isset($list[0]) ? $list[0] : '';
            if (in_array($method, self::$allowMethods)) {
                array_shift($list);
                self::addRoute($method, $path . '/' . implode('_', $list), $controller . '@' . $func);
            }
        }

    }

    /**
     * @param $method 请求方式
     * @param $arguments 参数
     * 将 ::method映射到 addRoute
     */
    public static function __callStatic($method, $arguments)
    {
        $method = strtolower($method);;
        if (in_array($method, self::$allowMethods)) {
            self::addRoute($method, ...$arguments);
        } else {
            return error('not allow method ' . $method);
        }
    }

    /**
     * @return array
     * 查询所有路由
     */
    public static function routes()
    {
        return self::$routes;
    }

    /**
     * @return mixed|void
     * 分发注册的路由
     */
    public static function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        if (empty(self::$routes) || !isset(self::$routes[$method])) {
            return;
        }
        foreach (self::$routes[$method] as $path => $route) {
            if (preg_match('#^' . $path . '$#', $uri, $ret)) {
                $match = isset($route['match']) ? $route['match'] : [];
                $args = [];
                if (!empty($match)) {
                    $nPath = ltrim($uri, '/');
                    $paths = explode('/', $nPath);
                    foreach ($match as $param => $m) {
                        $args[$param] = $paths[$m];
                    }
                }
                $call = $route['route'];
                if (is_object($call)) {
                    return call_user_func($call, ...array_values($args));
                } else {
                    list($controller, $fun) = explode('@', $call);
                    if (!class_exists($controller)) {
                        return error('`class` ' . $controller . ' is not exists !', -1, 404);
                    }
                    if (!method_exists($controller, $fun)) {
                        return error('method `' . $fun . '` in class `' . $controller . '` is not exists', -1, 404);
                    }

                    $request = new \Lib\Request;
                    foreach ($args as $key => $value) {
                        $request->$key = $value;
                    }
                    $args = array_values($args);
                    array_unshift($args, $request);
                    return call_user_func_array([new $controller, $fun], $args);
                }
            }
        }
        return error('route `' . $uri . '` is not exists !', -1, 404);
    }
}
