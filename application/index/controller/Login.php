<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/19
 * Time: 11:37
 */

namespace app\index\controller;


class Login
{
    public function index()
    {
        return view('seu/login');
    }

    public function doLogin()
    {
        $username = input('post.username');
        $password = input('post.password');
    }

}