<?php
/**
 * Created by PhpStorm.
 * User: Uasier
 * Date: 2018/12/13
 * Time: 11:36
 */

namespace ItFarm\PhpSdk;


class Func
{
    public static function getIp() {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        isset($ip) || $ip = '0.0.0.0';
        return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    }
    public static function getPort() {
        $port = $_SERVER['SERVER_PORT'];
        isset($port) || $port = '0';
        return $port;
    }

    /**
     * 为了解决windows环境下获取时间戳不正确。
     * @return string
     */
    public static function zipkin_timestamp()
    {
        $str = microtime();
        $fstr =  substr($str,11,10).substr($str,2,6);
        return $fstr;
    }
}
