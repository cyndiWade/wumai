<?php
/**
 * 会员管理
 */
class MemberAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '会员管理';
	
	//初始化数据库连接
	protected  $db = array(
		//会员
		'Users'=>'Users',
	    'City'=>'City'
	);
	
	//会员状态
	private $user_status;
	
	private $user_sex_status;
	/**
	 * 构造方法
	 */
	public function __construct() {
	
	   parent::__construct();
	
	   $this->_initData();
	}
	
	private function _initData () {
	    
	    parent::global_tpl_view(array('module_name'=>$this->module_name));
	    
	    $this->user_status = C('USER_STATUS');
	    
	    $this->user_sex_status = C('USER_SEX_STATUS');
	    
	}
	
    public function index () {
        $result = array();
      
        $where = array();
        //分页
        $db_result = $this->db['Users']->getMemberListHtml($where,'*',500,'id DESC');
        
	    $result['list'] = $db_result['list'];
        $result['page_html'] = $db_result['page_html'];
        
	    parent::global_tpl_view( array(
	        'action_name'=>'会员列表',
	        'title_name'=>'会员列表',
	        'add_name'=>'添加会员'
	    ));
	     
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	public function edit () {
	    $result = array();
	   
	    $Users = $this->db['Users'];
	    $act = $this->_get('act');
	    $id = $this->_get('id');
	    
	    if ($act == 'add') {
	        if ($this->isPost()) {
	            $Users->create();
	            $Users->add() ? $this->success('添加成功') : $this->error('添加失败请稍后再试！');
	            exit;
	        }
	    } else if ($act == 'update') {
	        if ($this->isPost()) {
	            $Users->create();
	            $Users->save_one_data(array('id'=>$id)) ? $this->success('修改成功') : $this->error('修改失败请稍后再试！');
	            exit;
	        } 
	        
	        $result = $Users->get_one_data(array('id'=>$id));
	        
	    } else if ($act == 'delete') {
	       $Users->delete_data(array('id'=>$id)) ? $this->success('删除成功') : $this->error('删除失败请稍后再试！');
	        exit;
	    } 
	    
	    $city_datas = $this->db['City']->get_data_by_parent_id(0);
	    parent::data_to_view(array(
	        'city_datas' => $city_datas,
	    ));
	    
	    parent::data_to_view(array(
	        'user_status' => $this->user_status,
	        'user_sex_status' => $this->user_sex_status,
	    ));
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'编辑',
	        'title_name'=>'编辑',
	        'add_name'=>'编辑'
	    ));
	    
	    parent::data_to_view($result);
	    $this->display();
	}
	
	

}
