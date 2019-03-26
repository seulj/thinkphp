<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/26
 * Time: 17:29
 */

namespace app\index\model;


class Questionnaire
{
    public function getQuestionnaire()
    {
        return db('questionnaire')->select();
    }

}