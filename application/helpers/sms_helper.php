<?php

//上海一通短信平台
function SendSMS($mobile,$content)
{
	$content = mb_convert_encoding($content,"GBK","UTF-8");
	$sms_info = array(
            'host'=>'61.132.98.174',
            'port'=>'3088',
            'url'=>'/SMSCenter/servlet/SendMessageServelt?type=2&mobilePhone='.$mobile.'&barCode=100004',
            'content'=>$content
        );
	
	$ret = http_stream($sms_info["host"],$sms_info["port"],$sms_info["url"],$sms_info["content"]);
	if(strlen($ret) == 0)
	{
		log_message('error',"发送验证码失败：可能未连上服务器");
		return  false;
	}
	
	$arr = explode("|", $ret);
	if($arr[0] == "40000")
	{
		return true;
	}
	else
	{
		$msg = $arr[1];
		log_message('error',"发送短信失败：[{$msg}]");	
		return  false;
	}
	
}

//云片发送短信平台
function SendMsgByYP($tpl_id,$tpl_parameter_value,$mobiles)
{
	$appkey = "1934626abc579552bddd97bfc723f8c1";
	$host = "http://yunpian.com/v1/sms/tpl_send.json";
	
	$params = array("apikey"=>$appkey,"mobile"=>$mobiles,"tpl_id"=>$tpl_id,"tpl_value"=>$tpl_parameter_value);
	
	$post_data = http_build_query($params);
	
	$result = http_post($host, $post_data);
	
	return $result;
}

//根据手机号获取归属地
function GetCityByMobile($mobile)
{
	$province = "";
	
	$city = "";
	
	$url = "http://apis.juhe.cn/mobile/get?phone={$mobile}&key=aa209f179352d2a87e1e8bbb82c38d39";
	
	$result = http_get($url);
	
	if(strlen($result) > 0)
	{
		$result = json_decode($result);
	
		$code = $result->resultcode;
		if($code == "200")
		{
			$city_result = $result->result;
			$province = $city_result->province;
			$city = $city_result->city;
		}
	}	
	return array("province"=>$province,"city"=>$city);
	
}

