<?php

//推送日志
class PushLogModel extends ApiBaseModel {
	
	public function __construct() {
		parent::__construct();
	}

	//获取用户日志数据
	public function get_user_log_list ($condition = array(),$fields = '*',$list_rows = 500,$order_by = '') {
	    $list_data = $this->get_spe_page_data($condition,$fields,$list_rows,$order_by,false);
	    
	    $list = $list_data['list'];
	  
	    $this->set_all_time($list, array('time'));
	    
	    return $list;
	}
	
	//获取系统日志数据
	public function get_system_log_list () {
	    $list_data = $this->get_spe_data(array('type'=>0),'*',0,500,'id DESC');
	    
	    $this->set_all_time($list_data, array('time'));
	    
	    return $list_data;
	}
	
	
}

?>
