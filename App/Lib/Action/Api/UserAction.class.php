<?php

/**
 * 用户
 */
class UserAction extends ApiBaseAction {
	
	protected  $is_check_rbac = true;		//当前控制是否开启身份验证
	
	protected  $not_check_fn = array();	//登陆后无需登录验证方法
	
	//初始化数据库连接
	protected  $db = array(
	    'Users' => 'Users',
	);
	
	//和构造方法
	public function __construct() {
		parent::__construct();
		$this->_init_data();
	}
	
    public function _init_data() {
        
    }
	
    public function get_user_login_info_for_token () {
        $identity_encryption = $this->request['token'];

        parent::end_integral_all_info($this->oUser->id,2);

        parent::end_integral_all_info($this->oUser->id,10);

        parent::callback(C('STATUS_SUCCESS'),'获取用户成功',parent::cancel_info($this->oUser->id),array('token'=>$identity_encryption));
    }
	
	
	
	
}

?>