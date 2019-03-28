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
        return db('patient')->where('phone', $phone)->find();
    }

    public function register($data)
    {
        return db('patient')->insertGetId($data);
    }

    public function getByAccountId($account_id)
    {
        return db('patient')->where('account_id', $account_id)->find();
    }

    public function getDocument($phone)
    {
        return db('document')->where('phone', $phone)->find();
    }

    public function insertDocument($data)
    {
        return db('document')->insert($data);
    }

    public function updateInformation($phone, $params)
    {
        return db('document')->where('phone', $phone)->update($params);
    }

    public function updatePatient($phone, $name)
    {
        return db('patient')->where('phone', $phone)->update(['name' => $name]);
    }

    public function updateAccountId($phone, $account_id)
    {
        return db('patient')->where('phone', $phone)->update(['account_id' => $account_id]);
    }

}