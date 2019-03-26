<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/3/26
 * Time: 17:26
 */

namespace app\index\controller;


class Questionnaire
{
    public function getQuestionnaire()
    {
        $questionnaire = [];
        $result = model('questionnaire')->getQuestionnaire();
        if ($result) {
            for ($i = 0; $i < count($result); $i++) {
                array_push($questionnaire, $result[$i]['title']);
            }
            return json(['ret' => 1, 'data' => $questionnaire]);
        } else {
            return json(['ret' => 0, 'message' => 'get failed']);
        }
    }

}