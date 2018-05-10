<?php
/**
 * Created by HuaTu.
 * User: 陈仁焕
 * Email: ruke318@gmail.com
 * Date: 2018/4/28
 * Time: 18:26
 * Desc: [文件描述]
 */
namespace App\Controllers;

use App\Models\Link;
use Lib\Request;
use think\Db;

class TestController
{
    public function getIndex(Request $request, $id = null)
    {
        $info = LinK::find(26);
        return success(['info' => $info, 'ip' => $request->ip()]);
    }

    public function getTest(Request $request) {
        $info = Db::table('nav')->field('name, id')->simplePaginate(5);
        return success($info);
    }
}