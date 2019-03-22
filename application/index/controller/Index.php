<?php

namespace app\index\controller;

use think\Session;
use think\Url;

url::root('/index.php');

class Index extends \think\Controller
{
    public function index()
    {
        $result = Session::has('phone');
        if (!$result) {
            $this->redirect("index/login");
            exit();
        }
        $data['users_count'] = model('user')->getCount();
        $data['contents_count'] = model('content')->getCount();
        return view('seu/index', $data);
    }

    public function login()
    {
        return view('seu/login');
    }

    public function doLogin()
    {
        $phone = input('post.phone');
        $password = input('post.password');
        $result = model('admin')->getUer($phone, $password);
        if ($result) {
            Session::set('phone', $result['phone']);
            return json(['ret' => 1, 'message' => 'success']);
        } else {
            return json(['ret' => 0, 'message' => 'failed']);
        }
    }

    public function doLogout()
    {
        Session::delete('phone');
        $this->redirect("index/login");
    }
}
