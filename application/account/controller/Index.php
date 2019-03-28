<?php

namespace app\account\controller;

use ItFarm\PhpSdk\Client;

class Index
{
    public function getOpenid()
    {
        $params['js_code'] = input('get.js_code') ?: cutout(0, 'js_code null');

        $params['appid'] = config('weChat')['appid'];
        $params['secret'] = config('weChat')['secret'];
        $params['grant_type'] = config('weChat')['grant_type'];

        $json = myRequest('https://api.weixin.qq.com/sns/jscode2session', 'GET', $params);

        $data = json_decode($json, true);

        if (isset($data['errcode'])) {
            return json(['ret' => 0, 'message' => 'null']);
        } else {
            return json(['ret' => 1, 'data' => ['openid' => $data['openid']]]);
        }
    }

    public function getCaptcha()
    {
        $phone = input('post.phone');
        $appkey = config('appkey');

        $client = new Client();
        $client->setAppkey($appkey);
        $client->setServices([
            'account' => config('account')
        ]);
        $post_captcha = array("phone" => $phone, "appkey" => $appkey);
        $response = $client->call('account', 'post', 'main.php/json/captcha/phone', $post_captcha);

        if ($response["state"] == 1) {
            return json(['ret' => 1, 'data' => $response]);
        } else
            return json(['ret' => 0, 'data' => $response]);
    }

    public function getUser()
    {
        $params['phone'] = input('get.phone');
    }

    public function login()
    {
        $phone = input('post.phone');
        $password = input('post.password');
        $appkey = config('appkey');
        $mold = input('post.mold');

        $client = new Client();
        $client->setAppkey($appkey);
        $client->setServices([
            'account' => config('account')
        ]);
        $post = array("phone" => $phone, "password" => $password, "appkey" => $appkey);
        $response = $client->call('account', 'post', 'main.php/json/login/phone', $post);

        if ($response["state"] == 1) {
            $data['phone'] = $phone;
            $data['account_id'] = $response['account_id'];
            if ($mold == 'doctor') {
                $res = model('patient')->findPatient($phone);
                if (!$res) {
                    $result = model('doctor')->findDoctor($phone);
                    if (!$result) {
                        $content = model('doctor')->register($data);
                        if ($content) {
                            $response['doctor_id'] = $content;
                            return json(['ret' => 1, 'data' => $response]);
                        } else {
                            return json(['ret' => 0, 'message' => 'failed']);
                        }
                    } else {
                        $response['doctor_id'] = $result['id'];
                        return json(['ret' => 1, 'data' => $response]);
                    }
                } else {
                    return json(['ret' => 0, 'data' => ['msg' => '请不要用患者账号登录医生端']]);
                }
            }
            if ($mold == 'patient') {
                $res = model('doctor')->findDoctor($phone);
                if (!$res) {
                    $result = model('patient')->findPatient($phone);
                    if (!$result) {
                        $content = model('patient')->register($data);
                        if ($content) {
                            $response['patient_id'] = $content;
                            return json(['ret' => 1, 'data' => $response]);
                        } else {
                            return json(['ret' => 0, 'message' => 'failed']);
                        }
                    } else {
                        model('patient')->updateAccountId($phone, $data['account_id']);
                        $response['patient_id'] = $result['id'];
                        return json(['ret' => 1, 'data' => $response]);
                    }
                } else {
                    return json(['ret' => 0, 'data' => ['msg' => '请不要用医生账号登录患者端']]);
                }
            }
        } else
            return json(['ret' => 0, 'data' => $response]);
    }

    public function register()
    {
        $phone = input('post.phone');
        $captcha = input('post.captcha');
        $password = config('password');
        $confirm_password = config('password');
        $appkey = config('appkey');
        $mold = input('post.mold');

        $client = new Client();
        $client->setAppkey($appkey);
        $client->setServices([
            'account' => config('account')
        ]);
        $post_register = array(
            'captcha' => $captcha,
            'password' => $password,
            'confirm_password' => $confirm_password,
            'appkey' => $appkey,
            'phone' => $phone
        );
        $response = $client->call('account', 'post', 'main.php/json/register/phone', $post_register);

        if ($response["state"] == 1) {
            $post_perfect = array(
                'appkey' => $appkey,
                'account_id' => $response['account_id'],
                'name' => '未设置'
            );
            $client = new Client();
            $client->setAppkey($appkey);
            $client->setServices([
                'ucenter' => config('ucenter')
            ]);
            $responses = $client->call('ucenter', 'post', 'main.php/json/user_info/register_user', $post_perfect);
            $data['phone'] = $phone;
            $data['account_id'] = $response['account_id'];
            if ($mold == 'doctor') {
                $res = model('doctor')->register($data);
                if ($res) {
                    return json(['ret' => 1, 'message' => 'success']);
                } else {
                    return json(['ret' => 0, 'message' => 'failed']);
                }
            }
            if ($mold == 'patient') {
                $res = model('patient')->register($data);
                if ($res) {
                    return json(['ret' => 1, 'message' => 'success']);
                } else {
                    return json(['ret' => 0, 'message' => 'failed']);
                }
            }
            return json(['ret' => 1, 'data' => $responses]);
        } else
            return json(['ret' => 0, 'data' => $response]);
    }

    public function forget()
    {
        $phone = input('post.phone');
        $captcha = input('post.captcha');
        $password = input('post.password');
        $userPwd = input('post.userPwd');
        $confirm_password = input('post.confirm_password');
        $appkey = config('appkey');

        if (!$password) {
            $password = $userPwd;
        }
        $client = new Client();
        $client->setAppkey($appkey);
        $client->setServices([
            'account' => config('account')
        ]);
        $post_register = array(
            'captcha' => $captcha,
            'password' => $password,
            'confirm_password' => $confirm_password,
            'appkey' => $appkey,
            'phone' => $phone
        );
        $response = $client->call('account', 'post', 'main.php/json/change_password/phone', $post_register);
        if ($response["state"] == 1) {
            return json(['ret' => 1, 'data' => $response]);
        } else
            return json(['ret' => 0, 'data' => $response]);
    }

    public function getStage($birth_date)
    {
        $endTime = strtotime($birth_date);
        $startTime = time();

        $diff = $endTime - $startTime;
        $day = $diff / 86400;

        if ($day > 190) {
            return "early";
        } else if ($day >= 85 && $day <= 190) {
            return "middle";
        } else if ($day < 85 && $day > 0) {
            return "late";
        } else {
            return "after";
        }
    }
}