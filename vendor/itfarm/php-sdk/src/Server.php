<?php

namespace ItFarm\PhpSdk;

use Lcobucci\JWT\Parser;

class Server
{
    private static $instance = null;

    private $token = null;

    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    public function __construct()
    {
        try {
            $token = Token::getBeareToken();
            if ($token) {
                $token = (new Parser())->parse((string)$token);
                $this->token = $token;
            }
        } catch (\InvalidArgumentException $e) {
        }
    }

    /**
     * 返回token中所有数据
     * @return array|null
     */
    public function getTokenData()
    {
        if ($this->token) {
            return $this->token->getClaims();
        }

        return null;
    }

    /**
     * 获取请求token中的appkey
     * @return mixed|null
     */
    public function getAppkey()
    {
        if ($this->token) {
            return $this->token->getClaim('appkey');
        }

        return null;
    }

    /**
     * 获取请求token中的channel
     * @return mixed|null
     */
    public function getChannel()
    {
        if ($this->token) {
            return $this->token->getClaim('channel');
        }

        return null;
    }

    /**
     * 获取请求token中的account_id
     * @return mixed|null
     */
    public function getAccountID()
    {
        if ($this->token) {
            return $this->token->getClaim('account_id');
        }

        return null;
    }
}