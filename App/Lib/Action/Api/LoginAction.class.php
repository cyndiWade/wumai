<?php

/**
 * 用户登录注册模块
 */
class LoginAction extends ApiBaseAction {
	
	//每个类都要重写此变量
	protected  $is_check_rbac = false;		//当前控制是否开启身份验证
	
	protected  $not_check_fn = array();	//无需登录和验证rbac的方法名
	
	//初始化数据库连接
	protected  $db = array(
		'Users' => 'Users',
		'Verify'=>'Verify',
	);
	
	public function __construct() {
		
		parent:: __construct();			//重写父类构造方法
		
		$this->_init_data();
	}
	
	
	private function _init_data () {
		$this->request['account'] = $_POST['account'];							//用户账号
		$this->request['password'] = $_POST['password'];					//用户密码
		$this->request['password_confirm'] = $_POST['password_confirm'];		//确认密码
	}
	
	
	//登录验证
	public function login () {

		if ($this->isPost()) {
			
			$Users = $this->db['Users'];						//用户模型表
			
			$account = $this->request['account'];				//用户账号
			$password = pass_encryption($this->request['password']);		//用户密码
			
			$this->check_me();									//验证提交数据
			
			//数据库验证用户信息
			$user_info = $Users->get_available_pt_user_info($account);

			if (empty($user_info)) {
				parent::callback(C('STATUS_NOT_DATA'),'此用户不存在，或已被禁用');
			} else {
				if ($password != $user_info['password']) {
					parent::callback(C('STATUS_OTHER'),'密码错误');
				} else {
					
					//生成秘钥
					$encryption = $user_info['id'].':'.$user_info['account'].':'.date('Y-m-d');	//生成解密后的数据
					$identity_encryption = passport_encrypt($encryption,C('UNLOCAKING_KEY'));	//生成加密字符串,给客户端
					
					//更新用户登录信息
					$Users->up_login_info($user_info['id']);
					//查找会员信息

                    //触发完成登陆事件
                    parent::end_integral_all_info($user_info['id'],2);

                    parent::end_integral_all_info($user_info['id'],10);

					//返回给客户端数据
					parent::callback(C('STATUS_SUCCESS'),'登录成功',parent::cancel_info($user_info['id']),array('token'=>$identity_encryption));
				}	
			}
		}
	}
	
	
	
	//用户注册	
	public function register () {	

		if ($this->isPost()) {		
			//初始化数据
			//验证提交数据
			//$this->check_me();		
			$arr['nickname'] = $this->_post('nickname');							//注册账号	
			$arr['password'] = $this->_post('password');							//密码
			$password_confirm = $this->_post('password_confirm');					//密码确认
			$arr['sex'] = $this->_post('user_sex');
			$arr['cell_phone'] = $this->_post('cell_phone');
			$arr['city'] = $this->_post('user_city');

			//密码确认验证
			if ($arr['password'] != $password_confirm)
				parent::callback(C('STATUS_OTHER'),'','二次密码输入不一致');
			
			//短信验证模块
			//parent::check_verify($account,1);			//验证类型1为注册验证
			
			//数据库验证
			$Users = $this->db['Users'];						//用户表模型	
			
			$is_nickname = $Users->nickname_is_have($arr['nickname']);

			if ($is_nickname!='')
				parent::callback(C('STATUS_OTHER'),'','昵称已经存在');

			//手机号唯一
			$is_have = $Users->phone_is_have($arr['cell_phone']);		//查看账号是否存在

			if ($is_have!='')
				parent::callback(C('STATUS_OTHER'),'','此手机号已存在');


            //添加注册用户
            //上传头像
            if($this->_post('head_img')!='')
                $arr['head_img'] = $this->_post('head_img');

			if($_FILES['user_avater']!='')
			{
				$file_list = parent::upload_file($_FILES['user_avater']);
                if($file_list['status']==true)
				    $arr['head_img'] = $file_list['info'][0]['savename'];
			}

            if($arr['head_img']=='')
                $arr['head_img'] = '';

			$id = $Users->add_info($arr,C('ACCOUNT_TYPE.USER'));		//写入数据库

			if ($id) {

                //触发完成注册事件
                parent::end_integral_all_info($id,1);

				//生成秘钥
				$encryption = $id.':'.$arr['cell_phone'].':'.date('Y-m-d');					//生成解密后的数据
				$identity_encryption = passport_encrypt($encryption,C('UNLOCAKING_KEY'));	//生成加密字符串,给客户端
				
				$list = $this->db['Users']->get_id_info($id);

				//返回客户端
				$return_data = array('token' => $identity_encryption,'user_info'=>$list);
				parent::callback(C('STATUS_SUCCESS'),'注册成功',$return_data);
			} else {
				
				parent::callback(C('STATUS_UPDATE_DATA'),'注册失败');
			}


		}
	}

