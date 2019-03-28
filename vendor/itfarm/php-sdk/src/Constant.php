<?php

namespace ItFarm\PhpSdk;

class Constant
{
    /**
     * SDK 版本
     */
    const VERSION = '0.1.10';

    /**
     * UA标识
     */
    const USER_AGENT = 'ITFARM-PHP-CLIENT';

    /**
     * 最大并发数
     */
    const MAX_CONCURRENCY = 512;

    /**
     * 默认并发送
     */
    const DEFAULT_CONCURRENCY = 5;

    /**
     * 默认连接超时时间
     */
    const CONNECT_TIMEOUT = 2.0;

    /**
     * 默认请求超时时间
     */
    const TIMEOUT = 3.0;

    /**
     * 支持的请求方法
     */
    const ALLOW_METHODS = ['get', 'delete', 'head', 'options', 'patch', 'post', 'put'];

    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';
    const CONTENT_TYPE_JSON = 'application/json';

    const CONTENT_TYPE_MULTIPART = 'multipart/form-data';
}