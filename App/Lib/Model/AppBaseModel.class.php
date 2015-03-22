<?php 

/**
 * 公共---基础模型
 */

class AppBaseModel extends Model {
	
	protected $prefix;		//表前缀
	
	public function __construct() {
		parent::__construct();
		
		$this->admin_base_init();
	}
	
	//初始表前缀
	private function admin_base_init () {
		$this->prefix = C('DB_PREFIX');
	}

	//删除方法，请废弃不要使用
	public function del($condition) { 
		return $this->where($condition)->data(array('status'=>-2))->save();
	}
	
	//获取所有数据，请废弃不要使用
	public function get_all_data ($field = '*') {
		return $this->field($field)->select();
	}

	
	
	/**
	 * 获取指定所有数据 (过于复杂的sql，请单独写一个model,参数类似)
	 * @param Array $condition 条件
	 * @param string $fields  字段
	 * @param number $offset  偏移值
	 * @param number $limit   条数
	 * @param string $order_by 排序
	 * @return Array
	 * 
	 * ($condition = array(),$fields = '*',$offset = 0,$limit = 500,$order_by = "") 
	 * ($condition,$fields ,$offset,$limit,$order_by );
	 * 
	 */
	public function get_spe_data ($condition = array(),$fields = '*',$offset = 0,$limit = 500,$order_by = "") {
		$result = $this->field($fields)
		->where($condition)
		->order($order_by)
		->limit($offset.','.$limit)
		->select();
		return $result;
	}
	
	
	/**
	 * 获取指定数据，然后分页
	 * @param Array $condition 条件
	 * @param string $fields 获取的字段：如:$fields = 'a.*,b.*'
	 * @param number $list_rows  每页条数
	 * @param string $order_by   按照哪个字段排序
	 * @param string $is_show_page_html  是否显示分页的HTML
	 * @return array
	 * 
	 * ($condition = array(),$fields = '*',$list_rows = 500,$order_by = '',$is_show_page_html =  true)
	 * ($condition,$fields,$list_rows,$order_by,$is_show_page_html) 
	 * 
	 */
	public function get_spe_page_data ($condition = array(),$fields = '*',$list_rows = 500,$order_by = '',$is_show_page_html =  true) {
	    
	    $result = array();
	     
	    import('ORG.Util.Page');
	    
	    $count =  $this->where($condition)->count();

	    $Page  = new Page($count,$list_rows,$condition);
	    
	    if ($is_show_page_html == true) {
	        $Page->setConfig('header','<span style=";">条记录</span>');//设置样式
	        $Page->setConfig('prev','上一页');
	        $Page->setConfig('next','下一页');
	        $Page->setConfig('first','首页');
	        $Page->setConfig('last','尾页');
	        //替换以后样式到
	        $Page->setConfig('theme','共 %totalRow% %header% %nowPage%/%totalPage% 页  %first%  %upPage%  %linkPage%  %downPage%  %end% ');
	        
	        $result['page_html'] = $Page->show();
	    }
	
	    $list = $this->get_spe_data($condition,$fields,$Page->firstRow,$Page->listRows,$order_by);
	    $result['list'] = $list;
	     
	    return $result;
	}
	
	//获取一条数据
	public function get_one_data ($condition,$field = '*') {
		return $this->field($field)->where($condition)->find();
	}
	
	//修改一条数据
	public function save_one_data ($condition,$data = array()) {
	    
	    if (empty($data)) {
	       $state = $this->where($condition)->save();
	    } else {
	       $state = $this->where($condition)->data($data)->save();
	    }
		return $state;
	}
	
	//逻辑删除
	public function delete_data ($condition) {
	    return $this->where($condition)->data(array('is_del'=>1))->save();
	}
	
	//真实删除
	public function delete_real ($condition) {
	    return $this->where($condition)->delete(); 
	}
	 
	
	
