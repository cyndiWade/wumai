<?php

//城市管理别表
class CityModel extends ApiBaseModel {
	

    private $data_list = array();
    
    //递归调用一个父级下所有的子分类数据
    private function seek_parent_list($parent_id) {
        //$now_data_list = $this->where(array('parent_id'=>$parent_id,'is_del'=>0,'show_status'=>1))->select();
        $now_data_list = $this->get_available_data($parent_id);
    
        if ($now_data_list == true) {
            foreach ($now_data_list as $key=>$val) {
                array_push($this->data_list,$val);
                $this->seek_parent_list($val['id']);
            }
        }
    }
    
    /**
     * 获取可以用的列表
     * @param INT $parent_id
     */
    private function get_available_data ($parent_id) {
        return $this->where(array('parent_id'=>$parent_id,'is_del'=>0,'show_status'=>1))->select();
    }
    
    /**
     * 获取子类下所有的分类数据，并且分类好
     * @param INT $parent_id
     * @return Array
     */
    public function get_classify_data ($parent_id) {
        $this->data_list = array();
         
        $this->seek_parent_list($parent_id);
        $data = $this->data_list;
    
        $result = array();
        foreach ($data as $key=>$val) {
            $result[$val['parent_id']][] = $val;
        }
    
        return $result;
    }
    
    
    /*
     * 获取一个指定父类下的一组节点
    */
    public function get_data_by_parent_id ($parent_id) {
        $result = array();
         
        $data = $this->get_available_data($parent_id);
    
        $result = regroupKey($data,'id',true);
    
        return $result;
    }
}

?>
