<?php
/**
 * Created by PhpStorm.
 * User: guochao
 * Date: 2017/11/25
 * Time: 下午4:24
 */


//手机号验证
if (!function_exists('isMobile')) {
    function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }

        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,3,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}

//uuid 32位不重复字符
if (!function_exists('uuid')) {
    function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = $chars;

        return $uuid;
    }
}

//表情转字符串
if (!function_exists('emoji_to_string')) {
    function emoji_to_string($str)
    {
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
            return '\\';
        }, $text); //将两条斜杠变成一条，其他不动

        $data = json_decode($text);
        $data = "{$data}";
        return $data;
    }
}

//emoji表情转unicode
if (!function_exists('replace_emoji')) {
    function replace_emoji($str)
    {
        if (strlen($str) == 0) return "";

        $text = $str; //可能包含二进制emoji表情字符串
        $tmpStr = json_encode($text); //暴露出unicode

        $tmpStr = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, $tmpStr);
        $text = json_decode($tmpStr);

        return $text;
    }
}

//验证
if (!function_exists('verify')) {

    function verify($params, $signKey = '')
    {
        empty($signKey) && $signKey = VERIFY_KEY;
        ksort($params);
        $signString = urldecode(http_build_query($params, '&'));

        return md5($signString . $signKey);
    }
}