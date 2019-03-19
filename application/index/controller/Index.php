<?php
namespace app\index\controller;
use think\Session;
class Index
{
    public function index()
    {
        return view('seu/index');
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
}
