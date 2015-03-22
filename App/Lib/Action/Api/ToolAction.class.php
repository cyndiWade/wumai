<?php

/**
 * 公共工具控制器
 */
class ToolAction extends ApiBaseAction {
	
	
	//每个类都要重写此变量
	protected  $is_check_rbac = false;		//是否需要RBAC登录验证
	
	protected  $not_check_fn = array();	//无需登录和验证rbac的方法名
	
	//控制器说明
	private $module_explain = '公共工具控制器';
	
	//初始化数据库连接
	protected  $db = array(
		'Region'=>'Region',
		
	);
	
	//和构造方法
	public function __construct() {
		parent::__construct();
		
		$this->_init_data();
	}
	
	//初始化需要的数据
	private function _init_data () {

	}
	
	//获取区域数据
	public function get_Region_Data (){
		$parent_id = $this->_post('parent_id');
		$data = $this->db['Region']->get_parent_list($parent_id);
 		if ($data == true) {
 			parent::callback(C('STATUS_SUCCESS'),'获取成功',$data);
 		} else {
 			parent::callback(C('STATUS_NOT_DATA'),'获取失败',$data);
 		}
	}
	

}

?>