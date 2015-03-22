<?php
/**
 * 后台订单管理
 */
class OrderAction extends AdminBaseAction {
  	
	//控制器说明
	private $module_name = '订单管理';
	
	//初始化数据库连接
	protected  $db = array(
		//新闻媒体订单
		'GeneralizeNewsOrder'=>'GeneralizeNewsOrder',	

		//新闻推广单关联表
		'GeneralizeNewsAccount'=>'GeneralizeNewsAccount',

		//微博推广单	
		'GeneralizeOrder'=>'GeneralizeOrder',
		//微博推广单，订单关联表
		'GeneralizeAccount'=>'GeneralizeAccount',
			
		//微博意向单
		'IntentionWeiboOrder'=>'IntentionWeiboOrder',
		//微博意向单、订单关联边	
		'IntentionWeiboAccount'=>'IntentionWeiboAccount',
			
		//微信推广单
		'GeneralizeWeixinOrder'=>'GeneralizeWeixinOrder',
		//微信推广单，订单关联表
		'GeneralizeWeixinAccount'=>'GeneralizeWeixinAccount',	
			
		//微信意向单表
		'IntentionWeixinOrder'=>'IntentionWeixinOrder',
		//微信意向单，订单关联边
		'IntentionWeixinAccount'=>'IntentionWeixinAccount',
			
		'Users'=>'Users',	//用户账号表
		
		'AccountNews'=>'AccountNews',
		'AccountWeibo'=>'AccountWeibo',
		'AccountWeixin'=>'AccountWeixin',
			
		//订单日志表	
		'OrderLog'=>'OrderLog',
			
		'GeneralizeFiles'=>'GeneralizeFiles',
		'GeneralizeWeixinFiles'	=> 'GeneralizeWeixinFiles',
		'IntentionWeiboFiles'=>'IntentionWeiboFiles',
		'IntentionWeixinFiles'=>'IntentionWeixinFiles'

	);

	private $OrderLog;	
	
	private $Account_Order_Status;
	
	private $where;

	/**
	 * 构造方法
	 */
	public function __construct() {
	
		parent::__construct();
	
		parent::global_tpl_view(array('module_name'=>$this->module_name));
		
		$this->_init_data();
	}
	
	
	private function _init_data () {
		$this->OrderLog = $this->db['OrderLog'];
		
		$this->Order_Status = C('Order_Status');
		
		$this->Account_Order_Status = C('Account_Order_Status');
		
		import("@.Tool.Validate");	//验证类
		
		$this->where['status'] = array('IN',array(1,2,3,4,5));
	}
	
		
	
	//新闻媒体数据列表
	public function news_generalize () {
	
		$GeneralizeNewsOrder = $this->db['GeneralizeNewsOrder'];
		$where = $this->where;
	
		$list = $GeneralizeNewsOrder->get_order_list($where);

		
// 		if ($list == true) {
// 			foreach ($list as $key=>$val) {
// 				//用户
// 				$user_info = $this->db['Users']->get_user_info(array('id'=>$val['users_id']));
// 				$list[$key]['order_user_account'] = $user_info['account'];
				
// 				//媒体账号数
// 				$list[$key]['account_num'] =  $this->db['GeneralizeNewsAccount']->where(array('generalize_id'=>$val['id']))->count();
 			
// 				//状态
// 				$list[$key]['status_explain'] = $this->Order_Status[$val['status']]['explain'];
				
// 			}
// 		}
				
		$data['list'] = $list;
		parent::global_tpl_view( array(
			'action_name'=>'新闻媒体推广单',
			'title_name'=>'新闻媒体推广单',
			'add_name'=>'添加类别'	
		));
	
		parent::data_to_view($data);
		$this->display();
	}
	
