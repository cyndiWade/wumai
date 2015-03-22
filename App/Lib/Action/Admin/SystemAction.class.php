<?php
/**
 * 网站管理
 */
class SystemAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '网站设置';
	
	//初始化数据库连接
	protected  $db = array(
		'SystemBase'=>'SystemBase',
		'SystemFinance'=>'SystemFinance'
	);
	
	
	private static $system_id ;
	
	private static $system_finance_id;

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));

		self::$system_id = C('WEB_SYSTEM.base_id');
		
		self::$system_finance_id =C('WEB_SYSTEM.finance_id');
	}
	
	
	
	//基本设置查看
	public function index () {
	
		
		$base_data = $this->db['SystemBase']->where(array('id'=>self::$system_id))->select();
		
		parent::public_file_dir($base_data,'web_logo','images/');
		
		parent::data_to_view($base_data[0]);
		parent::global_tpl_view( array(
				'action_name'=>'基本设置',
				'title_name' =>'基本设置'
		));
		$this->display();
	}
	
	//基本设置编辑
	public function edit () {
		$act = $this->_get('act');						//操作类型
		$SystemBase= $this->db['SystemBase'];			//系统基本表
		
		if ($act == 'save_base') {
			if ($this->isPost()) {
				
				$upload_dir = C('UPLOAD_DIR');
				$dir = $upload_dir['web_dir'].$upload_dir['image'];
				$logo_files = $_FILES['web_logo_images'];
				$upload_result = parent::upload_file($logo_files,$dir);
				
				$SystemBase->create();	

				if ($upload_result['status'] == true) {
					$SystemBase->web_logo = $upload_result['info'][0]['savename'];
				}
				
				$SystemBase->where(array('id'=>self::$system_id))->save();	
			}
			
			$this->redirect('Admin/System/index');
			
			
		} elseif ($act == 'save_finance') {
			
			if ($this->isPost()) {
				$this->db['SystemFinance']->create();
				$this->db['SystemFinance']->where(array('id'=>self::$system_finance_id))->save();
			}
			
			$this->redirect('Admin/System/finance');
		}
		
	}
	
	
	//财务设置
	public function finance () {
		
		$finance_data = $this->db['SystemFinance']->where(array('id'=>self::$system_finance_id))->find();
		
		parent::data_to_view($finance_data);
		parent::global_tpl_view( array(
				'action_name'=>'财务设置',
				'title_name' =>'财务设置'
		));
		$this->display();
	}
    
}