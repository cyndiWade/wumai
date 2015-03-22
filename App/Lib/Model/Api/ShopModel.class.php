<?php 

/**
 * API-商城模型
 */

class ShopModel extends ApiBaseModel {
	
    private $shop_status;
    
    public function __construct() {
        parent::__construct();
        $this->shop_status = C('SHOP_STATUS');
    }

	public function getInfoALL()
	{
        $new_list = array();

		$list = $this->where(array('is_del'=>0,'status'=>$this->shop_status[0]['status']))->select();

        foreach($list as $key=>$value)
        {
            $new_list[$key] = $value;
            $new_list[$key]['shop_id'] = $value['id'];
            $new_list[$key]['shop_url'] = parent::get_shopphoto_url($value['id']);
        }

		parent::public_file_dir($new_list,array('shop_url'));

		return $new_list;
	}
}
?>