	//新闻订单编辑
	public function news_generalize_edit() {
		
		$order_id = $this->_get('id');
		$type = 1;
		$act = $this->_get('act');						//操作类型
		$content = $this->_post('content');				//留言内容
		
		if ($act == 'update') {
			if ($this->isPost()) {
					
				//修改订单状态
				$this->db['GeneralizeNewsOrder']->create();
				$order_status = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->save();
				
				//修改关联边订单状态
				$status = $this->_post('status');
				if ($status == 2) {
					$audit_status = $this->Account_Order_Status[1]['status'];
				} elseif ($status == 3) {
					$audit_status = $this->Account_Order_Status[2]['status'];
				}
				$GeneralizeNewsAccount = $this->db['GeneralizeNewsAccount'];
				$GeneralizeNewsAccount->where(array('generalize_id'=>$order_id))->save(array('audit_status'=>$audit_status));
				
				//创建日志
				if (Validate::checkNull($content) == false) {
					$this->OrderLog->create();
					$is_insert = $this->OrderLog->add_order_log($this->oUser->id,$order_id,$type);
				}
				
				//如果已支付，拒绝就需要退款 20141204 bumtime
				if($status == 3)
				{
					$totalPrice = 0 ;
					$order_info = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->find();
					$adUserID 	= $order_info['users_id'];
					
		    		$accoutList = $this->db['GeneralizeNewsAccount']->where(array('generalize_id'=>$order_id))->field('`account_id`, `price`, `rebate`, `audit_status`, users_id')->select();
		    		//总金额
		    		$tipsInfo = C('MESSAGE_TYPE_ADMIN');
		    		$k =0;
		    		$messageData = array();
		    		foreach ($accoutList  as $value)
		    		{
		    			$totalPrice += getAdMoney($value['price'], 'news', $value['rebate']);
		    			//$totalPrice += $this->getAdMoney($value['price'], 'weibo', $value['rebate']);
		    			
						//取消发站内短信 add by bumtime 20141223
		    		    $messageData[$k]['send_from_id']	=	$this->oUser->id;
						$messageData[$k]['send_to_id']		=	$value['users_id'];
						$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
						$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Media/EventOrder/showNews', array('id'=>$order_id)));
						$messageData[$k]['message_time']	=	time();
						$k++;
		    		}
					//给广告主解冻
		    		D("UserAdvertisement")->setMoney($totalPrice, $adUserID, 1, 1, $order_id);
		    		
		    		//取消发站内短信(to 广告主) add by bumtime 20141223 
	    		  	$messageData[$k]['send_from_id']	=	$this->oUser->id;
					$messageData[$k]['send_to_id']		=	$adUserID;
					$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
					$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Advert/News/generalize_detail', array('order_id'=>$info['order_id'])));
					$messageData[$k]['message_time']	=	time();
					
					if($messageData)
						parent::sendMessageInfo($messageData, 2);			
				}
			}
			
		} elseif ($act == 'update_order') {
			if ($this->isPost()) {
				
				//修改订单状态
				$this->db['GeneralizeNewsOrder']->create();
				$st = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->save();
			}
			
		//当个确认	
		} elseif ($act == 'confirm') {
			$account_id = $this->_get('account_id');	//账号ID
			
			$audit_status = $this->Account_Order_Status[1]['status'];
			$this->db['GeneralizeNewsAccount']->where(array('id'=>$account_id))->save(array('audit_status'=>$audit_status));
			
			alertLocation('',C('PREV_URL'));
			//$this->redirect('/Admin/Order/'.__FUNCTION__.'/'.12313);
		}
		
		
		
		$data['order_id'] = $order_id;
		
		$order_info = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->find();
		$order_info['title'] = stripslashes($order_info['title']);
		$order_info['zf_info'] = stripslashes($order_info['zf_info']);
		
		$data['order_info'] = $order_info;
		
		$order_log_list = $this->OrderLog->get_order_list(array('order_id'=>$order_id,'type'=>$type));
		$data['order_log_list'] = $order_log_list;
		
		//媒体列表
		$account_list = $this->db['GeneralizeNewsAccount']->where(array('generalize_id'=>$order_id))->select();
		if($account_list == true) {
			foreach ($account_list as $key=>$val) {
				$account_data =  $this->db['AccountNews']->get_account_data_one($val['account_id']);
				$account_list[$key]['status_explain'] = $this->Account_Order_Status[$val['audit_status']]['explain'];				
				//$account_list[$key]['status_explain'] = $val['audit_status'];
				$account_list[$key] = array_merge($account_list[$key],$account_data);
			}
		}
		
		$data['account_list'] = $account_list;
		
		//dump($data['order_info']);
		//exit;
		
		parent::global_tpl_view( array(
				'action_name'=>'新闻媒体推广单',
				'title_name'=>'新闻媒体推广单',
				'add_name'=>'添加类别'
		));
		
		parent::data_to_view($data);
		$this->display();
	}
	
