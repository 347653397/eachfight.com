<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


	function http_get( $url, $https = false, $cookie='' )
	{
		// 初始化一个cURL会话
		$curl = curl_init($url);
		
		if($https)
		{
			//验证证书
			//curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,true); ;
   			//curl_setopt($curl,CURLOPT_CAINFO,BASEPATH.'cacert.pem');
			
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);			
		}
		
		// 不显示header信息
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		if(!empty($cookie)) {
			// 包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT,120);  
		// 自动设置Referer
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		// 执行一个curl会话
		$tmp = curl_exec($curl);
		// 关闭curl会话
		curl_close($curl);
		return $tmp;
	}
	
	function http_post( $url, $params, $https=false )
	{
        header("Content-type:text/html;charset=utf-8");
        $curl = curl_init($url);
		if($https)
		{
			//不验证证书
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		
		curl_setopt($curl, CURLOPT_HEADER, 0);

		//模拟用户使用的浏览器，在HTTP请求中包含一个”user-agent”头的字符串。
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
		curl_setopt($curl, CURLOPT_POST, 1);
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		// 自动设置Referer
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		// Cookie地址
		//curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
		// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，
		// 在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串
		// 类似'para1=val1¶2=val2&...'或使用一个以字段名为键值，字段数据为值的数组
		// 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
		//curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		
		curl_setopt($curl, CURLOPT_POSTFIELDS,$params);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	
	function http_dowload($remote, $local, $cookie= '') {
		$cp = curl_init($remote);
		$fp = fopen($local,"w");
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		if($cookie != '') {
			curl_setopt($cp, CURLOPT_COOKIEFILE, $cookie);
		}
		curl_exec($cp);
		curl_close($cp);
		fclose($fp);
	}
	
	function http_stream($host,$port,$uri,$content)
	{
		
        $fp = @fsockopen($host,$port,$errno,$errstr,30);
		if($fp)
		{
			stream_set_blocking($fp,0);  
	        fwrite($fp,"POST ".$uri." HTTP/1.1\r\n");
	        fwrite($fp,"Host:".$host."\r\n");
	        fwrite($fp,"Content-Type: multipart/form-data; \r\n");
	        fwrite($fp,"Content-length:".strlen($content)."\r\n\r\n");
	        fwrite($fp,$content);
			
			$ret ="";
			
	        while (!feof($fp)){
	            $ret .= fgets($fp, 1024);
	        }
	        fclose($fp);
			
	        $ret = trim(strstr($ret, "\r\n\r\n"));
			
			return $ret;
		}
		else
		{
			log_message('error',"http_stream 连接失败");
			return  "";
		}
	}
	
	function post_it($datastream, $url,$port) { 

		$url = preg_replace("@^http://@i", "", $url);
		$host = substr($url, 0, strpos($url, "/"));
		$uri = strstr($url, "/"); 
		
      	$reqbody = "";
	    foreach($datastream as $key=>$val) {
        	if (!empty($reqbody)) 
          		$reqbody.= "&";
	      	$reqbody.= $key."=".urlencode($val);
	    } 
		
		$contentlength = strlen($reqbody);
	    $reqheader =  "POST $uri HTTP/1.1\r\n".
	                   "Host: $host\n". "User-Agent: PostIt\r\n".
	     "Content-Type: application/x-www-form-urlencoded\r\n".
	     "Content-Length: $contentlength\r\n\r\n".
	     "$reqbody\r\n"; 
		
		$socket = fsockopen($host, $port, $errno, $errstr);
		
		if (!$socket) {
		   $result["errno"] = $errno;
		   $result["errstr"] = $errstr;
		   return $result;
		}
		
		fputs($socket, $reqheader);
		
		while (!feof($socket)) {
		   $result[] = fgets($socket, 4096);
		}
		
		fclose($socket);
		
		return $result;
	}


	if ( ! function_exists('curlQuery'))
	{
	    function curlQuery($url, $postTag = false, $postData = array()) {
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	        if ($postTag)
	        {
	            curl_setopt($ch, CURLOPT_POST, true);
	            if(is_array($postData)){
	            	$postData = http_build_query($postData);
	            }
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	        }
	        $result = curl_exec($ch);
	        curl_close($ch);
	        return $result;
	    }
	}

	
?>
