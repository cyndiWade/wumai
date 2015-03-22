<?php
/**
 * 推送管理
 */
class PushAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '推送管理';

	//初始化数据库连接
	protected  $db = array(
        'PushLog' => 'PushLog'
	);

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));
		
		$this->_init_data();
	}
	
	private function _init_data () {
	    
	} 
	
	
	//发送给所有的用户
	public function seed_to_all () {
	  //
	    $result = array();
	    
	    if ($this->isPost()) {
	        $push_content = $this->_post('push_content');
	        $this->push($push_content);
	        $this->db['PushLog']->add_log(0,$push_content);
	    }   
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'群体推送',
	        'title_name'=>'群体推送',
	        'add_name'=>'群体推送'
	    ));
	     
	    //parent::data_to_view($result);
	    $this->display();
	}
	
	
	public function seed_to_user () {
	    
	    if ($this->isPost()) {
	        $push_content = $this->_post('push_content');
	        $user_id = $this->_get('user_id');
	        
	        $this->db['PushLog']->add_log(1,$push_content,$user_id);
	        $this->push_user($user_id,$push_content);
	    }
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'个推',
	        'title_name'=>'个推',
	        'add_name'=>'个推'
	    ));
	    
	    //parent::data_to_view($result);
	    $this->display();
	}
	
}