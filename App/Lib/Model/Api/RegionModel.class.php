<?php

//区域表
class RegionModel extends ApiBaseModel {
	
	
	//获取父集下的数据
	public function get_parent_list ($parent_id) {
		return parent::get_spe_data(array('parent_id'=>$parent_id));
	}
	
	public function get_regionInfo_by_id ($region_id) {
		return $this->where(array('region_id'=>$region_id))->find();
	}
	
}

?>
