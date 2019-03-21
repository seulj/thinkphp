<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/21
 * Time: 15:00
 */

namespace app\index\controller;

use think\Session;
use think\Url;

url::root('/index.php');
class Content extends \think\Controller
{
    public function __construct()
    {
        parent::__construct();
        $result = Session::has('phone');
        if (!$result) {
            $this->redirect("index/login");
            exit();
        }
    }

    public function getList()
    {
        $page = input('page') ?: 1;
        $page = intval($page);
        $limit = config('paginate')['list_rows'];
        $data['list'] = model('content')->getByPage($page, $limit);
        $data['page'] = model('content')->getPage();
        if ($data['list']){
            return json(['ret' => 1, 'data' => $data]);
        }else
            return json(['ret' => 0, 'data' => 'end']);

    }

    public function index()
    {
        $page = input('page') ?: 1;
        $page = intval($page);
        $limit = config('paginate')['list_rows'];
        $data['list'] = model('content')->getByPage($page, $limit);
        $data['page'] = model('content')->getPage();

        if ($page > 1) {
            $data['previous_page'] = $page - 1;
        } else {
            $data['previous_page'] = 1;
        }
        if ($page < $data['page']) {
            $data['next_page'] = $page + 1;
        } else {
            $data['next_page'] = $page;
        }
        return view('seu/content', $data);
    }

}