	//新闻订单详情
	public function news_generalize_detail () {
		$order_id = $this->_get('id');
		$order_info = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		parent::global_tpl_view( array(
				'action_name'=>'新闻媒体推广单',
				'title_name'=>'新闻媒体推广单',
				'add_name'=>'订单详情'
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	
	
	//微博推广单列表
	public function weibo_generalize () {
	
		$GeneralizeOrder = $this->db['GeneralizeOrder'];
		$where = $this->where;
		$list = $GeneralizeOrder->get_order_list($where);
	

// 		if ($list == true) {
// 			foreach ($list as $key=>$val) {
// 				//用户
// 				$user_info = $this->db['Users']->get_user_info(array('id'=>$val['users_id']));
// 				$list[$key]['order_user_account'] = $user_info['account'];
		
// 				//媒体账号数
// 				$list[$key]['account_num'] =  $this->db['GeneralizeAccount']->where(array('generalize_id'=>$val['id']))->count();
		
// 				//状态
// 				$list[$key]['status_explain'] = $this->Order_Status[$val['status']]['explain'];
		
// 			}
// 		}
		
		
		$data['list'] = $list;
		parent::global_tpl_view( array(
				'action_name'=>'微博推广单',
				'title_name'=>'微博推广单',
				'add_name'=>'添加类别'
		));
	
		parent::data_to_view($data);
		$this->display();
	}
	
	//微博推广单编辑
	public function weibo_generalize_edit() {
		$order_id = $this->_get('id');
		$type = 2;
		$act = $this->_get('act');						//操作类型
		$content = $this->_post('content');				//留言内容
		
		if ($act == 'update') {
			if ($this->isPost()) {
					
				//修改订单状态
				$this->db['GeneralizeOrder']->create();
				$this->db['GeneralizeOrder']->where(array('id'=>$order_id))->save();
				
				//修改关联边订单状态
				$status = $this->_post('status');
				if ($status == 2) {
					$audit_status = $this->Account_Order_Status[1]['status'];
				} elseif ($status == 3) {
					$audit_status = $this->Account_Order_Status[2]['status'];
				}
				$GeneralizeAccount = $this->db['GeneralizeAccount'];
				$GeneralizeAccount->where(array('generalize_id'=>$order_id))->save(array('audit_status'=>$audit_status));
				
				//创建日志
				if (Validate::checkNull($content) == false) {
					$this->OrderLog->create();
					$is_insert = $this->OrderLog->add_order_log($this->oUser->id,$order_id,$type);
				}
								
				//如果已支付，拒绝就需要退款 20141204 bumtime
				if($status == 3)
				{
					$totalPrice = 0 ;
					$order_info = $this->db['GeneralizeOrder']->where(array('id'=>$order_id))->find();
					$adUserID = $order_info['users_id'];
					
		    		$accoutList = $this->db['GeneralizeAccount']->where(array('generalize_id'=>$order_id))->field('`account_id`, `price`, `rebate`, `audit_status`, users_id')->select();
		    		
		    		$tipsInfo = C('MESSAGE_TYPE_ADMIN');
		    		$k =0;
		    		$messageData = array();
		    		
		    		//总金额
		    		foreach ($accoutList  as $value)
		    		{
		    			$totalPrice += getAdMoney($value['price'], 'weibo', $value['rebate']);
		    		//	$totalPrice += $this->getAdMoney($value['price'], 'weibo', $value['rebate']);
		    		
		    		  	$messageData[$k]['send_from_id']	=	$this->oUser->id;
						$messageData[$k]['send_to_id']		=	$value['users_id'];
						$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
						$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Media/EventOrder/show', array('id'=>$order_id)));
						$messageData[$k]['message_time']	=	time();
						$k++;
		    		}
					//给广告主解冻
		    		D("UserAdvertisement")->setMoney($totalPrice, $adUserID, 1, 3, $order_id);
		    		
		    		
		    		//取消发站内短信(to 广告主) add by bumtime 20141223 
	    		  	$messageData[$k]['send_from_id']	=	$this->oUser->id;
					$messageData[$k]['send_to_id']		=	$adUserID;
					$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
					$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Advert/WeiboOrder/generalize_detail', array('order_id'=>$info['order_id'])));
					$messageData[$k]['message_time']	=	time();
		    		if($messageData)
						parent::sendMessageInfo($messageData, 2);	
				}
			}
			
		}elseif ($act == 'update_order') {
			if ($this->isPost()) {
				
				//修改订单状态
				$this->db['GeneralizeOrder']->create();
				$st = $this->db['GeneralizeOrder']->where(array('id'=>$order_id))->save();
			}
		}	
		
			
		$data['order_id'] = $order_id;
		
		$order_info = $this->db['GeneralizeOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		$order_log_list = $this->OrderLog->get_order_list(array('order_id'=>$order_id,'type'=>$type));
		$data['order_log_list'] = $order_log_list;
		
		//媒体列表
		$account_list = $this->db['GeneralizeAccount']->where(array('generalize_id'=>$order_id))->select();
		if($account_list == true) {
			foreach ($account_list as $key=>$val) {
				$account_data =  $this->db['AccountWeibo']->get_account_data_one($val['account_id']);
				$account_list[$key]['status_explain'] = $this->Account_Order_Status[$val['audit_status']]['explain'];
				//$account_list[$key]['status_explain'] = $val['audit_status'];
				$account_list[$key] = array_merge($account_list[$key],$account_data);
			}
		}
		
		$data['account_list'] = $account_list;
	
		parent::global_tpl_view( array(
				'action_name'=>'微博推广单',
				'title_name'=>'微博推广单',
				'add_name'=>'添加类别'
		));
		
		parent::data_to_view($data);
		
		$this->display();
	}
	//微博订单详情
	public function weibo_generalize_detail () {
		$order_id = $this->_get('id');
		$order_info = $this->db['GeneralizeOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		//图片
		$files_data = $this->db['GeneralizeFiles']->where(array('generalize_order_id'=>$order_id))->select();
		if (!empty($files_data)) {
			parent::public_file_dir($files_data,array('url'),'images/');
			$Arr_files_format = array();
			foreach ($files_data as $key=>$val) {
				$Arr_files_format[$val['type']][] = $val;
			}
			$data['files'] = $Arr_files_format;
		}

		//dump($data['files']);
		//exit;
		
		parent::global_tpl_view( array(
				'action_name'=>'微博推广单',
				'title_name'=>'微博推广单',
				'add_name'=>'订单详情'
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	
	
	//微博意向单列表
	public function weibo_intention () {
	
		$IntentionWeiboOrder = $this->db['IntentionWeiboOrder'];
		$where = $this->where;
		$list = $IntentionWeiboOrder->get_order_list($where);
	
// 		if ($list == true) {
// 			foreach ($list as $key=>$val) {
// 				//用户
// 				$user_info = $this->db['Users']->get_user_info(array('id'=>$val['users_id']));
// 				$list[$key]['order_user_account'] = $user_info['account'];
		
// 				//媒体账号数
// 				$list[$key]['account_num'] =  $this->db['IntentionWeiboAccount']->where(array('intention_id'=>$val['id']))->count();
		
// 				//状态
// 				$list[$key]['status_explain'] = $this->Order_Status[$val['status']]['explain'];
		
// 			}
// 		}
		
		$data['list'] = $list;
		parent::global_tpl_view( array(
				'action_name'=>'微博意向单',
				'title_name'=>'微博意向单',
		));
	
		parent::data_to_view($data);
		$this->display();
	}
	
	//微博意向单编辑
	public function weibo_intention_edit() {
		$order_id = $this->_get('id');
		$type = 3;
		$act = $this->_get('act');						//操作类型
		$content = $this->_post('content');				//留言内容
		
		if ($act == 'update') {
			if ($this->isPost()) {
					
				//修改订单状态
				$this->db['IntentionWeiboOrder']->create();
				$this->db['IntentionWeiboOrder']->where(array('id'=>$order_id))->save();
				
				
				//修改关联边订单状态
				$status = $this->_post('status');
				if ($status == 2) {
					$audit_status = $this->Account_Order_Status[1]['status'];
				} elseif ($status == 3) {
					$audit_status = $this->Account_Order_Status[2]['status'];
				}
				$IntentionWeiboAccount= $this->db['IntentionWeiboAccount'];
				$IntentionWeiboAccount->where(array('intention_id'=>$order_id))->save(array('audit_status'=>$audit_status));
				
				//创建日志
				if (Validate::checkNull($content) == false) {
					$this->OrderLog->create();
					$is_insert = $this->OrderLog->add_order_log($this->oUser->id,$order_id,$type);
				}
				
			}
			
			
		} elseif ($act == 'update_order') {
			if ($this->isPost()) {
				
				//修改订单状态
				$this->db['IntentionWeiboOrder']->create();
				$st = $this->db['IntentionWeiboOrder']->where(array('id'=>$order_id))->save();
			}
		}	
		
			
		$data['order_id'] = $order_id;
		
		$order_info = $this->db['IntentionWeiboOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		$order_log_list = $this->OrderLog->get_order_list(array('order_id'=>$order_id,'type'=>$type));
		$data['order_log_list'] = $order_log_list;
		
		//媒体列表
		$account_list = $this->db['IntentionWeiboAccount']->where(array('intention_id'=>$order_id))->select();
		if($account_list == true) {
			foreach ($account_list as $key=>$val) {
				$account_data =  $this->db['AccountWeibo']->get_account_data_one($val['account_id']);
				$account_list[$key]['status_explain'] = $this->Account_Order_Status[$val['audit_status']]['explain'];
				//$account_list[$key]['status_explain'] = $val['audit_status'];
				$account_list[$key] = array_merge($account_list[$key],$account_data);
			}
		}
		
		$data['account_list'] = $account_list;
		
		//图片
		$files_data = $this->db['IntentionWeiboFiles']->where(array('intention_order_id'=>$order_id))->select();
		
		if (!empty($files_data)) {
			parent::public_file_dir($files_data,array('url'),'images/');
			$Arr_files_format = array();
			foreach ($files_data as $key=>$val) {
				$Arr_files_format[$val['type']][] = $val;
			}
			$data['files'] = $Arr_files_format;
		}

		parent::global_tpl_view( array(
				'action_name'=>'微博意向单',
				'title_name'=>'微博意向单',
		));
		
		
		parent::data_to_view($data);
		$this->display();
	}
	//微博订单详情
	public function weibo_intention_detail () {
		$order_id = $this->_get('id');
		$order_info = $this->db['IntentionWeiboOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		//图片
		$files_data = $this->db['IntentionWeiboFiles']->where(array('intention_order_id'=>$order_id))->select();
		if (!empty($files_data)) {
			parent::public_file_dir($files_data,array('url'),'images/');
			$Arr_files_format = array();
			foreach ($files_data as $key=>$val) {
				$Arr_files_format[$val['type']][] = $val;
			}
			$data['files'] = $Arr_files_format;
		}
		
		parent::global_tpl_view( array(
				'action_name'=>'微博意向单',
				'title_name'=>'微博意向单',
				'add_name'=>'订单详情'
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	

	//微信推广单列表
	public function weixin_generalize () {
	
		$GeneralizeWeixinOrder = $this->db['GeneralizeWeixinOrder'];
		$where = $this->where;
		$list = $GeneralizeWeixinOrder->get_order_list($where);
	
// 		if ($list == true) {
// 			foreach ($list as $key=>$val) {
// 				//用户
// 				$user_info = $this->db['Users']->get_user_info(array('id'=>$val['users_id']));
// 				$list[$key]['order_user_account'] = $user_info['account'];
		
// 				//媒体账号数
// 				$list[$key]['account_num'] =  $this->db['GeneralizeWeixinAccount']->where(array('generalize_id'=>$val['id']))->count();
		
// 				//状态
// 				$list[$key]['status_explain'] = $this->Order_Status[$val['status']]['explain'];
		
// 			}
// 		}
		
		
		$data['list'] = $list;
		parent::global_tpl_view( array(
				'action_name'=>'微信推广单',
				'title_name'=>'微信推广单',
				'add_name'=>'添加类别'
		));
	
		parent::data_to_view($data);
		$this->display();
	}
	//微信推广单编辑
	public function weixin_generalize_edit() {
		$order_id = $this->_get('id');
		$type = 4;
		$act = $this->_get('act');						//操作类型
		$content = $this->_post('content');				//留言内容
		
		if ($act == 'update') {
			if ($this->isPost()) {
					
				//修改订单状态
				$this->db['GeneralizeWeixinOrder']->create();
				$this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->save();
				
				
				//修改关联边订单状态
				$status = $this->_post('status');
				if ($status == 2) {
					$audit_status = $this->Account_Order_Status[1]['status'];
				} elseif ($status == 3) {
					$audit_status = $this->Account_Order_Status[2]['status'];
				}
				$GeneralizeWeixinAccount = $this->db['GeneralizeWeixinAccount'];
				$GeneralizeWeixinAccount->where(array('generalize_id'=>$order_id))->save(array('audit_status'=>$audit_status));
				
				//创建日志
				if (Validate::checkNull($content) == false) {
					$this->OrderLog->create();
					$is_insert = $this->OrderLog->add_order_log($this->oUser->id,$order_id,$type);
				}
				
				//如果已支付，拒绝就需要退款 20141204 bumtime
				if($status == 3)
				{
					$totalPrice = 0 ;
					$order_info = $this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->find();
					$adUserID = $order_info['users_id'];
					
		    		$accoutList = $this->db['GeneralizeWeixinAccount']->where(array('generalize_id'=>$order_id))->field('`account_id`, `price`, `rebate`, `audit_status`, users_id')->select();
		    		
		    		$tipsInfo = C('MESSAGE_TYPE_ADMIN');
		    		$k =0;
		    		$messageData = array();
		    		
		    		//总金额
		    		foreach ($accoutList  as $value)
		    		{
		    			$totalPrice += getAdMoney($value['price'], 'weixin', $value['rebate']);
		    			//$totalPrice += $this->getAdMoney($value['price'], 'weixin', $value['rebate']);
		    			//取消发站内短信 add by bumtime 20141223
		    		    $messageData[$k]['send_from_id']	=	$this->oUser->id;
						$messageData[$k]['send_to_id']		=	$value['users_id'];
						$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
						$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Media/EventOrder/showNews', array('id'=>$order_id)));
						$messageData[$k]['message_time']	=	time();
						$k++;
		    		}
		    		
					//给广告主解冻
		    		D("UserAdvertisement")->setMoney($totalPrice, $adUserID, 1, 2, $order_id);
		    		
		    		//取消发站内短信(to 广告主) add by bumtime 20141223 
	    		  	$messageData[$k]['send_from_id']	=	$this->oUser->id;
					$messageData[$k]['send_to_id']		=	$adUserID;
					$messageData[$k]['subject']			=	$tipsInfo[1]['subject'];
					$messageData[$k]['content']			=	sprintf($tipsInfo[1]['content'], $order_info['title'], U('/Advert/WeixinOrder/generalize_detail', array('order_id'=>$info['order_id'])));
					$messageData[$k]['message_time']	=	time();
						
		    		if($messageData)
						parent::sendMessageInfo($messageData, 2);	
		    		
				}
			}
			
			
		}elseif ($act == 'update_order') {
			if ($this->isPost()) {
				
				//修改订单状态
				$this->db['GeneralizeWeixinOrder']->create();
				$st = $this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->save();
			}
		}	
		
			
		$data['order_id'] = $order_id;
		
		$order_info = $this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		$order_log_list = $this->OrderLog->get_order_list(array('order_id'=>$order_id,'type'=>$type));
		$data['order_log_list'] = $order_log_list;
		
		//媒体列表
		$account_list = $this->db['GeneralizeWeixinAccount']->where(array('generalize_id'=>$order_id))->select();
		if($account_list == true) {
			foreach ($account_list as $key=>$val) {
				$account_data =  $this->db['AccountWeixin']->get_account_data_one($val['account_id']);
				$account_list[$key]['status_explain'] = $this->Account_Order_Status[$val['audit_status']]['explain'];
				//$account_list[$key]['status_explain'] = $val['audit_status'];
				$account_list[$key] = array_merge($account_list[$key],$account_data);
			}
		}
		
		$data['account_list'] = $account_list;
		
		parent::global_tpl_view( array(
				'action_name'=>'微信推广单',
				'title_name'=>'微信推广单',
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	//微信订单详情
	public function weixin_generalize_detail () {
		$order_id = $this->_get('id');
		$order_info = $this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		//图片
		$files_data = $this->db['GeneralizeWeixinFiles']->where(array('generalize_order_id'=>$order_id))->select();
		if (!empty($files_data)) {
			parent::public_file_dir($files_data,array('url'),'images/');
			$Arr_files_format = array();
			foreach ($files_data as $key=>$val) {
				$Arr_files_format[$val['type']][] = $val;
			}
			$data['files'] = $Arr_files_format;
		}
		
		parent::global_tpl_view( array(
				'action_name'=>'微信推广单',
				'title_name'=>'微信推广单',
				'add_name'=>'订单详情'
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	
	//微信意向单列表
	public function weixin_intention () {
		$IntentionWeixinOrder = $this->db['IntentionWeixinOrder'];
		$where = $this->where;
		$list = $IntentionWeixinOrder->get_order_list($where);
		
		if ($list == true) {
			foreach ($list as $key=>$val) {
				//用户
				$user_info = $this->db['Users']->get_user_info(array('id'=>$val['users_id']));
				$list[$key]['order_user_account'] = $user_info['account'];
		
				//媒体账号数
				$list[$key]['account_num'] = $this->db['IntentionWeixinAccount']->where(array('generalize_id'=>$val['id']))->count();
		
				//状态
				$list[$key]['status_explain'] = $this->Order_Status[$val['status']]['explain'];
		
			}
		}
		
		$data['list'] = $list;
		parent::global_tpl_view( array(
				'action_name'=>'微信意向单',
				'title_name'=>'微信意向单',
				'add_name'=>'添加类别'
		));
		
		parent::data_to_view($data);
		$this->display();
	}
	//微信意向单编辑
	public function weixin_intention_edit() {
		$order_id = $this->_get('id');
		$type = 5;
		$act = $this->_get('act');						//操作类型
		$content = $this->_post('content');				//留言内容
		
		if ($act == 'update') {
			if ($this->isPost()) {
					
				//修改订单状态
				$this->db['IntentionWeixinOrder']->create();
				$this->db['IntentionWeixinOrder']->where(array('id'=>$order_id))->save();
				
				
				//修改关联边订单状态
				$status = $this->_post('status');
				if ($status == 2) {
					$audit_status = $this->Account_Order_Status[1]['status'];
				} elseif ($status == 3) {
					$audit_status = $this->Account_Order_Status[2]['status'];
				}
				$IntentionWeixinAccount = $this->db['IntentionWeixinAccount'];
				$IntentionWeixinAccount->where(array('generalize_id'=>$order_id))->save(array('audit_status'=>$audit_status));
				
				//创建日志
				if (Validate::checkNull($content) == false) {
					$this->OrderLog->create();
					$is_insert = $this->OrderLog->add_order_log($this->oUser->id,$order_id,$type);
				}
			}
		
		}elseif ($act == 'update_order') {
			if ($this->isPost()) {
				
				//修改订单状态
				$this->db['IntentionWeixinOrder']->create();
				$st = $this->db['IntentionWeixinOrder']->where(array('id'=>$order_id))->save();
			}
		}	
		
			
		$data['order_id'] = $order_id;
		
		$order_info = $this->db['IntentionWeixinOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		$order_log_list = $this->OrderLog->get_order_list(array('order_id'=>$order_id,'type'=>$type));
		$data['order_log_list'] = $order_log_list;
		
		
		//媒体列表
		$account_list = $this->db['IntentionWeixinAccount']->where(array('generalize_id'=>$order_id))->select();
		if($account_list == true) {
			foreach ($account_list as $key=>$val) {
				$account_data =  $this->db['AccountWeixin']->get_account_data_one($val['account_id']);
				$account_list[$key]['status_explain'] = $this->Account_Order_Status[$val['audit_status']]['explain'];
				//$account_list[$key]['status_explain'] = $val['audit_status'];
				$account_list[$key] = array_merge($account_list[$key],$account_data);
			}
		}
		$data['account_list'] = $account_list;
	
		parent::global_tpl_view( array(
				'action_name'=>'微信意向单',
				'title_name'=>'微信意向单',
				'add_name'=>'添加类别'
		));
		parent::data_to_view($data);
		$this->display();
	}
	//微信订单详情
	public function weixin_intention_detail () {
		$order_id = $this->_get('id');
		$order_info = $this->db['IntentionWeixinOrder']->where(array('id'=>$order_id))->find();
		$data['order_info'] = $order_info;
		
		//图片
		$files_data = $this->db['IntentionWeixinFiles']->where(array('generalize_order_id'=>$order_id))->select();
		if (!empty($files_data)) {
			parent::public_file_dir($files_data,array('url'),'images/');
			$Arr_files_format = array();
			foreach ($files_data as $key=>$val) {
				$Arr_files_format[$val['type']][] = $val;
			}
			$data['files'] = $Arr_files_format;
		}
		
		parent::global_tpl_view( array(
				'action_name'=>'微信推广单',
				'title_name'=>'微信推广单',
				'add_name'=>'订单详情'
		));
		parent::data_to_view($data);
		$this->display();
	}
	
	

	
	 /**
     * 计算加价的总金额
     * 
     * @param  float 	$money	订单账号的金额
     * @param  int		$type	类型：weibo weixin news
     * @author bumtime
     * @date   2014-11-15
     * 
     * @return array   
     **/
    private function getAdMoney($money, $type, $rebate)
    {
    	switch($type)
    	{
    		case 'weibo' :
    		case 'weixin' : 
    			$money = $money * (1+$rebate);
    			break;
    		case 'news' :
    			$money = $money + $rebate;
    			break;
    	}
    	
    	return $money;
    }


    /**
    * 修改订单状态
    * @param  _POST
    * @param  int $type	类型：weibo名人草根 weixin名人草根 news 1-新闻 2-微博推广 3-微博意向 4-微信推广 5-微信意向
    * @author zhucc
    * @date   2015-1-10
    * @return boolen   
    **/

  	public function setPostLogStatus()
  	{
  		$order_id = $this->_post('order_id');			//大订单ID
  		$account_id = $this->_post('account_id');		//小订单ID
  		$type = $this->_post('type');					//订单类型
  		$content = $this->_post('content');				//内容
  		$act = $this->_post('act');						//通过不通过
  		$Account_Order_Status = C('Account_Order_Status');
  		
  		$OrderLog = $this->db['OrderLog'];
  		switch($type)
  		{
  			//1-新闻
  			case 1:
  				$OrderLog->create();
  				$OrderLog->user_id = $this->oUser->id;
  				$OrderLog->create_time = time();
  				$OrderLog->add();
  				if($act=='tg')
  				{
  					$update = array('audit_status'=>$Account_Order_Status[1]['status']);
  					$bool = $this->db['GeneralizeNewsAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}else{
  					//退款处理如果已经支付
  					$audit_status = $this->db['GeneralizeNewsAccount']->where(array('id'=>$account_id))->field('price,rebate,audit_status')->find();
  					if($audit_status['audit_status'] == $Account_Order_Status[3]['status'])
  					{
  						$allprice = $audit_status['rebate'] + $audit_status['price'];
  						$users_id = $this->db['GeneralizeNewsOrder']->where(array('id'=>$order_id))->getField('users_id');
  						$UserAdvertisement = D('UserAdvertisement');
  						$money = $UserAdvertisement->where(array('users_id'=>$users_id))->field('money,freeze_funds')->find();
  						$new_money['money'] = $money['money'] + $allprice;
  						$new_money['freeze_funds'] = $money['freeze_funds'] - $allprice;
  						$UserAdvertisement->where(array('users_id'=>$users_id))->save($new_money);
  						$arr['users_id'] = $users_id;
  						$arr['shop_number'] = 'JD' . time();
  						$arr['money'] = $allprice;
  						$arr['adormed'] = 2;
  						$arr['member_info'] = '该订单下子订单已被平台强制取消，解冻金额返回';
  						$arr['admin_info'] = $content;
  						$arr['time'] = time();
  						$arr['paytype'] = 2;
  						$arr['adverttype'] = 1;
  						$arr['generalizeid'] = $order_id;
  						$arr['status'] = 1;
  						D('fund')->add($arr);
  					}
  					$update = array('audit_status'=>$Account_Order_Status[2]['status']);
  					$bool = $this->db['GeneralizeNewsAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}
  			break;
  			//2-微博推广
  			case 2:
  				$OrderLog->create();
  				$OrderLog->user_id = $this->oUser->id;
  				$OrderLog->create_time = time();
  				$OrderLog->add();
  				if($act=='tg')
  				{
  					$update = array('audit_status'=>$Account_Order_Status[1]['status']);
  					$bool = $this->db['GeneralizeAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}else{
  					//退款处理如果已经支付
  					$audit_status = $this->db['GeneralizeAccount']->where(array('id'=>$account_id))->field('price,rebate,audit_status')->find();
  					if($audit_status['audit_status'] == $Account_Order_Status[3]['status'])
  					{
  						$allprice = ($audit_status['rebate'] * $audit_status['price']) + $audit_status['price'];
  						$users_id = $this->db['GeneralizeOrder']->where(array('id'=>$order_id))->getField('users_id');
  						$UserAdvertisement = D('UserAdvertisement');
  						$money = $UserAdvertisement->where(array('users_id'=>$users_id))->field('money,freeze_funds')->find();
  						$new_money['money'] = $money['money'] + $allprice;
  						$new_money['freeze_funds'] = $money['freeze_funds'] - $allprice;
  						$UserAdvertisement->where(array('users_id'=>$users_id))->save($new_money);
  						$arr['users_id'] = $users_id;
  						$arr['shop_number'] = 'JD' . time();
  						$arr['money'] = $allprice;
  						$arr['adormed'] = 2;
  						$arr['member_info'] = '该订单下子订单已被平台强制取消，解冻金额返回';
  						$arr['admin_info'] = $content;
  						$arr['time'] = time();
  						$arr['paytype'] = 2;
  						$arr['adverttype'] = 3;
  						$arr['generalizeid'] = $order_id;
  						$arr['status'] = 1;
  						D('fund')->add($arr);
  					}
  					$update = array('audit_status'=>$Account_Order_Status[2]['status']);
  					$bool = $this->db['GeneralizeAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}
  			break;
  			//3-微博意向
  			case 3:
  				$OrderLog->create();
  				$OrderLog->user_id = $this->oUser->id;
  				$OrderLog->create_time = time();
  				$OrderLog->add();
  				
  				//通过
  				if($act=='tg')
  				{
  					$update = array('audit_status'=>$Account_Order_Status[5]['status']);
  					$bool = $this->db['IntentionWeiboAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}else{
  					$update = array('audit_status'=>$Account_Order_Status[2]['status']);
  					$bool = $this->db['IntentionWeiboAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}
  			break;
  			//4-微信推广
  			case 4:
  				$OrderLog->create();
  				$OrderLog->user_id = $this->oUser->id;
  				$OrderLog->create_time = time();
  				$OrderLog->add();
  				if($act=='tg')
  				{
  					$update = array('audit_status'=>$Account_Order_Status[1]['status']);
  					$bool = $this->db['GeneralizeWeixinAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}else{
  					//退款处理如果已经支付
  					$audit_status = $this->db['GeneralizeWeixinAccount']->where(array('id'=>$account_id))->field('price,rebate,audit_status')->find();
  					if($audit_status['audit_status'] == $Account_Order_Status[3]['status'])
  					{
  						$allprice = ($audit_status['rebate'] * $audit_status['price']) + $audit_status['price'];
  						$users_id = $this->db['GeneralizeWeixinOrder']->where(array('id'=>$order_id))->getField('users_id');
  						$UserAdvertisement = D('UserAdvertisement');
  						$money = $UserAdvertisement->where(array('users_id'=>$users_id))->field('money,freeze_funds')->find();
  						$new_money['money'] = $money['money'] + $allprice;
  						$new_money['freeze_funds'] = $money['freeze_funds'] - $allprice;
  						$UserAdvertisement->where(array('users_id'=>$users_id))->save($new_money);
  						$arr['users_id'] = $users_id;
  						$arr['shop_number'] = 'JD' . time();
  						$arr['money'] = $allprice;
  						$arr['adormed'] = 2;
  						$arr['member_info'] = '该订单下子订单已被平台强制取消，解冻金额返回';
  						$arr['admin_info'] = $content;
  						$arr['time'] = time();
  						$arr['paytype'] = 2;
  						$arr['adverttype'] = 2;
  						$arr['generalizeid'] = $order_id;
  						$arr['status'] = 1;
  						D('fund')->add($arr);
  					}
  					$update = array('audit_status'=>$Account_Order_Status[2]['status']);
  					$bool = $this->db['GeneralizeWeixinAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}
  			break;
  			//5-微信意向
  			case 5:
  				$OrderLog->create();
  				$OrderLog->user_id = $this->oUser->id;
  				$OrderLog->create_time = time();
  				$OrderLog->add();
  				//通过
  				if($act=='tg')
  				{
  					$update = array('audit_status'=>$Account_Order_Status[5]['status']);
  					$bool = $this->db['IntentionWeixinAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}else{
  					$update = array('audit_status'=>$Account_Order_Status[2]['status']);
  					$bool = $this->db['IntentionWeixinAccount']->where(array('id'=>$account_id))->save($update);
  					return $bool ? true : false;
  				}
  			break;
  			default:
  				return false;
  			break;
  		}

  	}
}