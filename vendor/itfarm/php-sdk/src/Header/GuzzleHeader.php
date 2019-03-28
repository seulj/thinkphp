<?php
/**
 * Created by PhpStorm.
 * User: Uasier
 * Date: 2018/12/13
 * Time: 9:36
 */

namespace ItFarm\PhpSdk\Header;

use ItFarm\PhpSdk\Constant;

// 具体构件角色
class GuzzleHeader implements Header{
    private $header;
    public function __construct()
    {
        $this->header = [
            'User-Agent' => Constant::USER_AGENT . '/' . Constant::VERSION,
            'Accept' => 'application/json',
            ];
    }
    public function getHeader(){
        return $this->header;
    }
    public function addHeader(){
        return $this->header;
    }
}