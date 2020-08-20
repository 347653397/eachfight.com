<?php

if (!function_exists('createNonceStr')) {
    function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

if (!function_exists('prize_random')) {
    function prize_random($ps)
    {
        static $arr = array();
        $key = md5(serialize($ps));

        if (!isset($arr[$key])) {
            $max = array_sum($ps);
            foreach ($ps as $k => $v) {
                $v = $v / $max * 10000;
                for ($i = 0; $i < $v; $i++) $arr[$key][] = $k;
            }
        }
        return $arr[$key][mt_rand(0, count($arr[$key]) - 1)];
    }
}

if (!function_exists('DD')) {
    function DD($arr)
    {
        echo "<pre>";
        print_r($arr);
        exit;
    }
}

if (!function_exists('getIP')) {
    function getIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

//主键生成
if (!function_exists('uuid')) {
    function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));

        $uuid = $chars;

        return $uuid;
    }
}

//获取当前用户能否查看动态
if (!function_exists('dongtai_can_show')) {
    function dongtai_can_show($current_user_is_god, $dongtai_target_user)
    {

        $is_show = "1";
        if ($dongtai_target_user == "2" && $current_user_is_god == "0") //给游神看的 但是当前用户不是游神
            $is_show = "0";
        else if ($dongtai_target_user == "3" && $current_user_is_god == "1")//给普通用户看的 但是当前用户是游神
            $is_show = "0";

        return $is_show;

    }
}

function milltime()
{
    //sleep(100);
    $time = explode(" ", microtime());
    $weixiao = $time[0];
    $haomiao = floatval($weixiao) * 1000;
    $haomiao = intval($haomiao);
    if ($haomiao < 100)
        $haomiao = "0" . $haomiao;
    $time = $time[1] . "" . $haomiao;
    //$time2 = explode (".", $time );
    //$time = $time2[0];

    return $time;
}

//补位函数 str原始字符串 len长度 msg补位字符 type 0=后补 1=前补
function dispRepair($str, $len, $msg, $type = '1')
{
    $length = $len - strlen($str);
    if ($length < 1)
        return $str;
    if ($type == 1) {
        $str = str_repeat($msg, $length) . $str;
    } else {
        $str .= str_repeat($msg, $length);
    }
    return $str;
}

//生成优惠券码
function GenerateCouponCode()
{
    $CI = &get_instance();
    $coupon_code_key = "YPP_COUPON_MAX_CODE";
    $is_extis = $CI->redislib->exists($coupon_code_key);

    //获取最大值
    $max = 1;
    if (!$is_extis) {
        $CI->redislib->set($coupon_code_key, $max);
    } else {
        $max = $CI->redislib->get($coupon_code_key);
    }
    //自增加1
    $CI->redislib->incr($coupon_code_key);

    //生成券码
    if ($max < 100000000)
        $left_code = dispRepair($max, 9, 0);
    else
        $left_code = $max;

    $right_code = crc32($left_code);
    $right_code = substr($right_code, 0, 3);

    $result = $left_code . $right_code;

    return $result;

}

function upload_img($inputname, $folder)
{
    //按 年月日 分目录保存
    $date = date("Ymd");
    //$year = substr($date, 0,6);
    //$day = substr($date, 6,2);
    $folder = "{$folder}{$date}/";

    //判断文件夹是否存在，不存在则创建
    if (!is_dir($folder)) {
        $result = createFolder($folder);
        if (!$result) {
            $result = array("statusCode" => "300", "message" => "网络繁忙,请稍后重试");
            return $result;
        }
    }

    $msg = "";
    if (isset($_FILES[$inputname]) && strlen($_FILES[$inputname]['tmp_name']) > 0) {


        $rand = rand(1, 100);
        $name = milltime() . $rand;//定义变量，保存图片名，以防图片的名字相同

        $houzui = strrchr($_FILES[$inputname]["name"], ".");


        if ($houzui == false) {
            $houzui = ".jpg";
        }


        $name .= $houzui;

        $tmp_name = $_FILES[$inputname]["tmp_name"];
        if ($_FILES[$inputname]["error"] > 0) {
            $msg = "上传文件有错误";
        } else {

            if (move_uploaded_file($tmp_name, $folder . $name)) {
                $msg = "";
            } else {
                $msg = "上传失败";
            }
        }
        if (strlen($msg) > 0) {
            $result = array("statusCode" => "300", "message" => $msg);
        } else {

            $result = array("statusCode" => "200", "message" => "上传成功", "filename" => $folder . $name);
        }
        return $result;
    } else {
        $result = array("statusCode" => "300", "message" => "上传文件为空");
        return $result;
    }
}

