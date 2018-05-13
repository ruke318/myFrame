<?php
if (!function_exists('success')){
  /**
   * @desc  void 返回成功json数据
   * @param  array   $data      数据
   * @param  string  $msg       提示信息
   * @param  integer $code      [code码]
   * @param  integer $http_code [http状态码]
   * @return [type]             [json]
   */
  function success($data = [], $msg = 'success', $code = 1, $http_code = 200) {
    $data = ['data' => $data, 'msg'=>$msg, 'code'=>$code];
    header("Accept:application/json;charset=utf-8");
    header("Content-Type:application/json;charset=utf-8");
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    http_response_code($http_code);
    return json_encode($data,JSON_UNESCAPED_UNICODE);
  }
}

if (!function_exists('error')):
  /**
   * @desc 失败返回json
   * @param  string  $msg       [错误提示信息]
   * @param  integer $code      [错误码]
   * @param  integer $http_code [http状态码]
   * @param  array   $data      [数据]
   * @return [type]             [json]
   */
  function error($msg = 'request error', $code = -1, $http_code = 200, $data = []) {
    $data = ['data'=>$data, 'msg'=>$msg, 'code'=>$code];
    header("Accept:application/json;charset=utf-8");
    header("Content-Type:application/json;charset=utf-8");
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    http_response_code($http_code);
    return json_encode($data,JSON_UNESCAPED_UNICODE);
  }
endif;

function errorDie($msg = 'request error', $code = -1, $http_code = 200, $data = []) {
  $data = ['data'=>$data, 'msg'=>$msg, 'code'=>$code];
  header("Accept:application/json;charset=utf-8");
  header("Content-Type:application/json;charset=utf-8");
  header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
  http_response_code($http_code);
  echo json_encode($data,JSON_UNESCAPED_UNICODE);die;
}

if (!function_exists('input')):
  /**
   * @desc 获取request参数
   * @param  [type] $name [参数名字]
   * @return [type]       [description]
   */
  function input($name = null)
  {
    $str = new Lib\Request;
    if (is_null($name)) {
      return $str->all();
    }
    $args = explode('.', $name);
    $method = '';
    if (isset($args[1])) {
      list($method, $name) = $args;
    }
    switch($method) {
      case 'get':
        return $str->get($name);
      case 'post':
        return $str->post($name);
      case 'header':
        return $str->header($name);
      default:
        return $str->$name;
    }
  }
endif;