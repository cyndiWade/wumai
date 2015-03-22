<?php

/**
 * 短信发送类
 */
class SendMsgAction extends ApiBaseAction {
	
	
	protected  $is_check_rbac = false;		//当前控制是否需要验证RBAC
	
	protected  $not_check_fn = array();		//登陆后无需登录验证方法
	
	//和构造方法
	public function __construct() {
		parent::__construct();
		
		//短信内容
		$this->verify =  mt_rand(111111,999999);				//生成随机验证码
		$this->date = date('Y-m-d H:i');						//日期
		
		$this->telephone = I('telephone');
		$this->type		 = I('type');
		
	}
	
	//初始化数据库连接
	protected  $db = array(
		'Users'=>'Users',
		'Verify'=>'Verify',
	);
	
	private $telephone;			//目标手机号码
	private $verify;					//短信验证码
	private $msg;					//短信内容
	private $date;					//发送时间
	private $send_status;		//发送状态
	private $expired_time = 10;		//分钟


	//短信发送
	private function _send () {
		//请求数据
		if (empty($this->telephone)) {
			parent::callback(C('STATUS_OTHER'),'电话号码不存在');
		} elseif (!preg_match("/^1[358]\d{9}$/", $this->telephone)) {
			parent::callback(C('STATUS_OTHER'),'电话号码格式错误');
		}
	
		//执行发送短信
		$result =  parent::send_shp($this->telephone, $this->msg);
		$this->send_status = $result['status'];		//发送后的状态

	}
	
	
	//保存到数据库
	private function _add_data ($type) {
		$this->_send();	//发送短信
		
		//对发送结果进行处理
		if ($this->send_status == true) {		//发送成功后
			//写入数据库
			$Verify = $this->db['Verify'];					//短信表
			$Verify->telephone = $this->telephone;			//电话号码
			$Verify->verify = $this->verify;				//验证码
			$Verify->expired = strtotime('+'.$this->expired_time.' minute',time());				//过期时间设置为30分钟后
			$Verify->type = $type;														//过期时间设置为30分钟后
			//写入数据库
			$Verify->add() ? parent::callback(C('STATUS_SUCCESS'),'发送成功') : parent::callback(C('STATUS_UPDATE_DATA'),'发送超时');
			//失败处理
		} else {
			parent::callback(C('STATUS_OTHER'),'发送失败，请检查网络');
		}
	}
	
	
	//注册用户短信发送接口
	public function user_account_register () {
		//手机号码 13816958237
		if ($this->isPost()) {
				
			$iphone_is_have = $this->db['Users']->account_is_have($this->telephone);
			if ($iphone_is_have == true) {
				parent::callback(C('STATUS_OTHER'),'对不起此手机已注册');
			}
				
			//$this->msg = $this->verify.'，为您的账号注册验证码，请在'.$this->expired_time.'分钟内完成注册，如非本人注册，请忽略；'.$this->date.'。';
			$this->msg = '您的注册验证码为'.$this->verify.',请在'.$this->expired_time.'分钟内完成注册,如非本人操作请忽略;'.$this->date.'。';
			$this->_add_data(1);
			exit;
		}
		
		
		//$this->assign('name','telephone');
		//$this->display('Login:sendSHP');
	}
	
	
	//找回密码短信发送发送接口
	public function restore_the_password () {
	    if ($this->isPost()) {
	
	        $iphone_is_have = $this->db['Users']->account_is_have($this->telephone);
	        if ($iphone_is_have == false) {
	            parent::callback(C('STATUS_OTHER'),'对不起此手机号码未注册！');
	        }
	
	       // $this->msg = $this->verify.'，为您的账号注册验证码，请在'.$this->expired_time.'分钟内完成注册，如非本人注册，请忽略；'.$this->date.'。';
	        $this->msg = '您的找回密码，验证码为'.$this->verify.',请在'.$this->expired_time.'分钟内完成验证,如非本人操作请忽略;'.$this->date.'。';
	         
	        $this->_add_data(2);
	        exit;
	    }
	
	
	    //$this->assign('name','telephone');
	    //$this->display('Login:sendSHP');
	}
	

	
	
	
	
	
}

?>