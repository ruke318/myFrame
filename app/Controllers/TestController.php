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
use Di\Di;
use Lib\Request;
use think\Db;

class TestController
{
    public function getIndex(Request $request, $id = null)
    {
        return success(['info' => $id, 'ip' => $request->ip(), 'url' => $request->url()]);
    }

    public function getTest(Request $request) {
        return success(['s' => 'sš sss']);
        $info = Db::table('nav')->simplePaginate(1);
        return success($info);
    }

    public function postPage(Request $request)
    {
        $id = $request->get('id');
        $ret = $id ? Link::find($id) : Link::simplePaginate(3);
        return success($ret);
    }

    public function putTest(Request $request)
    {
        return success($request->all());
    }

    public function getFun(Request $request)
    {
        return success($request->all());
    }
}