//批量上传
function batch_upload_img($inputname, $folder)
{
    try {
        $date = date("Ymd");
        //$year = substr($date, 0,6);
        //$day = substr($date, 6,2);
        $folder = "{$folder}{$date}/";

        //判断文件夹是否存在，不存在则创建
        if (!is_dir($folder)) {
            $result = createFolder($folder);
            if (!$result) {
                $result = array("statusCode" => "300", "message" => "网络繁忙,请稍后重试");
                return $result;
            }
        }


        $files = $_FILES[$inputname];

        if (count($files) == 0) {
            throw new Exception("上传文件为空", "300");
        }
        $names = $files["name"];
        $tmp_names = $files["tmp_name"];
        $errors = $files["error"];
        $sizes = $files["size"];
        if (count($names) == 0)
            throw new Exception("上传文件为空", "300");
        $count = count($names);

        $result_arr = array();
        for ($i = 0; $i < $count; $i++) {
            $ori_name = $names[$i];
            $tmp_name = $tmp_names[$i];
            $error = $errors[$i];
            $size = $sizes[$i];

            if ($error != "0")
                throw new Exception("上传文件失败", "300");

            $rand = rand(1, 100);

            $name = milltime() . $rand;//定义变量，保存图片名，以防图片的名字相同

            $houzui = strrchr($ori_name, ".");
            if ($houzui == false)
                $houzui = ".jpg";

            $name .= $houzui;

            if (move_uploaded_file($tmp_name, $folder . $name)) {

                $filename = $folder . $name;
                array_push($result_arr, $filename);
            } else {
                throw new Exception("上传文件失败", "300");
            }
        }


        $result = array("statusCode" => "200", "files" => $result_arr);
        return $result;


    } catch (Exception $e) {
        $result = array("statusCode" => "300", "message" => $e->getMessage());
        return $result;
    }
}


//生成一个缩略图 ==停用
function create_thumb($origin_path, $new_width, $new_houzui = "_thumb", $ci)
{
    //停用
    list($width, $height) = getimagesize($origin_path);

    $new_width = intval($new_width);
    $bili = $new_width / $width;
    $new_height = ceil($height * $bili);

    $config1['image_library'] = 'gd2';
    $config1['source_image'] = $origin_path;
    $config1['create_thumb'] = TRUE;
    $config1['maintain_ratio'] = TRUE;
    $config1['width'] = $new_width;
    $config1['height'] = $new_height;
    $config1['quality'] = '60';
    $config1['thumb_marker'] = $new_houzui;

    //$this->image_lib->initialize($config);

    $ci->load->library('image_lib', $config1);

    $ci->image_lib->resize();


    //============================================
    /*
    $this->load->library('image_lib');
    list($width, $height) = getimagesize($origin_path);
    $config['image_library'] = 'gd2';
    $config['source_image'] = $origin_path;
    $config['maintain_ratio'] = TRUE;
    if($width >= $height)
    {
        $config['master_dim'] = 'height';
    }else{

        $config['master_dim'] = 'width';
    }
    $config['width'] = $new_width;
       $config['height'] = $new_height;
    $this->image_lib->initialize($config);
    $this->image_lib->resize();
     /*
    $config['maintain_ratio'] = FALSE;
    if($width >= $height)
    {
        $config['x_axis'] = floor(($width * 240 / $height - 240)/2);
    }else{
        $config['y_axis'] = floor(($height * 240 / $width - 240)/2);
    }
    $this->image_lib->initialize($config);
    $this->image_lib->crop();
    */
}


//递归创建文件夹
function createFolder($path)
{
    try {
        if (!file_exists($path)) {
            createFolder(dirname($path));
            mkdir($path, 0777);
        }
        return true;
    } catch (Exception $e) {
        $msg = $e->getMessage();
        log_message('error', "创建文件夹失败[{$path}],原因[{$msg}]");
        return false;
    }

}


//计算时间显示
function get_time_tip($createtime)
{
    $today = date("Y-m-d");

    $yestoday = date("Y-m-d", strtotime("-1 day"));

    $qiantian = date("Y-m-d", strtotime("-2 day"));

    $now = time();
    $createtime = strtotime($createtime);

    $create_date = date("Y-m-d", $createtime);

    //if($create_date != $today)
    //	return  date("m-d",strtotime($create_date));

    $secs = $now - $createtime;
    if ($secs < 60)
        return "1分钟前";
    else if ($secs < 60 * 60) {
        return floor($secs / 60) . "分钟前";
    } else if ($secs < 60 * 60 * 24) {
        return floor($secs / 3600) . "小时前";
    } else if ($create_date == $yestoday)
        return "昨天";
    else if ($create_date == $qiantian)
        return "前天";
    else
        return date("m-d", strtotime($create_date));


}

//按距离排序,门店
function distance_sort($a, $b)
{
    if ($a->distance == $b->distance)
        return 0;
    return ($a->distance > $b->distance) ? 1 : -1;
}

//按距离排序,用户
function user_distance_sort($a, $b)
{
    if ($a["distance"] == $b["distance"])
        return 0;
    return ($a["distance"] > $b["distance"]) ? 1 : -1;
}

//按好友数排序
function user_friends_count_sort($a, $b)
{
    if ($a["comm_friend_count"] == $b["comm_friend_count"])
        return 0;
    return ($a["comm_friend_count"] > $b["comm_friend_count"]) ? -1 : 1;
}

//按权重排序,推荐用户
function user_quanzhong_sort($a, $b)
{
    if ($a["quanzhong"] == $b["quanzhong"])
        return 0;
    return ($a["quanzhong"] > $b["quanzhong"]) ? -1 : 1;
}

