<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/21
 * Time: 15:01
 */

namespace app\api\controller;


class Content
{
    public function getList()
    {
        $page = input('page') ?: 1;
        $page = intval($page);
        $limit = config('paginate')['list_rows'];
        $data['list'] = model('content')->getByPage($page, $limit);
        $data['page'] = model('content')->getPage();
        if ($data['list']) {
            return json(['ret' => 1, 'data' => $data]);
        } else
            return json(['ret' => 0, 'data' => 'end']);
    }

}