	/**
	 * 格式化日期
	 * @param Array $all			//数组
	 * @param Array $fields			//字段如：array('create_time','update_time');
	 */
	protected function set_all_time(&$all,$fields,$default = 'Y-m-d H:i:s') {
		if (empty($all)) return false;
		/* 多维数组 */
		if (count($all[0]) >=1)  {
			foreach ($all AS $key=>$val) {
				for ($i=0;$i<count($fields);$i++) {
					if (empty($all[$key][$fields[$i]])) continue;
					$all[$key][$fields[$i]] = date($default,$all[$key][$fields[$i]]);
				}
			}
		/* 一维数组 */	
		} else {
			for ($i=0;$i<count($fields);$i++) {
				if (empty($all[$fields[$i]])) continue;
				$all[$fields[$i]] = date($default,$all[$fields[$i]]);
			}
		}	
		
	}
	
	
	/**
	 * 字符长度限制
	 * @param Array $all				//
	 * @param Array $fields			//字段如：array('create_time','update_time');
	 */
	protected function set_str_len(&$all,$fields,$length) {
		if (empty($all)) return false;
		/* 多维数组 */
		if (count($all[0]) >=1)  {
			foreach ($all AS $key=>$val) {
				for ($i=0;$i<count($fields);$i++) {			
					if (mb_strlen($all[$key][$fields[$i]],'utf-8') >$length) {
						$all[$key][$fields[$i]] = mb_substr($all[$key][$fields[$i]],0,10,'utf-8').'...';
			
					}	
				}
			}
			/* 一维数组 */
		} else {
			for ($i=0;$i<count($fields);$i++) {
				$all[$fields[$i]] = mb_substr($all[$key][$fields[$i]],0,10,'utf-8').'...';
			}
		}
	}
	
	
	
	/**
	 * 获取表的所有字段
	 * @param String $Model_Name
	 * @return Array
	 * @说明  调用parent::getTableColumns('AccountNews')
	 */
	protected function getTableColumns ($Model_Name) {
	
		$fields =  M($Model_Name)->query("
			SELECT
				column_name AS fields
			FROM
				Information_schema.columns
			WHERE
				 table_Name =  '__TABLE__'");
		if ($fields == true) {
			return getArrayByField($fields,'fields');
		} else {
			return array();
		}
	
	}
	
	/**
	 * 为表追加表前缀
	 * @param String $Model_Name
	 * @param String $prefix
	 * @return string|boolean
	 * @说明 parent::field_add_prefix('AccountNews','bs_');
	 */
	protected function field_add_prefix($Model_Name,$prefix,$fount='') {
		$fields = self::getTableColumns($Model_Name);
		$result_array = array();
		if ($fields == true) {
			foreach($fields as $fd) {
				array_push($result_array,$fount . $fd.' AS '.$prefix.$fd);
			}
			return implode(',',$result_array);
		} else {
			return false;
		}
	}
	
	
	/**
	 * 游标模式从数据库获取数据
	 * @param Object $Obj_Dao		dao的对象
	 * @param String $Str_vernier_field	游标的字段，这个字段保存的值必须是INT类型
	 * @param Array $Arr_where		基础的查询条件
	 * @param Int $Int_limit		每次拿出的值
	 * @param Array $Arr_field		拿出的字段
	 * @param Array $Arr_order_by	数据库排序
	 * @return Array
	 */
	protected  function vernier_for_data ($Obj_Dao,$Str_vernier_field,$Arr_where = array(),$Int_limit = 500,$Arr_field = array(),$Arr_order_by = array()) {
	
		$Int_now_id = 0;	//当前游标的ID
	
		$Arr_base_where = $Arr_where;
	
		$Arr_result = array();
	
		while (true) {
			//$Arr_extend_Where[$Str_vernier_field.' > ?'] = $Int_now_id;
			$Arr_extend_Where[$Str_vernier_field] = array('gt',$Int_now_id);
			
			$Arr_query_where = array_merge($Arr_base_where,$Arr_extend_Where);
			$Arr_data_list = $Obj_Dao->field($Arr_field)->where($Arr_query_where)->order($Arr_order_by)->limit('0,'.$Int_limit)->select();
			$Int_list_count = count($Arr_data_list);	//统计本次查询条数
			$Int_now_id = $Arr_data_list[$Int_list_count-1][$Str_vernier_field];	//记录本次查询数据的最后一条的ID
				
			$Arr_result = array_merge($Arr_result,$Arr_data_list);	//组合数组
				
			if ($Int_list_count < $Int_limit) break;
		}
	
		return $Arr_result;
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
                if(substr($arr[$key][$field],0,4)=='http')
                {
                    $arr[$key][$field] = $val[$field];
                }elseif($arr[$key][$field]==C('UPLOAD_DIR.default_background_img')){
                    $arr[$key][$field] = C('PUBLIC_VISIT.domain_dir').C('PUBLIC_VISIT.app_image').C('UPLOAD_DIR.default_background_img');
                }else{
                    $arr[$key][$field] = $public_file_dir.$val[$field];
                }
	        }
	    }
	}
	
	
	
	//protected function get
	
}
?>