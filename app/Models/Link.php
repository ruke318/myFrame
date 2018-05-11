<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/30
 * Time: 15:49
 */
namespace App\Models;

use think\Model;

class Link extends Model
{
    protected $table = 'links';

    public $append = ['testKey'];

    public function getTestKeyAttr($name, $item)
    {
        return $item['title'];
    }
}