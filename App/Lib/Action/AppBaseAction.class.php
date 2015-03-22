<?php

/**
 * 	项目---核心类
 *	 所有此项目分组的基础类，都必须继承此类
 */
class AppBaseAction extends GlobalParameterAction {
	
	/**
	 * 构造方法
	 */
	public function __construct() {
		//G('begin'); 							// 记录开始标记位（运行开始）
		
		parent::__construct();
		
		//初始化数据库连接
		$this->db_init();

		$this->global_system();		

	}
	
	private function global_system() {

		//系统基本属性
		$system_base_data = D('SystemBase')->where(array('id'=> C('WEB_SYSTEM.base_id')))->select();
		$this->global_system = $system_base_data[0];
		
		//短信属性
		$system_sms = D('SystemSms')->where(array('id'=>C('WEB_SYSTEM.sms_id')))->find();
		$this->global_sms = $system_sms;
		
		$this->global_tpl_view(array(
			//网站公共的资源路径
			'Global_Resource_Path'=>C('LocalHost').'/'.APP_PATH.'Public/Global/'	
		));
	}
	
	
	//初始化DB连接
	private function db_init() {
		foreach ($this->db as $key=>$val) {
			if (empty($val)) continue;
			$this->db[$key] = D($val);
		}
		
	}
	

	/**
	 * 短信发送类
	 * @param String $telephone  电话号码
	 * @param String $msg			短信内容
	 * @return Array  						$result[status]：Boole发送状态    $result[info]：ARRAY短信发送后的详细信息 	$result[msg]：String提示内容
	 */
// 	protected function send_shp ($telephone,$msg) {
// 		//执行发送短信
// 		import("@.Tool.SHP");	//SHP短信发送类
// 		$SHP = new SHP(C('SHP.NAME'),C('SHP.PWD'));			//账号信息
// 		$send = $SHP->send($telephone,$msg);		//执行发送
// 		return $send;
// 	}
	protected function send_shp ($telephone,$msg) {
		
		if ($this->global_sms == true) {
			$shp_type = $this->global_sms['sms_type']; 
			$shp_name = $this->global_sms['sms_account'];
			$shp_password = $this->global_sms['sms_pass'];
		} else {
			$shp_type = C('SHP.TYPE');
			$shp_name = C('SHP.NAME');
			$shp_password = C('SHP.PWD');		 
		}

		
		switch ($shp_type) {
			case 'SHP' :
				import("@.Tool.SHP");				//SHP短信发送类
				$SHP = new SHP($shp_name,$shp_password);			//账号信息
				$send = $SHP->send($telephone,$msg);		//执行发送
				break;
			case 'RD_SHP':
				import("@.Tool.RD_SHP");		//RD_SHP短信发送类
				$SHP = new RD_SHP($shp_name,$shp_password);			//账号信息
				$send = $SHP->send($telephone,$msg);		//执行发送
				break;
			default:
				exit('illegal operation！');	
		}
		return $send;
	}
	
	
	/**
	 * 统一数据返回
	 * @param unknown_type $status
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 */
	protected function callback($status, $msg = 'Yes!',$data = array(),$extend=array()) {
		$return = array(
				'status' => $status,
				'msg' => $msg,
				'data' => $data,
				'num' => count($data),
		);
		if (!empty($extend) && is_array($extend)) {
			foreach ($extend as $key=>$val) {
				$return[$key]  = $val;
			}
		}
		
		header('Content-Type:application/json;charset=utf-8');
	//	header("Content-type: text/xml;charset=utf-8");
		//header('charset=utf-8');	
		//die(json_encode($return));
		exit(JSON($return));
	}
	
	
	/**
	 * 全局模板变量
	 */
	protected function global_tpl_view (Array $extend = array()) {
	
		if (is_array($extend)) {
			foreach ($extend as $key=>$val) {
				$this->global_tpl_view[$key] = $val;
			}
		}
			
		//写入模板
		$this->assign('global_tpl_view',$this->global_tpl_view);
	}
	
	
	/**
	 * 传出数据到view层
	 * @param Array $view_data
	 */
	protected function data_to_view(Array $view_data = array())
	{
		//添加数据
		if (is_array($view_data) && !empty($view_data)) {
	
			foreach ($view_data as $key => $val) {
				$this->view_data[$key] = $val;
			}
	
		} 
		//注入变量到视图层
		$this->assign('view_data',$this->view_data);
	}
	

