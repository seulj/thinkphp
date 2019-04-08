<?php
/**
 * Created by PhpStorm.
 * User: Jamin
 * Date: 2019/4/8
 * Time: 9:31
 */

namespace app\api\controller;
use pic;

class Picture
{
    public function uploadPic()
    {
        $picture = $_FILES['file']['tmp_name'];
        $pic = new pic\UploadPic();
        try {
            $res = $pic->execApi('upload/pic', '', 'post', array('file' => $picture));
            if ($res['state'] == 1) {
                return json(['ret' => 1, 'data' => $res['data']['url']]);
            } else {
                throw new Exception($res['msg'], -1);
            }
        } catch (Exception $ex) {
            return json(['ret' => 0, 'data' => $ex]);
        }
    }
}