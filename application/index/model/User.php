<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/20
 * Time: 17:50
 */

namespace app\index\model;


class User
{
    public function getByPage($page, $limit)
    {
        $query = db('user')->page($page, $limit)->order('id desc')->select();
        return $query;
    }

    public function getPage()
    {
        $query = db('user')->count();
        $page = ceil($query / config('paginate')['list_rows']);
        return $page;
    }

}