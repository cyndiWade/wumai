<?php

class CurlRequest
{
	private	static 	$ch = '';	
	public function  __construct($url){
		$this->initCurl($url);		
	}
	public function  __destruct(){
		self::$ch = null;
	}
	
	/*  初始化curl   
		param  url  url地址
		return curl handler
	*/
	function initCurl($url){
		if(!$url) die('curl param error');
		$ch = curl_init($url);
		//curl_setopt($ch,CURLOPT_HEADER,1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		self::$ch = $ch;
		
		//curl_setopt(self::$ch,CURLOPT_CONNECTTIMEOUT,3);
		curl_setopt(self::$ch,CURLOPT_TIMEOUT,60);
	}

	/* 重新设置curl的url   
		param  url  url地址
		return curl handler		
	*/
	public function setUrl($url){
		$this->initCurl($url);
	}

	/* put数据到接口中
		param  dataArr  put的数据，可以是array，也可以是string('id=abc&key=hh')
		return  200 返回ok   否则返回1000
	*/
	public function  put($dataArr){
		$fields = (is_array($dataArr)) ? http_build_query($dataArr) : $dataArr;
		$cc = self::$ch;
		curl_setopt($cc,CURLOPT_CUSTOMREQUEST,'PUT');
		curl_setopt($cc, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
		curl_setopt($cc, CURLOPT_POSTFIELDS, $fields); 
		$ret = curl_exec($cc);
		$info = curl_getinfo($cc);
		if($info['http_code'] != 200){
			return 1000;die();	
		}
		return 'ok';
	}
	
	/* post数据到接口中
		param  dataArr  put的数据，可以是array，也可以是string('id=abc&key=hh')
		return 200 返回ok   否则返回1000
	*/	
	public function  post($dataArr){
		$fields = (is_array($dataArr)) ? http_build_query($dataArr) : $dataArr;
		$cc = self::$ch;
		curl_setopt($cc,CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($cc, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
		curl_setopt($cc, CURLOPT_POSTFIELDS, $fields); 
		$ret = curl_exec($cc);
		$info = curl_getinfo($cc);
		if($info['http_code'] != 200){
			return 1000;die();	
		}
		return 'ok';
	}

	/*
		function:新版的post函数，主要是解决返回值的问题
	*/		
	public function  newpost($dataArr){
		$fields = (is_array($dataArr)) ? http_build_query($dataArr) : $dataArr;
		$cc = self::$ch;
		curl_setopt($cc,CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($cc, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
		curl_setopt($cc, CURLOPT_POSTFIELDS, $fields); 
		$ret = curl_exec($cc);
		$info = curl_getinfo($cc);
		if($info['http_code'] != 200){
			return 1000;die();	
		}
		return $ret;
	}
	
	/*
		获取接口的数据
		参数：无
		return  200 返回获取的数据结果     否则1000
	*/		
	public function  get(){
		 
		$exec = curl_exec(self::$ch);	 
		
		$info = curl_getinfo(self::$ch);
		if($info['http_code'] != 200){
			return $info['http_code'];//错误的结果
			die();
		}
		if($exec === false){
			return "1000";	
		}
		return $exec;
	}
	/*
		通过接口删除数据
	*/
	public function  del($dataArr){
		//目前delete接口有点问题
		die();
	}
}
