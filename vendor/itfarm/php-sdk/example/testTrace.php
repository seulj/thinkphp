<?php
/**
 * Created by PhpStorm.
 * User: Uasier
 * Date: 2018/12/12
 * Time: 15:57
 */

namespace ItFarm\PhpSdk;

require_once '../src/Constant.php';
require_once '../vendor/autoload.php';

use ItFarm\PhpSdk\Header;
use whitemerry\phpkin\Tracer;
use whitemerry\phpkin\Endpoint;
use whitemerry\phpkin\Span;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Logger\SimpleHttpLogger;
use whitemerry\phpkin\TracerInfo;

$zipkinlogger = new SimpleHttpLogger(['host' => 'http://192.168.3.214:9411', 'muteErrors' => false]);
//采用定义常量的方式来拿到当前服务名
$endpoint = new Endpoint(SERVICENAME, '127.0.0.1', '80');

//traceId(32位或者64位)
$traceId = null;
if (!empty($_SERVER['HTTP_X_B3_TRACEID'])) {
    $traceId = new TraceIdentifier($_SERVER['HTTP_X_B3_TRACEID']);
}

$traceSpanId = null;
if (!empty($_SERVER['HTTP_X_B3_SPANID'])) {
    $traceSpanId = new SpanIdentifier($_SERVER['HTTP_X_B3_SPANID']);
}

$isSampled = null;
if (!empty($_SERVER['HTTP_X_B3_SAMPLED'])) {
    $isSampled = (bool) $_SERVER['HTTP_X_B3_SAMPLED'];
}

/**
 *  并创建跟踪器对象，如果您想要静态访问只是初始化TracerProxy
 *  TracerProxy :: init（$ tracer）;
 */
new Tracer(SERVICECALL, $endpoint, $zipkinlogger, $isSampled, $traceId, $traceSpanId);

// Test Case
//1.创建一个header
$headerObj = new Header\GuzzleHeader();

//2.加Trace
try {
    $headerObj = new Header\TracerHeader($headerObj);
} catch (\Exception $e) {
}

var_dump($headerObj->addHeader());