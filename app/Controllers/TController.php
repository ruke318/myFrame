<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28
 * Time: 23:54
 */
namespace App\Controllers;

use Lib\Request;

class TController
{
    public function getIndex()
    {

    }

    public function postIndex()
    {
        echo 'postIndex';
    }

    public function getApiTest()
    {
        echo __METHOD__;
    }

    public function getABoyY(Request $request)
    {
        var_dump($request->all());
    }
}