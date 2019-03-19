<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/19
 * Time: 14:06
 */

namespace app\index\model;


class Admin
{
    public function getUer($phone,$password)
    {
        $password = encrypt($password);
        return db('user')->where(['phone' => $phone, 'password' => $password])->find();
    }

}