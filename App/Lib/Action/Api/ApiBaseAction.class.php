<?php

/**
 * Api接口--基础类
 */
class ApiBaseAction extends AppBaseAction {

	protected  $is_check_rbac = true;		//当前控制是否开启身份验证
	
	protected  $not_check_fn = array();		//登陆后无需登录验证方法
	
	protected $request;						//获取请求的数据
	
	//构造方法
	public function __construct() {
		
		parent:: __construct();			//重写父类构造方法
		
		//全局系统变量
		$this->global_system();
		
		//初始化用户数据
		$this->check_system_info();

	}
	
	private function global_system () {
		$this->request['token'] = $this->_post('token');		//身份验证的token
		//$this->request['token'] = "UWRSbwgxBWsHNFVhAGUFYgUxA2gEaFUxAjxbO1E0CWRRbAY1BSBXMVdlUngNYVc0";
		$this->request['verify'] = $this->_post('verify');					//短信验证码
		
		//下面开始写$thi->
		
		//$this->oUser = (object) $sess
        ion_userinfo;
		
	}
	
	private function check_system_info() {
		
		if ($this->is_check_rbac == true && !in_array(ACTION_NAME,$this->not_check_fn)) {
			
			$this->deciphering_user_info();
			
			$check_result = $this->init_check($this->oUser);
			
			if ($check_result['status'] == false) parent::callback(C('STATUS_RBAC'),$check_result['message']);
		} 
	}
	

	/**
	 * 解密客户端秘钥，获取用户数据
	 */
	private function deciphering_user_info() {
		//获取加密身份标示
		$identity_encryption = $this->request['token'];	
		
		//解密获取用户数据
		$decrypt = passport_decrypt($identity_encryption,C('UNLOCAKING_KEY'));	
		$user_info = explode(':',$decrypt);		
		$uid = $user_info[0];				//用户id
		$account = $user_info[1];		//用户账号
		$date = $user_info[2];			//账号时间

		//安全过滤
		if (count($user_info) < 3) $this->callback(C('STATUS_OTHER'),'身份验证失败');
		if (countDays($date,date('Y-m-d'),1) >= 30 ) $this->callback(C('STATUS_NOT_LOGIN'),'登录已过期，请重新登录');		//钥匙过期时间为30天

		//去数据库获取用户数据
		//$user_data = $this->db['d']->field('id,account,nickname')->where(array('id'=>$uid,'status'=>0,'is_del'=>0))->find();
		$user_data = D('Users')->get_available_pt_user_info($account);
		
		if ($user_data ==  false || $account != $user_data['account']) {
			parent::callback(C('STATUS_NOT_DATA'),'此用户不存在，或被禁用');
		} else {
			$this->oUser = (object) $user_data;
		}

	}
	
	
	/**
	 * 短信验证模块
	 * @param String $telephone		//验证的手机号码
	 * @param Number $type				//验证类型：1为注册验证
	 */
	protected function check_verify ($telephone,$type) {
	
		$Verify = $this->db['Verify'];
		$verify_code = $this->request['verify'];		//短信验证码
	
		$shp_info = $Verify->seek_verify_data($telephone,$type);
	
		//手机验证码验证
		if (empty($shp_info)) {
			self::callback(C('STATUS_NOT_DATA'),'验证码不存在');
		} elseif ($verify_code != $shp_info['verify']) {
			self::callback(C('STATUS_OTHER'),'验证码错误');
		} elseif ($shp_info['expired'] - time() < 0 ) {
			self::callback(C('STATUS_OTHER'),'验证码已过期');
		}
		//把验证码状态变成已使用
		$Verify->save_verify_status($shp_info['id']);
	}
	
	/*
	 * author zhucc 任务触发
	 *  1.完成组册 2.每天登入 3.发表话题 4.文明点赞 5.评论话题 6.参与文明PK 7.邀请好友 8.分享给朋友 9.给建议
	 */

    protected function end_integral_all_info($user_id,$type)
    {
        $info = $this->checkIntegral_info($type);
        $num = $this->checkIntegral_num($user_id,$type);
        if($info['status']==0 && $num < $info['num'])
        {
            $this->insert_sameday_info($user_id,$type);
        }
    }

    //封装方法
    private function checkIntegral_info($type)
    {
        return D('IntegralAll')->where(array('id'=>$type))->find();
    }

    //检测是否达到上限
    private function checkIntegral_num($user_id,$integral_id)
    {
        $where = array('user_id'=>$user_id,'integral_id'=>$integral_id,'sameday'=>strtotime(date('Y-m-d')));
        return D('IntegralSameday')->where($where)->count();
    }

    //输入数据库
    private function insert_sameday_info($user_id,$integral_id)
    {
        $insert_arr = array(
            'sameday'=>strtotime(date('Y-m-d')),
            'user_id'=>$user_id,
            'integral_id'=>$integral_id,
            'status'=>0
        );
        D('IntegralSameday')->add($insert_arr);
    }

    //检测头像
    private function check_head_imgers($head_img)
    {
        if(substr($head_img,0,4)=='http')
        {
            return $head_img;
        }else{
            return C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_dir').$head_img;
        }
    }

    //检测背景
    private function check_background_img($background_img)
    {
        if($background_img==C('UPLOAD_DIR.default_background_img'))
        {
            return C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_image').C('UPLOAD_DIR.default_background_img');
        }else{
            return C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_dir').$background_img;
        }
    }

    public function cancel_info($id)
    {
        $user_info = D('Users')->where(array('id'=>array('eq',$id)))->find();

        $result = array(
            'id'=>$user_info['id'],
            'account'=>$user_info['account'],
            'nickname'=>$user_info['nickname'],
            'city_id' => $user_info['city_id'],
            'title'=> $this->getCity($user_info['city_id']),
            'head_img'=>$this->check_head_imgers($user_info['head_img']),
            'sex'=>$user_info['sex'],
            'background_img'=>$this->check_background_img($user_info['background_img']),
            'integral'=>$user_info['integral'],
            'interest'=>$user_info['interest'],
            'name'=>$user_info['name'],
            'user_age'=>$user_info['age'],
            'address'=>$user_info['address'],
            'address_phone'=>$user_info['address_phone'],
            'last_login_time'=>date('Y-m-d H:i:s',$user_info['last_login_time']),
            'login_count'=>$user_info['login_count'],
            'create_time'=>date('Y-m-d H:i:s',$user_info['create_time']),
        );

        return $result;
    }

    //返回城市
    public function getCity($id)
    {
        $city = D('City')->where(array('id'=>$id))->getField('title');
        if($city!='')
        {
            return $city;
        }else{
            return '全国';
        }
    }

    //查询城市ID
    public function getCityTitle_id($str)
    {
        if($str=='')
            return 0;

        $id = D('City')->where(array('title'=>array('like',$str.'%')))->getField('id');

        if($id!='')
        {
            return $id;
        }else{
            return 0;
        }
    }
}


?>