<?php

/**
 * 用户组模型
 */

class GroupModel extends AdminBaseModel {
	
	//获取指定子级数据
	public function seek_all_data ($pid) {
		$data = $this->field('id,name,title,status,create_time,update_time')->where(array('status'=>0,'pid'=>$pid))->select();
		parent::set_all_time($data,array('create_time','update_time'));
		return $data;
	}
	
}

?>
