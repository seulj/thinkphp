<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2018/10/24
 * Time: 16:53
 */

namespace app\account\model;


class Patient
{
    public function findPatient($phone)
    {
        return db('user')->where('phone', $phone)->find();
    }

    public function register($data)
    {
        return db('user')->insertGetId($data);
    }

    public function getByAccountId($account_id)
    {
        return db('user')->where('account_id', $account_id)->find();
    }

    public function updateUser($phone, $params)
    {
        return db('user')->where('phone', $phone)->update($params);
    }

    public function updateAccountId($phone, $account_id)
    {
        return db('user')->where('phone', $phone)->update(['account_id' => $account_id]);
    }

    public function findByOpenid($openid)
    {
        return db('user')->where(array('openid' => $openid))->order('id desc')->select();
    }

}