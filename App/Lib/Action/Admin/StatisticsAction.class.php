<?php
/**
 * 数据统计管理
 */
class StatisticsAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '数据统计管理';
	
	//初始化数据库连接
	protected  $db = array(
		//统计
	);
	
	/**
	 * 构造方法
	 */
	public function __construct() {
	
	   parent::__construct();
	
	   $this->_initData();
	}
	
	private function _initData () {
	    
	    parent::global_tpl_view(array('module_name'=>$this->module_name));
	    
	    
	}
	
	
	public function demo () {
	    $this->display();
	}
	
    public function index () {
       
        
	    parent::global_tpl_view( array(
	        'action_name'=>'统计列表',
	        'title_name'=>'统计列表',
	        'add_name'=>'添加统计'
	    ));
	     
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	public function edit () {
	    $result = array();
	   
	    $Shop = $this->db['Shop'];
	    $act = $this->_get('act');
	    $id = $this->_get('id');
	    
	    if ($act == 'add') {
	        if ($this->isPost()) {
	            $Shop->create();
	            $Shop->add() ? $this->success('添加成功') : $this->error('添加失败请稍后再试！');
	            exit;
	        }
	    } else if ($act == 'update') {
	        if ($this->isPost()) {
	            $Shop->create();
	            $Shop->save_one_data(array('id'=>$id)) ? $this->success('修改成功') : $this->error('修改失败请稍后再试！');
	            exit;
	        } 
	        
	        $result = $Shop->get_one_data(array('id'=>$id));
	        
	    } else if ($act == 'delete') {
	       $Shop->delete_data(array('id'=>$id)) ? $this->success('删除成功') : $this->error('删除失败请稍后再试！');
	        exit;
	    } 
	    
	    parent::data_to_view(array(
	        'shop_status' => $this->shop_status,
	    ));
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'编辑',
	        'title_name'=>'编辑',
	        'add_name'=>'编辑'
	    ));
	    
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	public function image() {
	    $result = array();
	    
	    $ShopPhoto = $this->db['ShopPhoto'];
	    
	    $shop_id = $this->_get('shop_id');
	    $result['primary_key_id'] = $shop_id;
	    
	    $img_types = array(
	    	1 => array('type'=>1,'explain'=>'普通')
	    );
	    $result['img_types'] = $img_types;
	    
	    $img_list = $ShopPhoto->getImgList(array('shop_id'=>$shop_id));
	    $result['img_list'] = $img_list;
	    
		parent::global_tpl_view( array(
	        'action_name'=>'图片编辑',
	        'title_name'=>'图片编辑',
	        'add_name'=>'图片编辑'
	    ));
		
		parent::data_to_view($result);
	    $this->display();
	}
	
	
	/**
	 * AJAX处理上传图片
	 */
	public function ajax_photo_upload() {
	    header('Content-Type:text/html;charset=utf-8');
	
	    if ($this->isPost()) {
	        /* 上传文件目录 */
	        $ShopPhoto = $this->db['ShopPhoto'];		//
	
	        /* 执行上传 */
	        $file = $_FILES['photo_files'];					//上传的文件
	        $primary_key_id = $this->_post('primary_key_id');				//车辆ID
	        $type = $this->_post('type');						//图片类型
	
	        /* 执行上传 */
	        $result = parent::upload_file($file, 5120000);
	
	        /* 上传结果处理 */
	        if ($result['status'] == true) {
	            $ShopPhoto->shop_id = $primary_key_id;
	            $ShopPhoto->type = $type;
	            $ShopPhoto->shop_url = $result['info'][0]['savename'];
	            $state = $ShopPhoto->add();		//写入数据库
	
	            if ($state) {
	                $return['success'] = true;
	                $return['info'] = '保存成功';
	                echo json_encode($return);
	            } else {
	                $return['success'] = false;
	                $return['info'] = '保存失败';
	                echo json_encode($return);
	            }
	        } else {
	            $return['success'] = false;
	            $return['info'] = '上传失败';
	            echo json_encode($return);
	        }
	
	    } else {
	        parent::callback(C('STATUS_ACCESS'),'非法访问！');
	    }
	
	}
	
	
	/**
	 * AJAX删除图片
	 */
	public function ajax_photo_remove () {
	    if ($this->isPost()) {
	        $id = $this->_post('id');
	        $ShopPhoto = $this->db['ShopPhoto'];		
	        $ShopPhoto->delete_real(array('id'=>$id)) ? parent::callback(C('STATUS_SUCCESS'),'删除成功') : parent::callback(C('STATUS_UPDATE_DATA'),'删除失败') ;
	    } else {
	        parent::callback(C('STATUS_ACCESS'),'非法访问！');
	    }
	}

}
