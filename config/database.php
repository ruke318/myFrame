<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/30
 * Time: 13:19
 */
//return [
//    'driver'    => 'mysql',
//    'host'      => 'localhost',
//    'database'  => 'crh',
//    'username'  => 'root',
//    'password'  => 'root',
//    'charset'   => 'utf8',
//    'collation' => 'utf8_general_ci',
//    'prefix'    => ''
//];

return [
    // 数据库类型
    'type'        => 'mysql',
    // 服务器地址
    'hostname'    => '127.0.0.1',
    // 数据库名
    'database'    => 'crh',
    // 数据库用户名
    'username'    => 'root',
    // 数据库密码
    'password'    => 'root',
    // 数据库连接端口
    'hostport'    => '3306',
    // 数据库连接参数
    'params'      => [],
    // 数据库编码默认采用utf8
    'charset'     => 'utf8',
    'paginate' => [
        'pageVar' => 'page',
        'size' => 15
    ]
];