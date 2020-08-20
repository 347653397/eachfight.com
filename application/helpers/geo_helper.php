<?php
	
	//根据经纬度计算距离 其中A($lat1,$lng1)、B($lat2,$lng2)
    function getDistance($lat1,$lng1,$lat2,$lng2)
    {
        //地球半径
        $R = 6378137;
 
        //将角度转为狐度
        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
 
        //结果
        $s = acos(cos($radLat1)*cos($radLat2)*cos($radLng1-$radLng2)+sin($radLat1)*sin($radLat2))*$R;
 
        //精度
        $s = round($s* 10000)/10000;
 
        return  round($s);
    }
	
	
	function getDistance1($lat1, $lng1, $lat2, $lng2)
	{
	     $earthRadius = 6367000; //approximate radius of earth in meters
		 
	     $lat1 = ($lat1 * pi() ) / 180;
	     $lng1 = ($lng1 * pi() ) / 180;
	
	     $lat2 = ($lat2 * pi() ) / 180;
	     $lng2 = ($lng2 * pi() ) / 180;
	
	     $calcLongitude = $lng2 - $lng1;
	     $calcLatitude = $lat2 - $lat1;
	     $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  
		 $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
	     $calculatedDistance = $earthRadius * $stepTwo;
	
	     return round($calculatedDistance);
	}
?>
