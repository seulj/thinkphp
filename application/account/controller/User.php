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

    public function getUser()
    {
        $params['openid'] = input('get.openid') ?: cutout(0, 'openid null');
        $json = myRequest('https://qa.epihealth.cn/index.php/api/User/getUser', 'GET', $params);
        $data = json_decode($json, true);
        return json($data);
    }

}