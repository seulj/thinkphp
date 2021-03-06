<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2018/10/24
 * Time: 16:53
 */

namespace app\account\model;


class Doctor
{
    public function findDoctor($phone)
    {
        return db('doctor')->where('phone', $phone)->find();
    }

    public function register($data)
    {
        return db('doctor')->insertGetId($data);
    }

    public function getByAccountId($account_id)
    {
        return db('doctor')->where('account_id', $account_id)->find();
    }

    public function updateInformation($account_id, $params)
    {
        return db('doctor')->where('account_id', $account_id)->update($params);
    }

}