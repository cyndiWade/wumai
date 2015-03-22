<?php
/**
 *	后台用户管理
 *
 */
class UserAction extends AdminBaseAction {
   
	private $module_name = '系统管理';
	
	protected  $db = array(
		'Users' => 'Users'		
	);
	
	/**
	 * 构造方法
	 */
	public function __construct() {
		parent::__construct();
		
		parent::global_tpl_view(array('module_name'=>$this->module_name));
	}
	
	//用户列表	
	public function index () {
		$Users = D('Users');
		$user_status = C('ACCOUNT_STATUS');		//状态
		$user_list = $Users->seek_all_data();

		foreach ($user_list AS $key=>$val) {
			$user_list[$key]['status_info'] = $user_status[$val['status']];
		}
		
		parent::global_tpl_view( array(
				'action_name'=>'账号管理',
				'title_name'=>'账号列表',
				'add_name' => '添加账号'
		));
		$this->assign('user_list',$user_list);
		$this->display();
	}
	
	/**
	 * 修改用户账号状态
	 */
	public function user_status () {
		$Users = D('Users');
		$id = $this->_get('id');
		$status = $this->_get('status');
		$Users->status = $status;
		$Users->save_one_data(array('id'=>$id)) ? $this->success('已修改') : $this->error('没有做出修改');
	}
	
	
	public function del_account() {
		$id = $this->_get('id');
		$Users = $this->db['Users'];
		$Users->is_del = 1;
		$Users->save_one_data(array('id'=>$id)) ? $this->success('删除成功！') : $this->error('删除失败，请稍后再试！');
	}
	
	/**
	 * 修改用户账号密码
	 */
	public function modifi_password () {
		$password = $this->_post('password');
		$new_password = $this->_post('new_password');
		$re_new_password = $this->_post('re_new_password');
		import("@.Tool.Validate");
		$Users = D('Users');
		//数据过滤
		if (Validate::checkNull($password)) $this->error('密码不得为空');
		if (Validate::checkNull($new_password)) $this->error('新密码不得为空');
		if (Validate::checkNull($re_new_password)) $this->error('重复密码不得为空');
		if (!Validate::checkEquals($new_password,$re_new_password)) $this->error('新密码不一致');
		//读取用户数据
		$user_info = $Users->get_user_info(array('id'=>$this->oUser->id,'is_del'=>0));

		//验证密码
		if (pass_encryption($password) != $user_info['password']) {
			$this->error('原密码错误！');

		} else {//密码修改
			
			$mes=$Users->modifi_user_password($this->oUser->id,pass_encryption($new_password));
			if ($mes == true)	{ 
					$this->success('密码修改成功！新密码为 '.$new_password);
			} else {
				$this->success('密码修改失败！');
			}
			
		} 
	}
	
	
	/**
	 * 个人中心模块
	 */
	public function personal () {

		$this->display();
	}

	
	
	/* 注册用户编辑 */
	public function edit () {
		$id = $this->_get('id');				//id
		$act = $this->_get('act');			//动作
		$type = $this->_post('type');
		$Users = D('Users');			//注册用户表
	
		switch ($act) {
			case 'add' :
				if ($this->isPost()) {
					$this->check_data($act);		//验证提交数据			
					/* 验证账号是否存在 */
					$account_is_have = $Users->account_is_have($_POST['account']);
					if ($account_is_have) $this->error('此账号已存在');
				
					$Users->create();
					//$user_id = $Users->add_account(C('ACCOUNT_TYPE.ADMIN'));
					if (empty($type)) {
						$type = C('ACCOUNT_TYPE.ADMIN');
					}
					$user_id = $Users->add_account($type);
					
					$user_id ? $this->success('添加成功！') : $this->error('添加失败，请重新尝试！');
					exit;
				}
				$tpl = 'account_add';	//模板名称
				break;
	
			case 'update' :
				if ($this->isPost()) {
					$this->check_data($act);		//验证提交数据
						
					$Users->create();
					if (!Validate::checkNull($_POST['password_old'])) {
						$Users->password = pass_encryption($_POST['password_old']);
					}
					$Users->update_user_info($id) ? $this->success('修改成功！') : $this->error('没有做出修改');
					exit;
				}
				//获取用户数据
				$member_info = $Users->seek_account_info($id);
				if (empty($member_info)) $this->error('此用户不存在');
	
				$this->assign('member_info',$member_info);
				$tpl = 'account_update';	//模板名称
				break;
	
			case 'delete' :
				$Users->del(array('id'=>$id)) ? $this->success('删除成功！') : $this->error('删除失败，请重新尝试！');
				exit;
				break;
	
			default:
				$this->error('非法操作');
				exit;
		}
	
		parent::global_tpl_view( array(
				'action_name'=>'添加用户',
				'title_name'=>'用户列表',
				'title_name' => '编辑账号'
		));
		$this->display($tpl);
	}
	
	
	//验证提交数据
	private function check_data($act) {
		import("@.Tool.Validate");							//验证类
	
		if ($act == 'add') {
			/* 账号验证 */
			if (Validate::checkNull($_POST['account'])) $this->error('账号不得为空');
			if (!Validate::checkAccount($_POST['account'])) $this->error('账号必须以字母开头,只能是字符与数字组成,不得超过30位');
	
			if (Validate::checkNull($_POST['password'])) $this->error('昵称不得为空');
			if (!Validate::checkEquals($_POST['password'],$_POST['password_affirm'])) $this->error('二次输入的密码不相同');
		} elseif ($act == 'update') {
			if (!Validate::checkNull($_POST['password_old'])) {
				if (!Validate::checkEquals($_POST['password_old'],$_POST['password_affirm'])) $this->error('二次输入的密码不相同');
			}
	
		}
	
	}
	
	
	
	//对原账号进行双MD5处理
	public function set_pass_two_md5() {
		$users = $this->db['Users']->select();
		if (!empty($users)) {
			foreach ($users as $key=>$val) {
				$now_data = $this->db['Users']->where(array('id'=>$val['id']))->find();
				$new_password = md5($now_data['password']);
				$this->db['Users']->where(array('id'=>$val['id']))->save(array('password'=>$new_password));
			}
		}
		//dump($users);
	}
	
	
	
	

}