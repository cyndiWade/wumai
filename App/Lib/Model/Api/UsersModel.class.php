<?php

//用户数据模型
class UsersModel extends ApiBaseModel {
	
	public function get_account_count ($where) {
		return $this->where($where)->count();
	}
	
	//添加账号
	public function add_account($type) {
		//写入数据库
		$this->password = pass_encryption($this->password);
		$time = time();
		$this->last_login_time = $time;
		$this->last_login_ip = get_client_ip();
		$this->create_time = $time;
		$this->update_time = $time;
		$this->type = $type;				//用户类型
		return $this->add();
	}
	

	//注册
	public function add_info($arr,$type)
	{
		$arr_db['nickname'] = $arr['nickname'];
		$arr_db['sex'] = $arr['sex'];
        $arr_db['background_img'] = C('UPLOAD_DIR.default_background_img');
		$arr_db['phone'] = $arr['cell_phone'];
		$arr_db['account'] = $arr['cell_phone'];
		$arr_db['city_id'] = $arr['city'];
		$arr_db['password'] = pass_encryption($arr['password']);
		$arr_db['head_img'] = $arr['head_img']!='' ? $arr['head_img'] : '';
		$arr_db['last_login_time'] = time();
		$arr_db['last_login_ip'] = get_client_ip();
		$arr_db['create_time'] = time();
		$arr_db['update_time'] = time();
		$arr_db['type'] = $type;				//用户类型
		return $this->add($arr_db);
	}
	

	//获取数据
	public function get_id_info($id)
	{
		$info = parent::get_user_info($id);

        parent::public_file_dir($info,array('head_img','background_img'));
        
		return $info;
	}


	//获取普通可用用户信息
	public function get_available_pt_user_info ($account) {
		return $this->where(array('account'=>$account,'type'=>1,'is_del'=>0,'status'=>0))->find();
	}
	
	
	//通过账号验证账号是否存在
	public function account_is_have ($account) {

		return $this->where(array('account'=>$account))->getField('id');
	}
	
	//验证昵称是否存在
	public function nickname_is_have($nickname)
	{
		return $this->where(array('nickname'=>$nickname))->getField('id');
	}

	//通过手机号是否存在
	public function phone_is_have ($phone) {

		return $this->where(array('phone'=>$phone))->getField('id');
	}

	//获取账号数据
	public function get_user_info ($condition) {
		return $this->where($condition)->find();
	}
	
	
	public function get_account ($condition) {
		$con = array('is_del'=>0);
		array_add_to($con,$condition);
		return $this->where($con)->find();
	}
	
	//修改密码
	public function modifi_user_password ($id,$password) {
		return $this->where(array('id'=>$id))->save(array('password'=>$password));
	}
	
	
	//更新登录信息
	public function up_login_info ($uid) {
		
		$time = time();
		$con['last_login_time'] = $time;
		$con['last_login_ip'] = get_client_ip();
		$con['login_count'] = array('exp','login_count+1');
		return $this->where(array('id'=>$uid))->save($con);
	}
	
	
	public function seek_all_data () {
		$data = $this->field('u.id,u.account,u.last_login_time,u.last_login_ip,u.type,u.status')
		->table($this->prefix.'users AS u')
		//->where(array('u.is_del'=>0,'u.type'=>array('neq',0)))
		->where(array('u.is_del'=>0,'u.type'=>array('eq',0)))
		->select();
		parent::set_all_time($data, array('last_login_time'));
		return $data;
	}

	
	public function selectFriend($user_name,$id)
	{
		$where['u.nickname'] = array('like',$user_name.'%');
		$where['u.id'] = array('neq',$id);
		$list = $this->where($where)->table('app_users as u')
		->join('app_city as c on c.id = u.city_id and c.parent_id = 0')
		->field('u.id,u.nickname,u.head_img,c.title')
		->select();

		parent::public_file_dir($list,array('head_img'));

		return $list;
	}
	
	//随机找朋友
    public function get_random_friends($id)
    {
        $list = $this->where(array('u.id'=>array('neq',$id)))
            ->table('app_users as u')->join('app_city as c on c.id = u.city_id')->order('rand()')
            ->field('u.id,u.nickname,u.head_img,c.title')->limit(10)->select();

        $user_friend = D('UserFriends');

        parent::public_file_dir($list,array('head_img'));

        $new_list = array();

        foreach($list as $key=>$value)
        {
            $status = $user_friend->where(array('user_id'=>$id,'friend_id'=>$value['id'],'friend_statis'=>1))->find();
            $new_list[$key] = $value;
            if($status!='')
            {
                $new_list[$key]['is_friend'] = 1;
            }else {
                $new_list[$key]['is_friend'] = 2;
            }
        }

        return $new_list;
    }
}

?>
