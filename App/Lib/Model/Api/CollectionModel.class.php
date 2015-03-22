<?php

/*
*	收藏文章
*
*
*/

class CollectionModel extends ApiBaseModel {

	public function getCollList($id,$p,$index)
	{
		$first = $p == '' ? 0 : $p;

		$offset = $index =='' ? 10 : $index;

		$list_arr = array();

		$list = $this->where(array('c.user_id'=>array('eq',$id)))
            ->table('app_collection as c')
            ->join('app_article as a on a.id = c.article_coll_id')->limit($first * $offset,$offset)
            ->order('c.create_time desc')->order('a.create_time desc')
            ->field('a.id,a.content,a.article_img,a.user_id,a.create_time')->select();

		parent::public_file_dir($list,array('article_img'));

		foreach($list as $key=>$value)
		{
            $list_arr['info'][$key]['user_info'] = parent::get_user_info($value['user_id']);

			parent::public_file_dir($list_arr['info'][$key],array('head_img'));

			$list_arr['info'][$key]['content'] = $value;
			
			$list_arr['info'][$key]['content']['time'] = date('Y-m-d H:i:s',$value['create_time']);

            $list_arr['info'][$key]['content']['like_num'] = parent::get_contentpraise_count($value['id']);
		}

		$list_arr['news_num'] = $this->where(array('user_id'=>array('eq',$id)))->count();

		return $list_arr;
	}

	public function collect_like($id,$photo_id)
	{
		$where = array('user_id'=>$id,'article_coll_id'=>$photo_id);
		$count = $this->where($where)->count();
		if($count==0)
		{
			$where['create_time'] = time();
			$bool = $this->add($where);
			$log = array('user_id'=>$id,'attention_id'=>$photo_id,'status'=>2);
			parent::listen(__CLASS__,__FUNCTION__,$log);
			return $bool ? true : false;
		}
	}
}