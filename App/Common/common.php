<?php
/**
 * 公共函数库
 */

/**
 * 	echo G('begin','end',6);	//页面执行时间
 */

/*	判断是否为post提交
 * @$value  post提交的值
*/

//倒入状态位

include APP_PATH.'Common/status.php';

function isPost($value) {
	//是post提交 ，并且post值存在，或者post值不为空
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && sizeof($value) && !empty($value)) {
		return true;
	} else {
		return false;
	}

}


function array_add_to (&$old,&$new) {
	if (is_array($old) && is_array($new)) {
		foreach ($new AS $key=>$val) {
			$old[$key] = $val;
		}
	} else {
		return false;
	}
}


/**
 * 对象转换为字符串
 * @param unknown_type $_object
 * @param unknown_type $_fild
 * @return string
 */
function objArrOfstr ($_object,$_fild) {
	if ($_object) {	//如果有值
		foreach ($_object as $_value) {
			$_html .= $_value->$_fild.',';
		}
	}
	return substr($_html,0,strlen($_html)-1) ;//去掉最后的逗号
}


/**
 * 计算天数，返回日期函数
 * @param num or string $month		月
 * @param num or string $year		年
 * @param num or string $day			日
 * @param num or string $type		返回时间类型
 * @return string 	date
 */
function getDateNum($day,$month,$year,$type = 't') {
	return date($type,mktime(0,0,0,$month,$day,$year));
}


//时间数据转换
function time_conversion($data){
	$arr=explode("/",$data);
	return date("Y-m-d",mktime(0,0,0,$arr[0],$arr[1],$arr[2]));
}
function time_conversion_2($data){
	$arr=explode("-",$data);
	return $arr[1].'/'.$arr[2].'/'.$arr[0];
}


/**
 * 获取数组val值中的字段
 * @param Array $arr
 * @param string $field
 * return Array
 */
function getArrayByField(&$arr,$field, $key = '') {
	$aRet = array();
	if ($key !== '') {
		foreach ($arr AS $aVal) {
			$aRet[$aVal[$key]] = $aVal[$field];
		}
	} else {
		foreach ($arr AS $aVal) {
			$aRet[] = $aVal[$field];
		}
	}
	return $aRet;
}

/**
 * 根据Val值，重新排序数组
 * @param Array $arr			//排序的数组
 * @param String $k				//排序的字段
 * @param Boole $old			//是否按照原数组的排序
 * @return Array
 */
function regroupKey(&$arr,$k,$old = false) {
	if (empty($arr)) return false;
	$aRet = array();
	if ($old == true) {
		foreach ($arr AS $key=>$val) {
			$aRet[$val[$k]] = $val;
		}
	} else {
		foreach ($arr AS $key=>$val) {
			$aRet[$val[$k]][] = $val;
		}
	}
	return $aRet;
}



/**
 * 预防SQL注入，转义非法字符
 * @param unknown_type $str
 * @return Ambigous <unknown, string>
 */
function setString($str) {
	return get_magic_quotes_gpc() ? $str : addslashes($str);
}
/**
 * 把转译的字符返回没有转义前的样子
 * @param string $str
 * @return string
 */
function unSetString($str) {
	return stripslashes($str);
}
//4.预防SQL注入
function setSql($_str) {
	if (is_array($_str)) {	//数组
		foreach ($_str as $_key => $_value) {
			$_string[$_key] =  setSql($_value);
		}
	} else if (is_object($_str)) {	//对象
		foreach ($_str as $_key => $_value) {
			$_string->$_key =  setSql($_value);
		}
	} else {	//字符串
		$_string = setString($_str);
	}
	return $_string;
}



//数组的插入与删除
/**
 * 5、数组任意部位插入新的值，保持排序
 * @param data  		$data		插入的数据
 * @param num	 	$num		插入的位置
 * @param array 		 $array		要操作的数组
 * @return array
 */
function InsertValArray($data,$num,&$array) {
	for ($i=count($array);$i>$num;$i--) {
		$array[$i] = $array[$i-1];	//把数组的值向后移动
	}
	$array[$num] =  $data;			//在指定位置插入数据
	//return $array;
}



