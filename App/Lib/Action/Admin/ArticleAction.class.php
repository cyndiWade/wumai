<?php
/**
 * 文章管理
 */
class ArticleAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '文章管理';
	
	//初始化数据库连接
	protected  $db = array(
        'Article' => 'Article',
	    'PushLog'=>'PushLog'
	);
	
	//文章禁用状态
	private $article_status;

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		$this->_init_data();
	}
	
	private function _init_data () {
	   parent::global_tpl_view(array('module_name'=>$this->module_name));
	   
	   $this->article_status = C('ARTICLE_STATUS');
	}
	
    public function index_report () {
        $result = array();
      
        $where = array();
        $where['is_report'] = 1;
        //分页
        $db_result = $this->db['Article']->getListHtml($where,'*',500,'id DESC');
        
	    $result['list'] = $db_result['list'];
        $result['page_html'] = $db_result['page_html'];
        
	    parent::global_tpl_view( array(
	        'action_name'=>'举报列表',
	        'title_name'=>'举报列表',
	    ));
	     
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	public function edit () {
	    $result = array();
	   
	    $Article = $this->db['Article'];
	    $act = $this->_get('act');
	    $id = $this->_get('id');
	    
	    if ($act == 'add') {
	        if ($this->isPost()) {
	            $Article->create();
	            $Article->add() ? $this->success('添加成功') : $this->error('添加失败请稍后再试！');
	            exit;
	        }
	    } else if ($act == 'update') {
	        if ($this->isPost()) {

	            $Article->create();
	            $update_status = $Article->save_one_data(array('id'=>$id));
	            if ($update_status == true) {
	                
	                $result = $Article->get_one_data(array('id'=>$id));
	                $push_content = $this->_post('push_content');
	                
	                //记录推送记录
	                $this->db['PushLog']->add_log(1,$push_content,$result['user_id']);
	                //推送给用户
	                $this->push_user($result['user_id'],$push_content);
	                
	                $this->success('操作成功！');
	            } else {
	                $this->error('请换个状态！');
	            }
	            exit;
	        } 
	        
	        $result = $Article->get_one_data(array('id'=>$id));

	    } else if ($act == 'delete') {
	       $Article->delete_real(array('id'=>$id)) ? $this->success('删除成功') : $this->error('删除失败请稍后再试！');
	        exit;
	    } 
	    
	    
	    parent::data_to_view(array(
	        'article_status' => $this->article_status,
	    ));
	    
	    parent::global_tpl_view( array(
	        'action_name'=>'驳回编辑',
	        'title_name'=>'驳回编辑',
	    ));
	    
	    parent::data_to_view($result);
	    $this->display();
	}
	
	
	
}