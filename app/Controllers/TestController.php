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

use Lib\Request;
use Illuminate\Database\Capsule\Manager as DB;

class TestController
{
    public function getIndex(Request $request)
    {
        $id = $request->id;
        $list = DB::table('links')->find($id);
        return success($list);
    }
}