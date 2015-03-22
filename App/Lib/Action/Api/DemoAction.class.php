<?php

/**
 * 测试
 */
class DemoAction extends ApiBaseAction {
	
	protected  $is_check_rbac = true;		//当前控制是否开启身份验证
	
	protected  $not_check_fn = array('upload','show_img');	//登陆后无需登录验证方法
	
	//和构造方法
	public function __construct() {
		parent::__construct();
	
	}
	
	//初始化数据库连接
	protected  $db = array(
		
	);
	
	public function index() {
		print_r($this->oUser);
	}
	
	//上传图片
	public function upload () {
	    
	    if ($this->isPost()) {
	        $file_1 = $_FILES['fiel'];
	        
	        //执行上传
	        $status_content = parent::upload_file($file_1);
	        dump($status_content);
	    }
		$this->display('upload');
	}
	
	//显示图片
	public function show_img () {
	    //数据库读出来的数组
	   $arr[] = array(
	       'aa'=>'20150204/54d1923dd6108.jpg',
	       'bb'=>'20150204/54d193acd9739.jpg'
	   );
	   
	   //引用传递后，格式化出来的数组
	   parent::public_file_dir($arr,array('aa','bb'));
	   
	   dump($arr);
	}
	
	
	
	
}

?>