	//设置域->分组下的session
	protected function set_session ($data) {
		$_SESSION[C('SESSION_DOMAIN')][GROUP_NAME] = $data;
	}
	
	//获取域->分组下的session
	protected function get_session ($key) {
		return $_SESSION[C('SESSION_DOMAIN')][GROUP_NAME][$key];
	}
	
	protected function get_group_session($GROUP_NAME) {
		return $_SESSION[C('SESSION_DOMAIN')][$GROUP_NAME];
	}
	
	//删除域->分组下的session
	protected function del_session ($key) {
		if ($key == GROUP_NAME) {	//如果key和分组名相同，则删除此分组下所有数据
			unset($_SESSION[C('SESSION_DOMAIN')][GROUP_NAME]);
		} else {
			unset($_SESSION[C('SESSION_DOMAIN')][GROUP_NAME][$key]);
		}	
	}
	
	
	
	/**
	 * 初始化RBAC方法
	 */
	protected function init_rbac() {
		import("@.Tool.RBAC"); 	//权限控制类库
		/* 初始化数据 */
		$Combination = new stdClass();
	
		/* 数据表配置 */
		$Combination->table_prefix =  C('DB_PREFIX');
		$Combination->node_table = C('RBAC_NODE_TABLE');
		$Combination->group_table = C('RBAC_GROUP_TABLE');
		$Combination->group_node_table = C('RBAC_GROUP_NODE_TABLE');
		$Combination->group_user_table = C('RBAC_GROUP_USER_TABLE');
	
		/* 方法配置 */
		$Combination->group = GROUP_NAME;					//当前分组
		$Combination->module = MODULE_NAME;				//当前模块
		$Combination->action = ACTION_NAME;					//当前方法
		$Combination->not_auth_group = C('NOT_AUTH_GROUP');			//无需认证分组
		$Combination->not_auth_module = C('NOT_AUTH_MODULE');		//无需认证模块
		$Combination->not_auth_action = C('NOT_AUTH_ACTION');			//无需认证操作
	
		RBAC::init($Combination);		//初始化数据
	}
	
	
	//初始化用户数据
	protected function init_check($user_info) {
		//$this->init_rbac();
		//$this->is_check_rbac();
		if (C('USER_AUTH_ON') == true) {	//权限验证开启
				
			//当前的Action开启RBAC权限
			if ($this->is_check_rbac == true) {
	
				//当前Action里放行无需验证的方法
				if (in_array(ACTION_NAME,$this->not_check_fn) == true) {
					return array('status'=>true,'message'=>'放行，本方法无需验证');
				}
	
				if (empty($user_info)) {
					return array('status'=>false,'message'=>'未登陆，请先登陆');
				}
	
				/* 对于不是管理员的用户进行权限验证 */
				if (in_array($user_info->account,explode(',',C('ADMIN_AUTH_KEY')))) {
					return array('status'=>true,'message'=>'本账号无需验证');
				} else {
					//初始化rbac
					$this->init_rbac();
					/* RBAC权限验证 */
					$check_result = RBAC::check($user_info->id);
						
					return array('status'=>$check_result['status'],'message'=>$check_result['message']);
				}
	
			} else {
				return array('status'=>true,'message'=>'放行，本Action验证关闭');
			}
				
		} else {
			if ($this->is_check_rbac == true) {
				//当前Action里放行无需验证的方法
				if (in_array(ACTION_NAME,$this->not_check_fn) == true) {
					return array('status'=>true,'message'=>'放行，本方法无需验证');
				}
				
				if (empty($user_info)) {
					return array('status'=>false,'message'=>'未登陆，请先登陆');
				} 
				
				return array('status'=>true,'message'=>'放行,验证通过');
				
			} else {
				return array('status'=>true,'message'=>'放行，权限验证已关闭。');
			}
		}
	
	}
	
	
	/**
	 * 手动验证当前用户权限
	 * @param String $module		//验证模块名
	 * @param String $action			//验证分组名
	 */
	protected function chenk_user_rbac ($module,$action,$group = GROUP_NAME) {
		/* RBAC权限系统开启 */
		if (C('USER_AUTH_ON') == true) {
			$this->init_rbac();		//RBAC权限控制类库
				
			$assign = new stdClass();
			$assign->group = $group;									//当前分组
			$assign->module = $module;							//当前模块
			$assign->action = $action;								//当前方法
			$assign->table_prefix =  C('DB_PREFIX');			//表前缀
	
			$assign->not_auth_group = C('NOT_AUTH_GROUP');			//无需认证分组
			$assign->not_auth_module = C('NOT_AUTH_MODULE');		//无需认证模块
			$assign->not_auth_action = C('NOT_AUTH_ACTION');			//无需认证操作
			RBAC::init($assign);		//初始化数据
	
			/* 对于不是管理员的用户进行权限验证 */
			if (!in_array($this->oUser->account,explode(',',C('ADMIN_AUTH_KEY')))) {
				/* RBAC权限验证 */
				$check_result = RBAC::check($this->oUser->id);
				return array('status'=>$check_result['status'],'message'=>$check_result['message']);
			} else {
				return array('status'=>true,'message'=>'放行，管理员账号无需验证。');
			}
		} else {
			return array('status'=>true,'message'=>'放行，权限验证已关闭。');
		}
	}
	
	
	/**
	 * 上传文件
	 * @param Array   $file  $_FILES['pic'  上传的数组
	 * @param Array   $type   上传文件类型  
	 * @return Array  上传成功返回文件保存信息，失败返回错误信息
	 */
	protected function upload_file($file,$size = 3145728000,$type=array('jpg','gif','png','jpeg','mp4','MP4')) {
	    import('@.ORG.Util.UploadFile');				//引入上传类

	    //上传文件目录
	    $dir =  C('UPLOAD_DIR.domain_dir').C('UPLOAD_DIR.app_dir');
	    
	    $upload = new UploadFile();
	    $upload->maxSize  =  $size;					// 设置附件上传大小
	    $upload->allowExts  = $type;				// 上传文件的(后缀)（留空为不限制），，
	    //上传保存
	    $upload->savePath =  $dir;					// 设置附件上传目录
	    $upload->autoSub = true;					// 是否使用子目录保存上传文件
	    $upload->subType = 'date';					// 子目录创建方式，默认为hash，可以设置为hash或者date日期格式的文件夹名
	    $upload->saveRule =  'uniqid';				// 上传文件的保存规则，必须是一个无需任何参数的函数名
	
	    //执行上传
	    $execute = $upload->uploadOne($file);
	    //执行上传操作
	    if(!$execute) {						// 上传错误提示错误信息
	        return array('status'=>false,'info'=>$upload->getErrorMsg());
	    }else{	//上传成功 获取上传文件信息
	        return array('status'=>true,'info'=>$execute);
	    }
	}
	
	
	/**
	 * 组合图片外部访问地址
	 * @param Array $arr		 //要组合地址的数组
	 * @param String Or Array	 //组合的字段key  如：pic 或  array('pic','head')
	 */
	protected function public_file_dir (Array &$arr,$field) {
	    
	    $public_file_dir =  C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_dir');
	    //递归
	    if (is_array($field)) {
	        for ($i=0;$i<count($field);$i++) {
	            self::public_file_dir($arr,$field[$i],$public_file_dir);
	        }
	    } else {
	        foreach ($arr AS $key=>$val) {
	            if (empty($arr[$key][$field])) continue;
                if (substr($val[$field],0,4)=='http')
                {
                    $arr[$key][$field] = $val[$field];
                }elseif($val[$field]==C('UPLOAD_DIR.default_background_img'))
                {
                    $arr[$key][$field] = C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_image').C('UPLOAD_DIR.default_background_img');
                }else {
                    $arr[$key][$field] = $public_file_dir . $val[$field];
                }
	        }
	    }
	}
	
	
	/**
	 * 推送服务
	 * 发现找不到类
	 * 进入App/vendor/
	 * vim composer.json 写入 { "jpush/jpush": "v3.2.1" }
	 * php composer.phar install
	 */
	protected function push ($content) {
	    import("@.Tool.JgPush");
	    $JgPush = new JgPush();
	    return $JgPush->seedToAll($content);
	}
	
	protected function push_user ($user_id,$content) {
	    import("@.Tool.JgPush");
	    $JgPush = new JgPush();
	    return $JgPush->seedToOne($user_id,$content);
	}
	
}


?>