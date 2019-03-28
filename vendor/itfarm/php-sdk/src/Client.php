<?php
/**
 * 中台服务调用SDK，适用场景
 * 1. 中台开发的应用，通过中台部署服务进行部署
 * 2. 第三方开发的应用，自行部署
 * 参数中 appkey 和 token 为关键字，可能会被自动覆盖
 */

namespace ItFarm\PhpSdk;

use ItFarm\PhpSdk\TracerLog\SimpleHttpLogger;
use Cienv\Cienv;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Log\LoggerInterface;
use whitemerry\phpkin\Tracer;
use whitemerry\phpkin\Endpoint;
use whitemerry\phpkin\Span;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Logger\Logger as TracerLogInterface;

class Client
{
    /**
     * @var Client $instance 单例
     * @var array $services 服务
     * @var int $connect_timeout 连接超时时间
     * @var int $timeout 请求超时时间
     * @var int $concurrency 请求并发数
     * @var string $token token值
     * @var string $appkey appkey
     * @var string $appsecret appsecret
     * @var LoggerInterface $logRecorder 日志记录变量
     * @var bool $needTracer 是否需要进行调用链追踪//默认为false
     * @var string $spanId 调用链追踪块id
     * @var SimpleHttpLogger $zipkinlogger zipkin调用链接收对象
     * @var Tracer $tracer zipkin调用链对象
     * @var array $request 发送请求
     * @var array $response 返回信息
     */
    private static $instance = null;
    private $services = [];
    private $connect_timeout = Constant::CONNECT_TIMEOUT;
    private $timeout = Constant::TIMEOUT;
    private $concurrency = Constant::DEFAULT_CONCURRENCY;
    private $token = '';
    private $appkey = '';
    private $appsecret = '';
    private $logRecorder = null;
    private $needTracer = false;
    private $spanId = '';
    private $zipkinlogger = null;
    private $tracer = null;
    private $request = array();
    private $response = array();


//

    /**
     * 构造函数
     * Client constructor.
     */
    public function __construct()
    {
        if (Cienv::getEnv('appkey')) {
            $this->appkey = Cienv::getEnv('appkey');
        }
        if (Cienv::getEnv('appsecret')) {
            $this->appsecret = Cienv::getEnv('appsecret');
        }
        $this->token = Token::getBeareToken();

        if (!empty($_SERVER['HTTP_X_B3_NEEDTRACER'])) {
            $this->needTracer = true;
        }
    }

//* @var Client $instance 单例
    /**
     * 单例模式（一次初始化，多次操作）
     * @return Client|null
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

//* @var array $services 服务
    /**
     * 设置services
     * @param $services
     * @return $this
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

//* @var int $connect_timeout 连接超时时间
    /**
     * 设置请求超时时间，不包含连接时间
     * @param $timeout integer 超时时间，单位毫秒
     * @return $this
     */
    public function setConnectTimeout($timeout)
    {
        if (is_numeric($timeout)) {
            $this->connect_timeout = $timeout / 1000;
        }
        return $this;
    }

//* @var int $timeout 请求超时时间
    /**
     * 设置请求超时时间，不包含连接时间
     * @param $timeout integer 超时时间，单位毫秒
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if (is_numeric($timeout)) {
            $this->timeout = $timeout / 1000;
        }
        return $this;
    }

//* @var int $concurrency 请求并发数
    /**
     * 设置并发数量
     * @param $num integer 并发数，默认5，注意不要设置过大，有限制
     * @return $this
     */
    public function setConcurrency($num)
    {
        if (is_numeric($num) && $num > 0 && $num < Constant::MAX_CONCURRENCY) {
            $this->concurrency = $num;
        }
        return $this;
    }

//* @var string $token token值
    /**
     * 设置token
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

//* @var string $appkey appkey
    /**
     * 设置appkey
     * @param $appkey
     * @return $this
     */
    public function setAppkey($appkey)
    {
        $this->appkey = $appkey;
        return $this;
    }

//* @var string $appsecret appsecret
    /**
     * 设置Appsecret
     * @param $appsecret
     * @return $this
     */
    public function setAppsecret($appsecret)
    {
        $this->appsecret = $appsecret;
        return $this;
    }

//* @var LoggerInterface $logRecorder 日志记录变量
    /**
     * 设置logRecorder
     * @param LoggerInterface $logger
     * @return $this
     * @throws \Exception
     */
    public function setLogRecorder(LoggerInterface $logger)
    {
        if (!$logger instanceof LoggerInterface) {
            throw new \Exception('The logRecorder should implement LoggerInterface according to Psr3 standard', 1000);
        }
        $this->logRecorder = $logger;
        return $this;
    }



//     * @var bool $needTracer 是否需要进行调用链追踪//默认为false

