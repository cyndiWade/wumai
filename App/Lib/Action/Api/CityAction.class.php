<?php

/**
 * 城市信息
 */
class CityAction extends ApiBaseAction {
	
	protected  $is_check_rbac = false;		//当前控制是否开启身份验证
	
	protected  $not_check_fn = array();	//登陆后无需登录验证方法
	
	//和构造方法
	public function __construct() {
		parent::__construct();
	
	}
	
	//初始化数据库连接
	protected  $db = array(
		'City' => 'City'
	);
	
	public function index() {
	   $data = $this->db['City']->get_data_by_parent_id(0);
	   $result = array();

	   if (!empty($data)) {
	      foreach($data as $key=>$val) {
	          $result[] = array(
	          	'city_id' => $val['id'],
	            'city_name' => $val['title']
	          );
	      } 
	      parent::callback(C('STATUS_SUCCESS'),'获取成功',$result);
	   } else {
	       parent::callback(C('STATUS_NOT_DATA'),'没有数据',$result);
	   }
	  
	}
	
	
	
	
	
	
}

?>