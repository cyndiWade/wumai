<?php
/**
 * 短信管理控制器
 */
class SmsAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '短信管理';
	
	//初始化数据库连接
	protected  $db = array(
		'SystemSms'=>'SystemSms',
		'Users'=>'Users'
	);
	
	
	private static $sms_id;
	

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));

		self::$sms_id = C('WEB_SYSTEM.sms_id');
		
	}
	
	
	
	
	public function index () {
		
		if ($this->isPost()) {
			$this->db['SystemSms']->create();
			$this->db['SystemSms']->where(array('id'=>self::$sms_id))->save();
		}
	
		$base_data = $this->db['SystemSms']->where(array('id'=>self::$sms_id))->find();
			
		parent::global_tpl_view( array(
				'action_name'=>'短信设置',
				'title_name' =>'短信设置'
		));
		
		$this->data_to_view($base_data);
		$this->display();
	}
	
	
	public function send_msg () {
		$type = $this->_get('type');
		$ids = $this->_get('ids');
			
		$phones = array();
		
		if ($this->isPost()) {
			$msg = $this->_post('msg');
				
			switch ($type) {
				case 0;	//管理员
				
					break;
					
				case 1:	//媒体主
					$media_list = $this->db['Users']->get_user_detail_info_list($type);
					$phones = getArrayByField($media_list,'mt_iphone');
					
					$result = $this->_send($phones,$msg);
					break;
					
				case 2:	//广告主
					$advert_list = $this->db['Users']->get_user_detail_info_list($type);
					$phones = getArrayByField($advert_list,'ad_contact_phone');
					
					$result = $this->_send($phones,$msg);
					break;	
					
				case 3:	//发送给所有人
					$media_list = $this->db['Users']->get_user_detail_info_list(1);
					$advert_list = $this->db['Users']->get_user_detail_info_list(2);
					
					$media_phone = getArrayByField($media_list,'mt_iphone');
					$advert_phone = getArrayByField($advert_list,'ad_contact_phone');
					
					$media_phone ? $media_phone : array();
					$advert_phone ? $advert_phone : array();
				
					$phones = array_merge($media_phone,$advert_phone);
					
					$result = $this->_send($phones,$msg);
					break;		
			}
			exit;
		}
		
		parent::global_tpl_view( array(
			'action_name'=>'发送短信',
			'title_name' =>'发送短信-批量发送'
		));
		
		$this->display('send_msg');
	}
	
	
	//发送实体
	private function _send($phones,$msg) {
		$result = array();
		if ($phones == true) {
			foreach($phones as $ph) {
				$re = parent::send_shp($ph, $msg);
				if ($re['status'] == true) {
					$result['success'][] = $ph;
				}else{
					$result['error'][] = $ph;
				}
			}
		}
		
		return $result;
	}
    
}