    /**
     * @如果一个调用链的初始调用被开启了，那么后面所有的调用都必须是开启的
     * @param bool $needTracer
     * @return $this
     */
    public function setNeedTracer($needTracer)
    {
        if (!empty($_SERVER['HTTP_X_B3_NEEDTRACER'])) {
            $this->needTracer = true;
        }else{
            $this->needTracer = $needTracer;
        }
        return $this;
    }

//* @var SimpleHttpLogger $zipkinlogger zipkin调用链接收对象

    /**
     * @param TracerLogInterface $tracerLogger
     * @return $this
     * @throws \Exception
     */
    public function setZipkinlogger(TracerLogInterface $tracerLogger)
    {
        if (!$tracerLogger instanceof TracerLogInterface) {
            throw new \Exception('The tracerLogger should implement TracerLogInterface', 1000);
        }
        $this->zipkinlogger = $tracerLogger;
        return $this;
    }

//* @var string $spanId 调用链追踪块id
    public function getSpandId()
    {
        return $this->spanId;
    }
//* @var array $request 发送请求

//* @var array $response 返回信息


    /**
     * 以app的身份发送请求(第一次发起请求)
     * @param string $service_name
     * @param string $method
     * @param string $api
     * @param null $data
     * @param string $content_type
     * @return array
     */
    public function callAsApp($service_name, $method, $api, $data = null, $content_type = Constant::CONTENT_TYPE_FORM)
    {
        if (!$this->appkey || !$this->appsecret) {
            return $this->logPipeline(['state' => 1101, 'msg' => 'appkey or appsecret must set']);
        }

        $this->_makeToken();
        $data === null && $data = [];
        $data = array_merge($data, ['appkey' => $this->appkey]);

        try {
            return $this->exec($service_name, $method, $api, $data, $content_type);
        } catch (\Exception $exception) {
            return $this->logPipeline(['state' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }

    /**
     * 直接请求服务(后续服务内发起请求)
     * @param string $service_name
     * @param string $method
     * @param string $api
     * @param null $data
     * @param string $content_type
     * @return array
     */
    public function call(
        $service_name,
        $method,
        $api,
        $data = null,
        $content_type = Constant::CONTENT_TYPE_FORM
    )
    {
        if (!$this->appkey) {
            return $this->logPipeline(['state' => 1101, 'msg' => 'appkey must set']);
        }
        $data === null && $data = [];
        $data = array_merge($data, ['appkey' => $this->appkey]);

        try {
            return $this->logPipeline($this->exec($service_name, $method, $api, $data, $content_type));
        } catch (\Exception $exception) {
            return $this->logPipeline(['state' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }

    /**
     * makeToken
     */
    private function _makeToken()
    {
        $signer = new Sha256();
        $this->token = (new Builder())->setIssuer($this->appkey)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + 60)// 一分钟过期
            ->set('appkey', $this->appkey)
            ->sign($signer, $this->appsecret)
            ->getToken();
    }


    /**
     * 发起接口调用
     * @param $service_name
     * @param $method
     * @param $api
     * @param null $data
     * @param string $content_type
     * @return array
     * @throws \Exception
     */
    public function exec($service_name, $method, $api, $data = null, $content_type = Constant::CONTENT_TYPE_FORM)
    {
        //zipkin
        if ($this->needTracer){

            $this->zipkinlogger || $this->zipkinlogger = new SimpleHttpLogger(['host' => 'http://192.168.3.214:9411', 'muteErrors' => false]);
            //采用定义常量的方式来拿到当前服务名
            $endpoint = new Endpoint(SERVICENAME, Func::getIp(), Func::getPort());

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
            $this->tracer = new Tracer(SERVICECALL, $endpoint, $this->zipkinlogger, $isSampled, $traceId, $traceSpanId);
            $this->tracer->setProfile(Tracer::BACKEND);

            $requestStart = Func::zipkin_timestamp();
            $this->spanId = new SpanIdentifier();
        }
        //zipkin
        $res = $this->checkParam($service_name, $method, $api, $data, $content_type);
        if ($res['state'] != 1) {
            return $res;
        }
        $options = $this->getGuzzleOptions($data, $content_type);

        $this->request = array(
            'url' => $api,
            'method' => $method,
            'options' => $options,
        );

        try {
            $guzzleClient = new GuzzleClient(['base_uri' => $res['base_uri']]);
            $this->response = $guzzleClient->request($method, $api, $options);
//zipkin
            if ($this->needTracer){
                // 为此请求设置zipkin数据
                $endpoint = new Endpoint($service_name, Func::getIp(), Func::getPort());//获取服务的ip:port
                if (isset($requestStart)){
                    $annotationBlock = new AnnotationBlock($endpoint, $requestStart);
                    $span = new Span($this->spanId, $method.'  '.$api, $annotationBlock);
                    // 添加跨度spans到Zipkin
                    $this->tracer->addSpan($span);
                    //发送trace
                    $this->tracer->trace();
                }else{
                    throw new \Exception('$requestStart null', 1000);
                }
            }
//zipkin

            return $this->parseResponse($this->response);
        } catch (ConnectException $e) { // 网络连接失败
            return $this->connectError($e);
        } catch (RequestException $e) { // 返回非200状态码
            return $this->requestError($e);
        } catch (GuzzleException $e) {  // 其他错误
            return $this->unknownError($e);
        }
    }

    /**
     * 参数验证 验证curl相关参数是否合法$service_name, $method, $api, $data, $content_type
     * @param string $service_name
     * @param string $method
     * @param string $api
     * @param array $data
     * @param string $content_type
     * @return array
     */
    private function checkParam($service_name, $method, $api, $data, $content_type)
    {
        /**
         * 当从环境变量取service时，通过网关调用服务
         */
        if (!$this->services) {//中台
            if (!Cienv::getEnv('services')) {
                return $this->logPipeline(['state' => 1404, 'msg' => 'services should in environment']);
            }

            try {
                $this->services = \GuzzleHttp\json_decode(base64_decode(Cienv::getEnv('services')), true);
                if (!$this->services[$service_name]) {
                    return $this->logPipeline(['state' => 1403, 'msg' => 'service not set']);
                }
            } catch (\InvalidArgumentException $e) {
                return $this->logPipeline(['state' => 1402, 'msg' => 'services should be json format']);
            }
        } else {//非中台
            if (!$this->services[$service_name]) {
                return $this->logPipeline(['state' => 1403, 'msg' => 'service not set']);
            }
        }

        $base_uri = $this->services[$service_name];

        $method = strtolower($method);
        if (!in_array($method, Constant::ALLOW_METHODS)) {
            return $this->logPipeline(['state' => 1001, 'msg' => 'method not allowed']);
        }

        $api = ltrim($api, '/');
        if (!is_string($api) || !$api) {
            return $this->logPipeline(['state' => 1002, 'msg' => 'api should start with \'/\'']);
        }

        if ($data && !is_array($data)) {
            return $this->logPipeline(['state' => 1004, 'msg' => 'data should be array']);
        }

        if (!in_array($content_type, [Constant::CONTENT_TYPE_FORM, Constant::CONTENT_TYPE_JSON, Constant::CONTENT_TYPE_MULTIPART])) { // add multipart support
            return $this->logPipeline([
                'state' => 1005,
                'msg' => 'content_type should be ' . Constant::CONTENT_TYPE_FORM . ' or ' . Constant::CONTENT_TYPE_JSON . 'or' . Constant::CONTENT_TYPE_MULTIPART,
            ]);
        }

        return ['state' => 1, 'base_uri' => $base_uri];
    }

    /**
     * 配置Guzzle参数条件
     * @param $data
     * @param $content_type
     * @return array
     * @throws \Exception
     */
    private function getGuzzleOptions($data, $content_type)
    {
        $headers = [
            'User-Agent' => Constant::USER_AGENT . '/' . Constant::VERSION,
            'Accept' => 'application/json',
        ];

        //zipkin
        // 用于传递标头的上下文（B3 传播）
        if ($this->needTracer) {
            $headerObj = new Header\GuzzleHeader();
            try {
                $headerObj = new Header\TracerHeader($headerObj, [$this->spanId]);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            array_merge($headers, $headerObj->getHeader());
        }
        //zipkin

        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }
        if ($this->appkey) {
            $headers['ITFARM_APPKEY'] = $this->appkey;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $headers[] = 'X-FORWARDED-FOR:' . $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $headers[] = 'X-FORWARDED-PROTO:' . $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        if (isset($_SERVER['HTTP_FRONT_END_HTTPS'])) {
            $headers[] = 'FRONT-END-HTTPS:' . $_SERVER['HTTP_FRONT_END_HTTPS'];
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'] . ' ' . $headers['User-Agent'];
        }

        $options = [
            'connect_timeout' => $this->connect_timeout,
            'timeout' => $this->timeout,
            'allow_redirects' => false,
            'headers' => $headers,
        ];

        if ($data) {
            if ($content_type == Constant::CONTENT_TYPE_JSON) {
                $options['json'] = $data;
            } else {
                $options['form_params'] = $data;
            }
        }

        return $options;
    }

    /**
     * 解析response，给出统一的返回值
     * @param $response Response response对象
     * @return array
     */
    private function parseResponse(Response $response)
    {
        $http_code = $response->getStatusCode();
        $body = (string)$response->getBody();
        $this->response = array(
            'http_code' => $http_code,
            'response_body' => $body
        );
        try {
            $result = \GuzzleHttp\json_decode($body, true);
        } catch (\InvalidArgumentException $e) {
            return $this->logPipeline([
                'state' => 1301,
                'msg' => 'invalid json format',
                'origin_body' => $body,
            ]);
        }
        return $this->logPipeline(array_merge(['state' => 1], $result));
    }

    /**
     * 连接失败异常处理
     * @param $e ConnectException 连接错误异常
     * @return array
     */
    private function connectError(ConnectException $e)
    {
        $context = $e->getHandlerContext();
        return $this->logPipeline([
            'state' => 1101,
            'msg' => 'connect failed: ' . $e->getMessage(),
            'error' => $context['error'],
            'errno' => $context['errno'],
        ]);
    }

    /**
     * http非200异常处理
     * @param $e RequestException 请求异常
     * @return array
     */
    private function requestError(RequestException $e)
    {
        if (!$e->hasResponse()) {
            return $this->logPipeline([
                'state' => 1201,
                'msg' => 'request failed: ' . $e->getMessage(),
            ]);
        }

        $http_code = $e->getResponse()->getStatusCode();
        $body = (string)$e->getResponse()->getBody();
        $this->response = array(
            'http_code' => $http_code,
            'response_body' => $body
        );

        switch ($http_code) {
            case 401:
                return $this->logPipeline([
                    'state' => 1202,
                    'msg' => 'Unauthorized',
                    'http_code' => $http_code,
                    'origin_body' => $body,
                ]);
            case 403:
                return $this->logPipeline([
                    'state' => 1204,
                    'msg' => 'No permission',
                    'http_code' => $http_code,
                    'origin_body' => $body
                ]);
            case 404:
                return $this->logPipeline([
                        'state' => 1203,
                        'msg' => 'api not exist',
                        'http_code' => $http_code,
                        'origin_body' => $body,
                    ]
                );
            default:
                return $this->logPipeline([
                    'state' => 1299,
                    'msg' => 'error response: ' . $e->getMessage(),
                    'http_code' => $http_code,
                    'origin_body' => $body,
                ]);
        }
    }

    /**
     * 处理未知错误
     * @param \Exception| GuzzleException $e
     * @return array
     */
    private function unknownError(\Exception $e)
    {
        return $this->logPipeline([
            'state' => 1401,
            'msg' => 'unknown Error: ' . $e->getMessage(),
        ]);
    }

    /**
     * 记录日志
     * @param $data = ['state'(must), 'msg'(optional), 'data'(optional)]
     * @return mixed
     */
    private function logPipeline($data)
    {
        if ($data['state'] == 1) {
            return $data;
        }

        $logLevel = (array_key_exists('http_code', $data) && ($data['http_code'] != 200)) ? 'error' : 'warning';
        $logLevel = (array_key_exists('errno', $data)) ? 'error' : $logLevel;

        $message = array(
            'code' => $data['state'],
            'context' => $data['msg'],
            'request' => $this->request,
            'response' => $this->response,
        );
        $message = json_encode($message);

        // 如果logRecorder没有设置，那么默认使用\Cxj\Logger
        if ($this->logRecorder === null) {
            $this->logRecorder = new \ItFarm\SysLog\Logger('', $logLevel);
        }

        switch ($logLevel) {
            case 'error':
                $this->logRecorder->error('ERROR: Error' . $message, ['code' => $data['state'], 'context' => $data['msg']]);
                break;
            default:
                $this->logRecorder->warning('WARNING: Warning' . $message, ['code' => $data['state'], 'context' => $data['msg']]);
                break;
        }
        return $data;
    }

    /**
     * @param $service_name
     * @param $api
     * @param $files
     * @param null $data
     * @return array|mixed
     * @throws \Exception
     */
    public function uploadFile($service_name, $api, $files, $data = null)
    {
        if (!$files || !is_array($files)) {
            return $this->logPipeline(['state' => 1501, 'msg' => 'No file input or file is not an array']);
        }
        $res = $this->checkParam($service_name, 'post', $api, $data, Constant::CONTENT_TYPE_MULTIPART);
        if ($res['state'] != 1) {
            return $res;
        }
        if (!$this->token) $this->_makeToken(); // 如果是服务调用直接使用header里的token,如果是应用调用重新生成token
        $options = $this->getGuzzleOptions(null, Constant::CONTENT_TYPE_MULTIPART); // Init empty options

        $options['multipart'] = [];

        $data = array_merge($data, ['appkey' => $this->appkey]);
        if ($data) {
            foreach ($data as $key => $object) {
                $options['multipart'][] = ['name' => $key, 'contents' => $object];
            }
        }
        foreach ($files as $name => $file) {
            $keyName = is_array($file) ? $name . '[]' : $name; // if is array of file add [] to post name
            $uploadFiles = is_array($file) ? $file : [$file];
            foreach ($uploadFiles as $singleFile) {
                if (!$singleFile instanceof UploadFile) {
                    return $this->logPipeline(['state' => 1502, 'msg' => 'File is not instance of \Itfarm\PhpSdk\UploadFile Class']);
                }
                /** @var UploadFile $singleFile */
                $uploadParam = [];
                $singleFile->fileName && $uploadParam['filename'] = $singleFile->fileName;
                $uploadParam['name'] = $keyName;
                $uploadParam['contents'] = fopen($singleFile->filePath, 'r');
                $options['multipart'][] = $uploadParam;
            }
        }
        $this->request = array(
            'url' => $api,
            'method' => 'post',
            'options' => $options,
        );

        try {
            $guzzleClient = new GuzzleClient(['base_uri' => $res['base_uri']]);
            $this->response = $guzzleClient->request('post', $api, $options);
            return $this->parseResponse($this->response);
        } catch (ConnectException $e) { // 网络连接失败
            return $this->connectError($e);
        } catch (RequestException $e) { // 返回非200状态码
            return $this->requestError($e);
        } catch (GuzzleException $e) {  // 其他错误
            return $this->unknownError($e);
        }
    }

}
