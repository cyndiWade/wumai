<?php

/**
 * 节点模型
 */

class NodeModel extends AdminBaseModel {
	
	//获取获取子集数据
	public function seek_pid_list ($pid) {
		return $this->where(array('pid'=>$pid))->select();
	}
	
	
	//添加节点
	public function add_node_data () {
		return $this->add();
	}
	
}

?>