/**
 * 把数组转换为json格式
 * @param $array  数组
 * json_encode($array)		//转换数组为JSON格式
 * json_decode($json);		//转换JSON为数组
 */
function JSON($array) {
	arrayRecursive($array, 'urlencode', true);
	$json = json_encode($array);		//转换数组为json格式
	return urldecode($json);
}
function arrayRecursive(&$array, $function, $apply_to_keys_also = false) {
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		die('possible deep recursion attack');
	}
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}
		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}




/**
 * 加密
 * @param string $txt  加密内容
 * @param string $key	解密时的钥匙
 */
function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

/**
 * 解密
 * @param string $txt	passport_encrypt()加密后的字符
 * @param $string $key	解密时的钥匙
 * @return Ambigous <string, boolean>
 */
function passport_decrypt($txt, $key) {
	$txt = passport_key(base64_decode($txt), $key);
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
		$md5 = $txt[$i];
		$tmp .= $txt[++$i] ^ $md5;
	}
	return $tmp;
}
//加密算法
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}



/**
 * 搜素字符串
 * @param STRING $find		//搜索源
 * @param STRING $str			//要搜索的字符串
 */
function find_string ($find,$str) {
	if (strpos($find,$str) ===false) {
		return false;
	} else {
		return true;
	}
}


/**
 *用指定经纬度，计算指定范围内，存在的数据
 *@param lng float 经度		(长的)		121.473704
 *@param lat float 纬度		(短的)		31.230393
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米 (范围)
 *@return array 正方形的四个点的经纬度坐标
 *
 *参考资料：http://www.flyphp.cn/phpmysql-%E6%A0%B9%E6%8D%AE%E4%B8%80%E4%B8%AA%E7%BB%99%E5%AE%9A%E7%BB%8F%E7%BA%AC%E5%BA%A6%E7%9A%84%E7%82%B9%EF%BC%8C%E8%BF%9B%E8%A1%8C%E9%99%84%E8%BF%91%E7%9A%84%E4%BA%BA%E6%9F%A5%E8%AF%A2.html
 */
function _SquarePoint($lng, $lat,$distance = 0.8){		//经度、纬度、范围

	define('EARTH_RADIUS', 6371);	//地球半径，平均半径为6371km
	$dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
	$dlng = rad2deg($dlng);

	$dlat = $distance/EARTH_RADIUS;
	$dlat = rad2deg($dlat);

	//返回，经纬度坐标点内，正方形4个点的经纬度
	return array(
			'left-top'=>array('lng'=>$lng-$dlng,'lat'=>$lat + $dlat),				//左上：经度、纬度
			'right-top'=>array('lng'=>$lng + $dlng,'lat'=>$lat + $dlat),			//右上：经度、纬度
			'left-bottom'=>array('lng'=>$lng - $dlng,'lat'=>$lat - $dlat),		//左下：经度、纬度
			'right-bottom'=>array('lng'=>$lng + $dlng,'lat'=>$lat - $dlat)		//又下：经度、纬度
	);
}


/**
 * 计算二个经纬度之间的距离
 * @param unknown_type $d
 * @return number
 */
