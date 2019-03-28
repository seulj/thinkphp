<?php
/**
 * Created by PhpStorm.
 * User: Zhou Miao
 * Date: 2018/3/27
 * Time: 17:04
 */
require __DIR__ . '/../vendor/autoload.php';

use ItFarm\PhpSdk\Client;
use ItFarm\PhpSdk\UploadFile;

//获取client实例
$client = Client::getInstance();

//****** 中台部署的项目 ******//
// TODO

//****** 非中台部署的项目 ******//
$client->setServices([
    'upload' => 'http://ubf1819eae6ec16c-dev.oneitfarm.com/', // 服务名称=>服务访问地址
    'face-detector' => 'https://api.oneitfarm.com/face-detector/',
]);
$client->setAppkey('nf2p7cm6hrmsf5g8y9nsqzyucieizxj3');
$client->setAppsecret('bu42vlgnqmpdfoiyfmcktghwnpjzd59b');
$uploadFile = new UploadFile();
$uploadFile->filePath = 'D:\1';
$uploadFile->fileName = "avatar.png";
$res = $client->uploadFile('upload', '/index.php/Upload/doUploadPictures', ['pictures' => [$uploadFile, $uploadFile]], ['appkey' => 'nf2p7cm6hrmsf5g8y9nsqzyucieizxj3', 'channel' => 0]);

// 以用户身份调用接口

