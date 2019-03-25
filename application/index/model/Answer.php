<?php
/**
 * Created by PhpStorm.
 * User: 10202
 * Date: 2019/3/24
 * Time: 21:45
 */

namespace app\index\model;


class Answer
{
    public function getByPage($page, $limit)
    {
        $query = db('answer')->page($page, $limit)->select();
        return $query;
    }

    public function getPage()
    {
        $query = db('answer')->count();
        $page = ceil($query / config('paginate')['list_rows']);
        return $page;
    }

    public function getAnswer($id)
    {
        return db('answer')->where('id', $id)->find();
    }

}