function rad($d) {
	return $d * 3.1415926535898 / 180.0;
}
function GetDistance($lat1, $lng1, $lat2, $lng2)	{//lat纬度(短的)，lng经度(长的)
	$EARTH_RADIUS = 6378.137;
	$radLat1 = rad($lat1);

	$radLat2 = rad($lat2);
	$a = $radLat1 - $radLat2;
	$b = rad($lng1) - rad($lng2);
	$s = 2 * asin(sqrt(pow(sin($a/2),2) +
			cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
	$s = $s *$EARTH_RADIUS;
	$s = round($s * 10000) / 10000;
	return $s;
}


/**
 * 快速排序：
 * 取数组中第一个作为比较值，遍历数组，把比比较值小的放在一个左数组中，反之放在右数组中。
 * 通过递归排序出需要的结果。
 */
function quickSort(&$array,$field){
	$count = count ($array);
     if ($count <= 1) return $array;
     
     $key = $array [0];
     
     $left_array = array ();
     $middle_array = array ();
     $right_array = array ();
       
     foreach ($array as $k => $val ) {
     	//这里改变大于小于，改变数组的排序
     	//如if ($key[$field] > $val[$field]) {
     	if ($key[$field] > $val[$field]) {
     		$left_array[] = $val;				
      	} else if ($key[$field] == $val[$field]) {
            $middle_array [] = $val;					 	//直接插入
     	} else {
            $right_array [] = $val;
       	}
     }
 
     //递归
     $left_array = quickSort($left_array,$field);
     $right_array = quickSort($right_array,$field);
       
     //合并数组
     $array = array_merge ($left_array, $middle_array, $right_array);
     return $array;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
    if(is_array($list)){
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
        	case 'asc': // 正向排序
        	    asort($refer);
        	    break;
        	case 'desc':// 逆向排序
        	    arsort($refer);
        	    break;
        	case 'nat': // 自然排序
        	    natcasesort($refer);
        	    break;
        }
        foreach ( $refer as $key=> $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 快速排序
 * @param Array $array
 * @param String $field	//需要排序的字段
 * @param String $sort	//asc 从小到大   desc 从大到下
 */
 function quickSortTwo(&$array,$field,$sort = 'asc'){
    $count = count ($array);
    if ($count <= 1) return $array;

    $key = $array [0];

    $left_array = array ();
    $middle_array = array ();
    $right_array = array ();

    foreach ($array as $k => $val ) {
        //这里改变大于小于，改变数组的排序
        //如if ($key[$field] > $val[$field]) {
        if ($sort == 'asc') {
            if ($key[$field] > $val[$field]) {
                $left_array[] = $val;
            } else if ($key[$field] == $val[$field]) {
                $middle_array [] = $val;					 	//直接插入
            } else {
                $right_array [] = $val;
            }
        } elseif ($sort == 'desc') {
            if ($key[$field] < $val[$field]) {
                $left_array[] = $val;
            } else if ($key[$field] == $val[$field]) {
                $middle_array [] = $val;					 	//直接插入
            } else {
                $right_array [] = $val;
            }
        }

       }

        //递归
        $left_array = quickSortTwo($left_array,$field,$sort);
        $right_array = quickSortTwo($right_array,$field,$sort);

        //合并数组
        $array = array_merge ($left_array, $middle_array, $right_array);
        return $array;
    }


/**
 * 比较二个数组，计算出需要插入和删除的数据
 * @param $Array $arr_request		//请求的数据
 * @param $Array $arr_have			//原本已存在的数组，如数据库中的数据
 * @return $Array								//需要插入和删除的数组
 */
function arrar_insert_delete(&$arr_request,&$arr_have) {
	if (empty($arr_request)) $arr_request = array();
	if (empty($arr_have)) $arr_have = array();
	//计算需要插入的数据 (二个数组不同的地方)
	$insert_arr = array_diff($arr_request,$arr_have);

	//计算需要删除的数据(不在$arr_request请求数组中的数据)
	$delete_arr = array();
	foreach ($arr_have AS $key=>$val) {
		if (!in_array($val,$arr_request)) {
			array_push($delete_arr,$val);
		}
	}
	return array('insert'=>$insert_arr,'delete'=>$delete_arr);
}


/**
 * 计算二个日期之间相差的天数
 * @param str $start		开始时间 	如:2013-02-10
 * @param str $over		结束时间		2013-02-12
 * @param num $type	类型		1为字符串  0为时间戳
 * @return number
 */
function countDays ($start,$over,$type =0) {	//传入时间戳、或者字符类型日期
	if ($type == 1) {
		//转换为时间戳
		$d1=strtotime($start);
		$d2=strtotime($over);
		//计算二个时间戳之差,获取相差天数
		$Days = round(($d2 - $d1)/3600/24);
	} elseif ($type == 0) {
		$Days = round(($over - $start)/3600/24);
	}
	return $Days;
}


/**
 * 不足6小时则算一天，超过6小时，但在6小时内，算0.5天。6小时算一天。
 * @param INT $start		开始日期
 * @param INT $over		结束如期
 */
function sex_day ($start,$over) {
	/* 转换日期为毫秒数 */
	$hours = ($over - $start) / 60 / 60;			//计算相差的小时数
	$sex_day = $hours  / 6;							//每6小时算一天

	if(is_float($sex_day)) {						//超过6小时，但是还在6小时内，算0.5天
		$length = floor($sex_day) + 0.5;			
	} else {
		$length = $sex_day;								//可以被整除的，算整除天数
	}
	
	return $length;

}



/**
 * 超过6小时算一天，但在6小时内，算0.5天。24小时算一天。
 * @param int $start		开始日期
 * @param int $over		结束如期
 */
function format_sex_day ($start,$over) {
	$hours = ($over - $start) / 60 / 60;			//计算相差的小时数
	
	$days = $hours  / 24;							//计算一共多少天

	$residue_hours = $hours % 24;			//剩余小时数

	if ($residue_hours == 0) {
		$length = $days;
	} elseif ($residue_hours < 6) {
		$length = floor($days) + 0.5;
	} elseif ($residue_hours >= 6) {
		$length = floor($days) +1;
	}

	return $length;
}



/**
 * 字符长度限制
 * @param Array $all				//
 * @param Array $fields			//字段如：array('create_time','update_time');
 */
function set_str_len(&$all,$fields,$length) {
	if (empty($all)) return false;
	/* 多维数组 */
	if (count($all[0]) >=1)  {
		foreach ($all AS $key=>$val) {
			for ($i=0;$i<count($fields);$i++) {
				if (mb_strlen($all[$key][$fields[$i]],'utf-8') >$length) {
					$all[$key][$fields[$i]] = mb_substr($all[$key][$fields[$i]],0,10,'utf-8').'...';
						
				}
			}
		}
		/* 一维数组 */
	} else {
		for ($i=0;$i<count($fields);$i++) {
			$all[$fields[$i]] = mb_substr($all[$key][$fields[$i]],0,10,'utf-8').'...';
		}
	}
}



function tolog($path,$content){
	if (!file_exists($path)) {
		file_put_contents($path,'create_file');
	}
	$file_content	=	file_get_contents($path);		
	$new_content = "\r\n".date('Y-m-d H:i:s').' -> '.$content;
	$file_content  .= $new_content;
	file_put_contents($path,$file_content);
	return $new_content;
}

//获得时间戳
function getTime($date){
  if($date =='今天') return strtotime(date('Y-m-d',time()));
  if($date =='明天') return strtotime(date('Y-m-d',strtotime("+1 day")));
  if($date =='后天') return strtotime(date('Y-m-d',strtotime("+2 day")));   
  $date = str_replace('年','-',$date);
  $date = str_replace('月','-',$date);
  $date = str_replace('日','',$date);
  $date = str_replace('号','',$date);

  return strtotime($date);
}

// 计算两个日期之间的天数
function daysDiff($timestamp1, $timestamp2) {
    $date1 = strtotime(date('Y-m-d', $timestamp1)); 
    $date2 = strtotime(date('Y-m-d', $timestamp2));
    return intval(($date1-$date2)/86400);
}
  
  
  //过滤字符
  function addsltrim($val)
  {
  	$new = array();
  	foreach($val as $k=>$v)
  	{
  		$new[$k] = trim(addslashes($v));
  	}
  	return $new;
  }
  

    
/**
 * 对象转成数组
 * @param Object $obj
 * @return Array
 */
 function objectToArray($obj){
    $arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
    $arr = array();

    if(!empty($arrObj))
    {
        foreach ($arrObj as $key => $val){
            $val = (is_array($val) || is_object($val)) ? objectToArray($val) : $val;
            $arr[$key] = $val;
        }
    }
    return $arr;
}


//1、弹窗返回
function alertBack($_info) {
	header('Content-Type:text/html;charset=utf-8');
	echo "<script type='text/javascript'>alert('$_info');history.back();</script>";
	exit();
}

//2、弹窗关闭
function alertClose($_info) {
	header('Content-Type:text/html;charset=utf-8');
	echo "<script type='text/javascript'>alert('$_info');close();</script>";
	exit();

}

//3、弹窗跳转
function alertLocation ($_info,$_url) {
	header('Content-Type:text/html;charset=utf-8');
	if (!empty($_info)) {
		echo "<script type='text/javascript'>alert('$_info');location.href='$_url';</script>";
		exit();
	} else {
		header('Location:'.$_url);
		exit();
	}
}


/**
 * 解析csv文件 成数组
 * @param String $file_name
 * $csv_data = array_splice($csv_data,1,count($csv_data)-2);
 */
function analysis_csv ($file_name) {
	if (file_exists($file_name) == false) {
		return false;
	}

	$result = array();

	$file = fopen($file_name,"r");
	while(!feof($file))
	{
		$csv_data = fgetcsv($file);
			
		$tmp_array = array();
		foreach ($csv_data as $key=>$val) {
			$format_string = iconv('gbk','utf-8',$val);
			array_push($tmp_array,$format_string);
		}

		array_push($result,$tmp_array);
		$tmp_array = null;
	}

	fclose($file);
	return $result;
}

/**
 * 解析文本文件成数组
 * @param String $file_name
 * @param String $ex
 */
function analysis_txt ($file_name,$ex = ' ') {
	header('Content-Type:text/html;charset=utf-8');

	if (file_exists($file_name) == false) {
		return false;
	}

	$result = array();

	$file_source = fopen($file_name,"r");
	while (! feof ($file_source)) {
		$line_str = fgets ($file_source);
		if (empty($line_str)) continue;
		$line_array = explode($ex,$line_str);
		array_push($result,$line_array);
	}

	fclose($file_source);

	return $result;
}


/**
 * 根据数据源创建csv文件
 * @param String $name
 * @param Array OR String $content
 */
function create_excel($name,$content) {

	if (is_array($content)) {

		//$title = '会员号,b,c'."\n";
		$result = '';
		foreach ($content as $key=>$val) {
			foreach ($val as $k=>$v) {
				//$str .= (iconv( "UTF-8","gbk",$val['oid'])).',';
				$result .= $v.',';
			}
			$result .= "\n";
		}
	}

	
	header('Content-Type:text/html;charset=utf-8');
	header("Content-Type: application/force-download");
	header("Content-Type: text/csv");					//CSV文件
	header("Content-Disposition: attachment; filename=$name.csv");					//强制跳出下载对话框
	header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
	header('Expires:0');
	header('Pragma:public');

	$content = (iconv( "UTF-8","gbk",$result)).',';

	echo $content;
}


/**
 * 加密算法
 * @param unknown $str
 * @return string
 */
function pass_encryption ($str){
	return md5($str);
}
  
    
    
    /**
     * 显示长度限制
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 是否显示后缀
     * @return string
     */
    function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    	if(function_exists("mb_substr"))
    		$slice = mb_substr($str, $start, $length, $charset);
    	elseif(function_exists('iconv_substr')) {
    		$slice = iconv_substr($str,$start,$length,$charset);
    		if(false === $slice) {
    			$slice = '';
    		}
    	}else{
    		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    		preg_match_all($re[$charset], $str, $match);
    		$slice = join("",array_slice($match[0], $start, $length));
    	}
    	return $suffix ? $slice.'...' : $slice;
    }

    function get_sj_max_num ($count,$num) {
    	$sj = 0;
    	if ($count - $num < 0 ){
    		$sj = 0;
    	} elseif ($count - $num < $num) {
    		$sj = 0;
    	} else {
    		$sj = $count - $num;
    	}
    	return $sj;
    }

    //下载数据
    function GrabImage($url,$filename="") {
        if($url=="")
            return '';

        if($filename=="")
            $filename=date("Ymd").mt_rand(1,9999).'.jpg';

        $filepath =  C('UPLOAD_DIR.app_dir').date('Ymd').'/';

        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();

        $fp2=@fopen($filepath.$filename, "a");
        fwrite($fp2,$img);
        fclose($fp2);

        return date('Ymd').'/'.$filename;
    }
?>