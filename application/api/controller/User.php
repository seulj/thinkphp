<?php

namespace app\api\controller;


class User
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
    }

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

    public function login()
    {
        $params['openid'] = input('post.openid') ?: cutout(0, 'openid null');
        $nickname = input('post.nickname') ?: cutout(0, 'nickname null');
        $params['nickname'] = emoji_reject($nickname);
        $params['avatar_url'] = input('post.avatar_url') ?: cutout(0, 'avatar_url null');

        $result = model('user')->findByOpenid($params['openid']);
        if ($result) {
            $token = encodeJwt($result['id']);
            return json(['ret' => 1, 'data' => [
                'id' => $result['id'],
                'nickname' => $params['nickname'],
                'avatar_url' => $params['avatar_url'],
                'openid' => $params['openid'],
                'token' => $token,
            ]]);
        }else{
            $res = model('user')->register($params);
            if ($res){
                $token = encodeJwt($res);
                return json(['ret' => 1, 'data' => [
                    'id' => $res,
                    'nickname' => $params['nickname'],
                    'avatar_url' => $params['avatar_url'],
                    'openid' => $params['openid'],
                    'token' => $token,
                ]]);
            }else{
                return json(['ret' => 0, 'message' => 'failed']);
            }
        }
    }
}
