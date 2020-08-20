<?php
define('CHARSET', "UTF-8");

	//过滤非法单引号
	function h_addslashes($str)
	{
		
		if(!get_magic_quotes_gpc())
		{
			if(is_array($str)) {
				foreach($str as $key => $val) {
					$str[$key] = h_addslashes($val);
				}
			} else {
				$str = addslashes($str);
			}
			
		}
		return $str;
		
	}

	//输出转义
	function h_stripslashes($data) {
		if(!get_magic_quotes_gpc())
		{
			if(is_array($data)) {
				foreach($data as $key => $val) {
					$data[h_stripslashes($key)] = h_stripslashes($val);
				}
			} else {
				$data = stripslashes($data);
			}
		}
		return $data;
	}


	//html代码替换
	function h_htmlspecialchars($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = h_htmlspecialchars($val);
			}
		} else {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		}
		return $string;
	}

	//sql注入检查
	function h_inject_check($sql_str) {      
	  return preg_match('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);    
	}    

	//获取字符串长度
	function h_strlen($str) {
		if(strtolower(CHARSET) != 'utf-8') {
			return strlen($str);
		}
		$count = 0;
		for($i = 0; $i < strlen($str); $i++){
			$value = ord($str[$i]);
			if($value > 127) {
				$count++;
				if($value >= 192 && $value <= 223) $i++;
				elseif($value >= 224 && $value <= 239) $i = $i + 2;
				elseif($value >= 240 && $value <= 247) $i = $i + 3;
		    	}
	    		$count++;
		}
		return $count;
	}

	//判断是否是EMAIL
	function h_isemail($email) {
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
	
	//判断合法的用户吗
	function h_check_username($username,$minlen,$maxlen) {
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		$len = h_strlen($username);
		if($len > $maxlen || $len < $minlen || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $username)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

if ( ! function_exists('isMobile'))
{
    function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,3,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}




//	/**********************************
//	  * 截取字符串(UTF-8)
//	  * @param string $str 原始字符串
//	  * @param $position 开始截取位置
//	  * @param $length 需要截取的偏移量
//	  * @return string 截取的字符串
//	  * $type=1 等于1时末尾加'...'不然不加
//	 *********************************/
//	 function utfSubstr($str, $position, $length,$type=1){
//	  	  $startPos = strlen($str);
//		  $startByte = 0;
//		  $endPos = strlen($str);
//		  $count = 0;
//		  for($i=0; $i<strlen($str); $i++){
//		   	if($count>=$position && $startPos>$i){
//		    	$startPos = $i;
//		    	$startByte = $count;
//		   	}
//		   	if(($count-$startByte) >= $length) {
//		    	$endPos = $i;
//		    	break;
//		   	}
//		   	$value = ord($str[$i]);
//		   	if($value > 127){
//		    	$count++;
//		    	if($value>=192 && $value<=223) $i++;
//	    		elseif($value>=224 && $value<=239) $i = $i + 2;
//		    	elseif($value>=240 && $value<=247) $i = $i + 3;
//		    	else
//		    		return self::raiseError("\"$str\" Not a UTF-8 compatible string", 0, __CLASS__, __METHOD__, __FILE__, __LINE__);
//		   	}
//		   $count++;
//
//		  }
//		  if($type==1 && ($endPos-6)>$length){
//		   	return substr($str, $startPos, $endPos-$startPos)."...";
//		  }
//		  else{
//		   	return substr($str, $startPos, $endPos-$startPos);
//	      }
//
//	 }

	function my_json_encode(array $data) {
        $s= array();
        foreach($data as $k => $v) {
            if(is_array($v)) {
                $v = my_json_encode($v);
                $s[] = "\"$k\":$v";
            }else{
                $v = addslashes( str_replace( array("\n","\r"), '', $v));
                $s[] = "\"$k\": \"$v\"";
            }
        }
        return '{'.implode(', ', $s).'}';
    }
    
    //Aes加密 
	function AESencrypt($input, $key) {  
        		
		$aescrypt = new AESCrypt($key);
		return $aescrypt->encrypt($input);
        
    }  
    
    
  
    //Aes解密
    function AESdecrypt($input, $key) {  
        $aescrypt = new AESCrypt($key);
		return $aescrypt->decrypt($input);
    }    
    
    //获取AES的KEY
    function GetAESKey($origin_key)
    {
    	if(strlen($origin_key) < 37)
    		return "";
    		
    	$key1 = substr($origin_key, 9,4);
    	$key2 = substr($origin_key, 15,4);
    	$key3 = substr($origin_key, 20,4);
    	$key4 = substr($origin_key, 32,4);
    	
    	return $key1.$key2.$key3.$key4;
    	
    }
    
