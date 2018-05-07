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
use Illuminate\Database\Capsule\Manager as DB;

class TestController
{
    public function getIndex(Request $request, $id)
    {
        $config = DB::table('links')->orderBy('id', 'desc')->get();
        return success($config);
    }

    public function getTest(Request $request) {
        $info = Link::find($request->id);
        return success($info);
    }
}