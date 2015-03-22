<?php
/**
 * 演示管理
 */
class DemoAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '演示管理';
	
	//初始化数据库连接
	protected  $db = array(
        'Shop' => 'Shop'
	);

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));
		
		$this->_init_data();
	}
	
	private function _init_data () {
	    
	} 
	
    public function index () {
        $result = array();
      
        $where = array();
        $where['is_del'] = 0;
        //分页
        $db_result = $this->db['Shop']->get_spe_page_data($where,'*',500,'id DESC');
        
	    $result['list'] = $db_result['list'];
        $result['page_html'] = $db_result['page_html'];
        
	    parent::global_tpl_view( array(
	        'action_name'=>'商品列表',
	        'title_name'=>'商品列表',
	        'add_name'=>'添加商品'
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
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'编辑',
	        'title_name'=>'编辑',
	        'add_name'=>'编辑'
	    ));
	    
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	
}