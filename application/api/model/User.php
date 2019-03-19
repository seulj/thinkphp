<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/18
 * Time: 17:41
 */

namespace app\api\model;


class User
{
    public function findByOpenid($openid)
    {
        return db('user')->where('openid',$openid)->find();
    }

    public function register($params)
    {
        return db('user')->insertGetId($params);
    }

}