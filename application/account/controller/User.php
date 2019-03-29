<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/28
 * Time: 16:41
 */

namespace app\account\controller;

use ItFarm\PhpSdk\Client;

class User
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

    public function getUserByPhone()
    {
        $params['phone'] = input('get.phone') ?: cutout(0, 'phone null');
        $json = myRequest('https://qa.epihealth.cn/index.php/api/User/getUserByPhone', 'GET', $params);
        $data = json_decode($json, true);
        if ($data['ret'] == 1) {
            $menses = $data['data']['menses'];
            $due_childbirth_date = date('Y-m-d', strtotime("{$menses} +280 day"));
            return json(['ret' => 1, 'msg' => 'success', 'data' => ['name' => $data['data']['name'], 'due_childbirth_date' => $due_childbirth_date]]);
        } else {
            return json(['ret' => 0, 'msg' => 'no user']);
        }
    }

    public function getUser()
    {
        $openid = input('get.openid') ?: cutout(0, 'openid null');

        $user_info = model('user')->findByOpenid($openid);

        if (empty($user_info[0])) {
            return json(['ret' => 0, 'message' => 'no user']);

        } else {
            $token = encodeJwt($user_info[0]['id']);
            return json(
                ['ret' => 1,
                    'data' => [
                        'id' => $user_info[0]['id'],
                        'name' => $user_info[0]['name'],
                        'nickname' => $user_info[0]['nickname'],
                        'phone' => $user_info[0]['phone'],
                        'avatar_url' => $user_info[0]['avatar_url'],
                        'due_childbirth_date'=>$user_info[0]['due_childbirth_date'],
                        'openid' => $user_info[0]['openid'],
                        'token' => $token
                    ]
                ]);
        }
    }

    public function register()
    {
        $phone = input('post.phone');
        $captcha = input('post.captcha');
        $password = config('password');
        $confirm_password = config('password');
        $appkey = config('appkey');
        $mold = input('post.mold') ?: cutout(0, 'mold null');

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

    public function completeInformation()
    {
        $params['openid'] = input('post.openid') ?: cutout(0, 'openid null');
        $phone = input('post.phone') ?: cutout(0, 'phone null');
        $params['name'] = input('post.name') ?: cutout(0, 'name null');
        $nickname = input('post.nickname') ?: cutout(0, 'nickname null');
        $params['nickname'] = emoji_reject($nickname);
        $params['avatar_url'] = input('post.avatar_url') ?: cutout(0, 'avatar_url null');
        $params['due_childbirth_date'] = input('post.due_childbirth_date') ?: cutout(0, 'due_childbirth_date null');
        $result = model('patient')->updateUser($phone, $params);
        if ($result) {
            return json(['ret' => 1, 'msg' => 'success']);
        } else {
            return json(['ret' => 0, 'msg' => 'failed']);
        }
    }

}