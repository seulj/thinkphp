<?php
use Firebase\JWT\JWT;

if (!function_exists('xurl')) {
    function xurl($string = '')
    {
        return '//' . $_SERVER['HTTP_HOST'] . '/' . $string;
    }
}

if (!function_exists('myRequest')) {
    function myRequest($url, $type, $data = false, $header = array(), &$err_msg = null, $timeout = 20, $cert_info = array())
    {
        $type = strtoupper($type);
        if ($type == 'GET' && is_array($data)) {
            $data = http_build_query($data);
        }
        $option = array();
        if ($type == 'POST') {
            $option[CURLOPT_POST] = 1;
        }
        if ($data) {
            if ($type == 'POST') {
                $option[CURLOPT_POSTFIELDS] = $data;
            } elseif ($type == 'GET') {
                $url = strpos($url, '?') !== false ? $url . '&' . $data : $url . '?' . $data;
            }
        }
        $option[CURLOPT_URL] = $url;
        $option[CURLOPT_FOLLOWLOCATION] = true;
        $option[CURLOPT_MAXREDIRS] = 4;
        $option[CURLOPT_RETURNTRANSFER] = true;
        $option[CURLOPT_TIMEOUT] = $timeout;
        $option[CURLOPT_USERAGENT] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36";
        //设置证书信息
        if (!empty($cert_info) && !empty($cert_info['cert_file'])) {
            $option[CURLOPT_SSLCERT] = $cert_info['cert_file'];
            $option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
            $option[CURLOPT_SSLCERTTYPE] = $cert_info['cert_type'];
        }
        //设置CA
        if (!empty($cert_info['ca_file'])) {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            $option[CURLOPT_SSL_VERIFYPEER] = 1;
            $option[CURLOPT_CAINFO] = $cert_info['ca_file'];
        } else {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            $option[CURLOPT_SSL_VERIFYPEER] = 0;
            $option[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        $ch = curl_init();

        $option[CURLOPT_HTTPHEADER] = $header;

        curl_setopt_array($ch, $option);
        $response = curl_exec($ch);
        $curl_no = curl_errno($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        // error_log
        if ($curl_no > 0) {
            if ($err_msg !== null) {
                $err_msg = '(' . $curl_no . ')' . $curl_err;
            }
        }
        return $response;
    }
}

// 剔除emoji表情 (3个字节的emoji无法剔除, 比如讯飞输入法的emoji表情)
if (!function_exists('emoji_reject')) {
    function emoji_reject($text)
    {
        $len = mb_strlen($text);
        $new_text = '';
        for ($i = 0; $i < $len; $i++) {
            $word = mb_substr($text, $i, 1);
            if (strlen($word) <= 3) {
                $new_text .= $word;
            }
        }
        return $new_text;
    }
}

// 是否包含emoji表情
if (!function_exists('emoji_test')) {
    function emoji_test($text)
    {
        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $word = mb_substr($text, $i, 1);
            if (strlen($word) > 3) {
                return true;
            }
        }
        return false;
    }
}

// 输出emoji表情的16进制字符串
if (!function_exists('emoji_print')) {
    function emoji_print($emoji)
    {
        $len = mb_strlen($emoji);
        $txt = '';
        for ($i = 0; $i < $len; $i++) {
            $hex = mb_substr($emoji, $i, 1);
            $txt .= strtolower(str_replace('%', '\x', urlencode($hex))) . "\r\n";
        }
        echo $txt;
    }
}

if (!function_exists('encrypt')) {
    /**
     * md5盐值加密
     * @param string
     * @return string
     */
    function encrypt($string, $salt = 'KISS')
    {
        return md5(md5($string) . $salt);
    }
}

if (!function_exists('get_file')) {
    function get_file($path)
    {
        $file = fopen($path, "r");
        $str = trim(fread($file, filesize($path)));
        fclose($file);
        return $str;
    }
}

if (!function_exists('sec2time')) {
    function sec2time($seconds)
    {
        $result = '0分0秒';
        if ($seconds > 0) {
            $hour = floor($seconds / 3600);
            $minute = floor(($seconds - 3600 * $hour) / 60);
            $second = floor((($seconds - 3600 * $hour) - 60 * $minute) % 60);
            $result = $minute . '分' . $second . '秒';
        }
        return $result;
    }
}

if (!function_exists('cutout')) {
    function cutout($ret = 0, $message = null)
    {
        echo json_encode(['ret' => $ret, 'message' =>$message]);
        die();
    }
}

if (!function_exists('encodeJwt')) {
    function encodeJwt($user_id)
    {
        $key = config('jwt_key');
        $token = array(
            'user_id' => $user_id
        );

        $jwt = JWT::encode($token, $key);
        return $jwt;
    }
}

if (!function_exists('decodeJwt')) {
    function decodeJwt($jwt)
    {
        $key = config('jwt_key');
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        return $decoded;
    }
}

if (!function_exists('verifyJwt')) {
    function verifyJwt($token)
    {
        try {
            $content = decodeJwt($token);
        } catch (\Exception $e) {
            cutout(0,'token error');
        }
    }
}
if (!function_exists('setCross')) {
    function setCross()
    {
        header('Access-Control-Allow-Origin: *');
    }
}