//动态按时间排序
function dt_time_sort($a, $b)
{
    if ($a["create_time"] == $b["create_time"])
        return 0;
    return ($a["create_time"] > $b["create_time"]) ? -1 : 1;
}

//游神智能排序
function god_zhineng_sort($a, $b)
{
    if ($a["zhineng_score"] == $b["zhineng_score"])
        return 0;
    return ($a["zhineng_score"] > $b["zhineng_score"]) ? -1 : 1;
}


//获取当前自然周
function getWeekNow()
{
    $datearr = getdate();
    $year = strtotime($datearr['year'] . '-1-1');
    $startdate = getdate($year);
    $firstweekday = 7 - $startdate['wday'];//获得第一周几天
    $yday = $datearr['yday'] + 1 - $firstweekday;//今年的第几天
    return ceil($yday / 7) + 1;//取到第几周
}

//计算游神综合分
function GetGodZhiNengScore($distance, $time_secs, $order_count)
{
    //距离
    $distance_score = 0;
    if ($distance <= 1000)
        $distance_score = 10;
    else if ($distance <= 2000)
        $distance_score = 9;
    else if ($distance <= 3000)
        $distance_score = 8;
    else if ($distance <= 5000)
        $distance_score = 7;
    else if ($distance <= 6000)
        $distance_score = 6;
    else if ($distance <= 8000)
        $distance_score = 5;
    else if ($distance <= 10000)
        $distance_score = 4;
    else if ($distance <= 15000)
        $distance_score = 3;
    else if ($distance <= 30000)
        $distance_score = 2;
    else
        $distance_score = 1;

    //活跃度（时间） 1-6
    $time_score = 0;
    if ($time_secs <= 300)
        $time_score = 6;
    else if ($time_secs <= 900)
        $time_score = 5;
    else if ($time_secs <= 1800)
        $time_score = 4;
    else if ($time_secs <= 3600)
        $time_score = 3;
    else if ($time_secs <= 10800)
        $time_score = 2;
    else
        $time_score = 1;


    //接单分 1-3
    $redu_score = 0;
    if ($order_count > 100)
        $redu_score = 3;
    else if ($order_count > 10)
        $redu_score = 2;
    else
        $redu_score = 1;

    //扣分项
    $kou_fen = 0;
    if ($time_secs > 7 * 24 * 3600)
        $kou_fen = 8;
    else if ($time_secs > 3 * 24 * 3600)
        $kou_fen = 6;
    else if ($time_secs > 1 * 24 * 3600)
        $kou_fen = 4;
    else if ($time_secs > 12 * 3600)
        $kou_fen = 2;

    $zonghe_score = $distance_score + $time_score + $redu_score - $kou_fen;

    return $zonghe_score;
}

//获取版本对应的KEY
function GetAppSecret($soft_version)
{
    //内部版本号
    $soft_version = floatval($soft_version);
    $secret = "";
    if ($soft_version >= 14)//V2.3换私钥以后
    {
        $secret = "V14ISZHANGLINAICHENYANMAYESTAAI20150601";
        return $secret;
    } else if ($soft_version < 14) //V2.2及以前的
    {
        $secret = "WYWKAPPV1TESTTOKEN";
        return $secret;
    } else //默认的
    {
        $secret = "WYWKAPPV1TESTTOKEN";
        return $secret;
    }
}

function getAgeByIdcard($id)
{
    if (!isCreditNo($id)) {
        return 0;
    }
    $date = strtotime(substr($id, 6, 8));//获得出生年月日的时间戳
    $today = strtotime('today');//获得今日的时间戳
    $diff = floor(($today - $date) / 86400 / 365);//得到两个日期相差的大体年数
    $age = strtotime(substr($id, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
    return $age;
}

function getSexByIdcard($id)
{
    if (!isCreditNo($id)) {
        return 0;
    }
    $sexint = (int)substr($id, 16, 1);
    return $sexint % 2 === 0 ? 0 : 1;
}

function getAgeByBirthday($birthday)
{
    $age = strtotime($birthday);
    if ($age === false) {
        return false;
    }
    list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
    $now = strtotime("now");
    list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
    $age = $y2 - $y1;
    if ((int)($m2 . $d2) < (int)($m1 . $d1))
        $age -= 1;

    return $age;
}

function isCreditNo($vStr)
{
    $vCity = array(
        '11', '12', '13', '14', '15', '21', '22',
        '23', '31', '32', '33', '34', '35', '36',
        '37', '41', '42', '43', '44', '45', '46',
        '50', '51', '52', '53', '54', '61', '62',
        '63', '64', '65', '71', '81', '82', '91'
    );

    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;

    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;

    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);

    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }

    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
    if ($vLength == 18) {
        $vSum = 0;

        for ($i = 17; $i >= 0; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
        }

        if ($vSum % 11 != 1) return false;
    }

    return true;
}

function hidtel($phone)
{
    $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i', $phone); //固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i', '$1****$2', $phone);
    } else {
        return preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
    }
}