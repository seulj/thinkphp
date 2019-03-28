<?php
require __DIR__ . '/../vendor/autoload.php';

use ItFarm\PhpSdk\Client;
use ItFarm\PhpSdk\TracerLog\SimpleHttpLogger;

define('SERVICENAME', 'TestService');//当前服务的服务名
define('SERVICECALL', 'TestApi');//当前服务的服务名


//****** 非中台部署的项目 ******//
//获取client实例
$client = Client::getInstance();
try {
    $client->setNeedTracer(true)//设置是否需要调用链跟踪，默认不需要，一旦设置调用链追踪，整个调用链此值全部为需要，无法修改
    ->setZipkinlogger(new SimpleHttpLogger(['host' => 'http://192.168.3.214:9411', 'muteErrors' => false]))//设置调用链跟踪zipkin数据处理方式，interface  whitemerry\phpkin\Logger\Logger
    ->setServices(['Service' => 'http://api.juheapi.com/',])// 服务名称=>服务访问地址
    ->setAppkey('nf2p7cm6hrmsf5g8y9nsqzyucieizxj3')//设置appkey
    ->setAppsecret('bu42vlgnqmpdfoiyfmcktghwnpjzd59b');
} catch (Exception $e) {
}//设置appsecret

$res = $client->callAsApp('Service',
    'POST',
    'japi/toh',
    [
        'key' => '63ff8bcf4d10ff2540a7fcde616c0a13',
        'v' => '1.0',
        'month' => '11',
        'day' => '11',
    ]);
var_dump($res);









