<?php
/**
 * 城市管理
 */
class CityAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '城市管理';
	
	//初始化数据库连接
	protected  $db = array(
		'City'=>'City',
	);
	
	private $parent_id;

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));
		
		$this->parent_id = $this->_get('parent_id');
		empty($this->parent_id) ? $this->parent_id = 0 : $this->parent_id;
	}
	
	
	
	//数据列表
	public function index () {
	
		//连接数据库
		$City= $this->db['City'];	
		
		$data = $City->get_data_by_parent_id($this->parent_id);
		
		$data['list']  = $City->get_spe_data(array('parent_id'=>$this->parent_id,'is_del'=>0));
		
		if ($data['list'] == true) {
			foreach ($data['list'] as $key=>$val) {
				
				$parent_info = $City->get_one_data(array('id'=>$val['parent_id']));
				
				$parent_name = $parent_info['title'];
				if ($parent_name == '') {
					$parent_name = '顶层';
				}  
				$data['list'][$key]['parent_name'] = $parent_name;
				if ($val['show_status'] == 1) {
					$data['list'][$key]['show_status_explain'] = '显示';
				} elseif($val['show_status'] == 0) {
					$data['list'][$key]['show_status_explain'] = '不显示';
				}
			}
		}
		
		parent::global_tpl_view( array(
			'action_name'=>'数据列表',
			'title_name'=>'数据列表',
			'add_name'=>'添加'	
		));
		$data['parent_id'] = $this->parent_id;
		
		$City->get_spe_data(array('parent_id'=>$this->parent_id,'is_del'=>0));
		
		$prev_parent_info = $City->get_one_data(array('id'=>$this->parent_id),'parent_id');
		$data['prev_parent_id'] = $prev_parent_info['parent_id'];

		parent::data_to_view($data);
		$this->display();
	}
	
	
	public function edit () {
		$act = $this->_get('act');						//操作类型
		$id = $this->_get('id');						//上一页地址
		$show_status = $this->_get('show_status');		//显示状态
		$prve_url = $this->_post('prev_url');			//上一页地址
		$City= $this->db['City'];		//标签类型表
		
		
		
		if ($act == 'add') {
			if ($this->isPost()) {
				$City->create();
				$City->parent_id = $this->parent_id;
				$City->add() ? $this->success('保存成功！',$prve_url) : $this->error('保存失败请稍后重新尝试！');
				exit;
			}
			$title_name = '添加标签';
		} elseif ($act == 'update') {
			if ($this->isPost()) {
				$City->create();
				$City->save_one_data(array('id'=>$id)) ? $this->success('修改成功！',$prve_url) : $this->error('修改失败请稍后重新尝试！');
				exit;
			}
			$data = $City->get_one_data(array('id'=>$id,'is_del'=>0));
			$title_name = $data['title'].'---编辑';
			parent::data_to_view($data);
			
		} elseif ($act == 'delete') {
			$City->delete_data(array('id'=>$id)) ? $this->success('删除成功') : $this->error('删除错误');
			exit;
		} elseif ($act == 'init_data') {
			
			//$data = '不限,男女,泛';
	
			$title_array = explode(',', $data);
			foreach ($title_array as $key=>$val) {
				$City->parent_id = $this->parent_id;
				$City->title = $val;
				//$City->add();
			}
			$this->success('成功');
			exit;
		} elseif ($act == 'cp_data') {
			//$mubiao_id = 666;
			
			//获取当前父级下的数据
			$list = $City->get_spe_data(array('parent_id'=>$this->parent_id,'is_del'=>0));
			
			foreach ($list as $key=>$val) {
				$City->parent_id = $mubiao_id;
				$City->title = $val['title'];
				$City->val = $val['val'];
				$City->field = $val['field'];
				//$City->add();
			}
			$this->success('成功');
			exit;
		//是否显示	
		}elseif($act == 'is_show') {
			$City->show_status = $show_status;
			$is_up = $City->save_one_data(array('id'=>$id));
			$is_up ? $this->success('修改成功！') : $this->error('修改失败！');
			exit;
		} else {
			$this->error('非法操作');
			exit;
		}
			
		parent::global_tpl_view( array(
				'action_name'=>'编辑',
				'title_name' => $title_name
		));
		
		
		$this->display();
	}
	
	
	
    
}