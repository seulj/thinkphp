<?php
/**
* Created by PhpStorm.
 * User: Uasier
* Date: 2018/12/13
* Time: 9:35
*/

namespace ItFarm\PhpSdk\Header;

//抽象构件角色
interface Header{
    public function getHeader();
    public function addHeader();
}