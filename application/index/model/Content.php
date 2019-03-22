<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/21
 * Time: 15:01
 */

namespace app\index\model;


class Content
{
    public function getByPage($page, $limit)
    {
        $query = db('content')->page($page, $limit)->select();
        return $query;
    }

    public function getPage()
    {
        $query = db('content')->count();
        $page = ceil($query / config('paginate')['list_rows']);
        return $page;
    }

    public function getContent($id)
    {
        return db('content')->where('id', $id)->find();
    }

    public function deleteContent($id)
    {
        return db('content')->where('id', $id)->delete();
    }

    public function editContent($id, $params)
    {
        return db('content')->where('id', $id)->update($params);
    }

    public function insertContent($params)
    {
        return db('content')->insert($params);
    }

    public function getCount()
    {
        return db('content')->count();
    }
}