<?php
/**
 * Created by PhpStorm.
 * User: Zhou Miao
 * Date: 2018/3/27
 * Time: 17:04
 */
require __DIR__ . '/../vendor/autoload.php';

use ItFarm\PhpSdk\Client;

//获取client实例
$client = Client::getInstance();

//****** 中台部署的项目 ******//
// TODO

//****** 非中台部署的项目 ******//
$client->setServices([
    'account' => 'http://idg-prod.tunnel.zhoumiao.com/account/', // 服务名称=>服务访问地址
    'face-detector' => 'https://api.oneitfarm.com/face-detector/',
]);
$client->setAppkey('nf2p7cm6hrmsf5g8y9nsqzyucieizxj3');
$client->setAppsecret('bu42vlgnqmpdfoiyfmcktghwnpjzd59b');

// 以用户身份调用接口
echo "\n设备id登录\n";
$res = $client->call('account', 'POST', 'main.php/json/login/device', [
    'deviceid' => '456',
]);
var_dump($res);

echo "\n当前登录用户\n";
$client->setToken($res['token']);
$res = $client->call('account', 'POST', 'main.php/json/login/loginAccount');
var_dump($res);

echo "\n手机号注册\n";
$res = $client->call('account', 'POST', 'main.php/json/register/phone', [
    'phone' => '13888888888',
    'captcha' => 8888, //开发环境，固定为8888
    'password' => '123456',
    'confirm_password' => '123456',
]);
var_dump($res);

echo "\n手机号登录\n";
$res = $client->call('account', 'POST', 'main.php/json/login/phone', [
    'phone' => '13888888888',
    'captcha' => 8888,
]);
var_dump($res);

// 以应用身份调用接口
$res = $client->callAsApp('account', 'POST', 'main.php/rpc/info/phone.json', [
    'phone' => '13888888888',
]);
var_dump($res);

$res = $client->callAsApp('face-detector', 'POST', 'apis/v1/quality_detect', []);
var_dump($res);
