<?php

/**
 * 后台核心类--所有后台方法必须继承此类
 */
class AdminBaseAction extends AppBaseAction {
	
	//构造方法
	public function __construct() {
		
		parent:: __construct();			//重写父类构造方法
		
		$this->init_rbac();		//RBAC权限控制类库
		
		//初始化用户数据
		$this->admin_base_init();
		
		//全局系统变量
		$this->global_system();
		
		
	}
	
	

	//初始化用户数据
	private function admin_base_init() {
		/* SESSION信息验证保存 */
		$session_userinfo = parent::get_session('user_info');
		if (!empty($session_userinfo)) {
			$this->oUser = (object) $session_userinfo;					//转换成对象
		}  		

		if (empty($this->oUser) && !in_array(MODULE_NAME,explode(',',C('NOT_AUTH_MODULE')))) {		
			//$this->error('请先登录','/Admin/Login/login');
			$this->error('请先登录',U('/Admin/Login/login'));
			exit;
		}
	
		/* RBAC权限系统开启 */
		if (C('USER_AUTH_ON') == true) {
			/* 对于不是管理员的用户进行权限验证 */
			if (!in_array($this->oUser->account,explode(',',C('ADMIN_AUTH_KEY')))) {	
				/* RBAC权限验证 */
				$check_result = RBAC::check($this->oUser->id);			
				if ($check_result['status'] == false) $this->error($check_result['message']);
			}
		}

	}
	
	
	/**
	 * 全局系统用到的数据
	 */
	private function global_system () {
	
		//初始化局模板变量
		parent::global_tpl_view(array(
				'user_info' => array(
						'nickname' => $this->oUser->nickname
				),
				'button' => array (
						'prve' => C('PREV_URL')
				),
				'prve_url' => C('PREV_URL'),
				
				'path'=>'http://'.$_SERVER['SERVER_NAME'].$path.''.'Public/'.GROUP_NAME.'/',
				
				'group_name' =>GROUP_NAME,
				
				'module_name'=>MODULE_NAME,
				
				'action_name'=>ACTION_NAME,
				
				//网站当前分组资源路径
				'Group_Resource_Path'=>C('LocalHost').'/'.APP_PATH.'Public/'.GROUP_NAME.'/',
				
				//模块级页面路径
				'Module_Resource_Path'=>C('LocalHost').'/'.APP_PATH.'Public/'.GROUP_NAME.'/Module/'.MODULE_NAME.'/',
				
		));
	}
	
	
}


?>