	//注册手机号验证
	public function regeister_cell_veritify()
	{
		$cell_phone = $this->_post('cell_phone');
		$user_val = $this->db['Users']->where(array('phone'=>$cell_phone))->find();
		if($user_val!='')
		{
			parent::callback(C('STATUS_HAVE_DATA'),'手机号已被注册');
		}else{
			parent::callback(C('STATUS_SUCCESS'),'手机号未被注册');
		}
	}

	//第三方登陆接口
    public function regeister_login_order()
    {
        $order_id = $this->_post('order_id');
        $type = $this->_post('type');
        $users = $this->db['Users'];
        //1-微博 2－微信
        switch($type)
        {
            case 1:
                $new_arr['weibo_order_id'] = $where['weibo_order_id'] = $order_id;
                break;
            case 2:
                $new_arr['weixin_order_id'] =  $where['weixin_order_id'] = $order_id;
                break;
            default:
                parent::callback(C('STATUS_DATA_ERROR'),'参数错误','');
                break;
        }
        $value = $users->where($where)->find();
        if($value['id']!='')
        {
            //更新用户登录信息
            $users->up_login_info($value['id']);
            $encryption = $value['id'].':'.$value['account'].':'.date('Y-m-d');
            parent::end_integral_all_info($value['id'],10);
            parent::callback(C('STATUS_SUCCESS'),'登录成功',parent::cancel_info($value['id']),array('token'=>passport_encrypt($encryption,C('UNLOCAKING_KEY'))));
        }else{
            $new_arr['head_img'] = $this->_post('image');
            $new_arr['background_img'] = C('UPLOAD_DIR.default_background_img');
            $new_arr['account'] = $new_arr['nickname'] = $this->_post('nickname');
            $new_arr['user_sex'] = $this->_post('sex');
            $new_arr['create_time'] = $new_arr['update_time'] = time();
            $bool = $users->add($new_arr);
            if($bool)
            {
                //更新用户登录信息
                $users->up_login_info($bool);
                $encryption = $bool.':'.$new_arr['account'].':'.date('Y-m-d');
                parent::end_integral_all_info($bool,2);
                parent::end_integral_all_info($bool,10);
                parent::callback(C('STATUS_SUCCESS'),'登录成功',parent::cancel_info($bool),array('token'=>passport_encrypt($encryption,C('UNLOCAKING_KEY'))));
            }else{
                parent::callback(C('STATUS_DATA_ERROR'),'登陆失败','');
            }
        }
    }

	//验证提交数据
	private function check_me() {
		import("@.Tool.Validate");							//验证类
		//数据验证
		if (Validate::checkNull($this->request['account'])) parent::callback(C('STATUS_OTHER'),'账号为空');
		if ($this->request['account'] != 'admin') {
			if (!Validate::checkPhone($this->request['account'])) parent::callback(C('STATUS_OTHER'),'账号必须为11位的手机号码');
		}
		if (Validate::checkNull($this->request['password'])) parent::callback(C('STATUS_OTHER'),'密码为空');		
	}
	
    //验证注册短信是否正确
	public function check_register_phone_msg() {
	    if ($this->isPost()) {
	        $telephone= $this->_post('telephone');
	        
	        //执行验证
	        parent::check_verify($telephone,1);
	        
	        parent::callback(C('STATUS_SUCCESS'),'验证成功');
	    }
	    
	}
	
	
	//验证找回密码短信接口
	public function check_restore_the_password() {
	    if ($this->isPost()) {
	        $telephone= $this->_post('telephone');
	         
	        //执行验证
	        parent::check_verify($telephone,2);
	         
	        parent::callback(C('STATUS_SUCCESS'),'验证成功');
	    }
	     
	}

    //新增修改密码接口
    public function set_new_password()
    {
        $cellphone = $this->_post('cellphone');
        $password = $this->_post('password');
        $new_password = $this->_post('new_password');
        if($password!=$new_password)
        {
            parent::callback(C('STATUS_DATA_ERROR'),'两次密码不一致','');
        }else{
            $new_pass_info = array('password'=>pass_encryption($new_password));
            $bool = $this->db['Users']->where(array('phone'=>array('eq',$cellphone)))->save($new_pass_info);
            $bool ? parent::callback(C('STATUS_SUCCESS'),'修改成功','') : parent::callback(C('STATUS_SUCCESS'),'修改失败','');
        }
    }
    
}

?>
