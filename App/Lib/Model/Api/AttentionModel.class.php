<?php

/*
*	赞的列表
*
*
*/

class AttentionModel extends ApiBaseModel {

	//获得关注人
	public function getNewsList($id,$p,$index)
	{
		$first = $p == '' ? 0 : $p;
		$offset = $index =='' ? 10 : $index;

        $list = D('UserFriends')->table('app_user_friends as f')->where(array('f.user_id'=>$id,'f.friend_statis'=>1))
                ->join('app_attention as a on f.friend_id = a.user_id')->order('a.create_time desc')
            ->limit($first * $offset,$offset)->select();

		$list_arr = array();
		
		$list_arr['all_num'] = D('UserFriends')->table('app_user_friends as f')
            ->where(array('f.user_id'=>$id,'f.friend_statis'=>1))
            ->join('app_attention as a on f.friend_id = a.user_id')->count();

        $Article = D('Article');

		foreach($list as $key=>$value)
		{
            $list_arr['info'][$key]['user_info'] = parent::get_user_info($value['user_id']);

			parent::public_file_dir($list_arr['info'][$key],array('head_img'));

			$list_arr['info'][$key]['content']['type'] = $value['status'];

			$list_arr['info'][$key]['content']['info'] = $Article->where(array('id'=>$value['attention_id']))
                ->field('id,content,article_img')->find();

			parent::public_file_dir($list_arr['info'][$key]['content'],array('article_img'));

            $list_arr['info'][$key]['content']['info']['like_num'] = parent::get_contentpraise_count($value['attention_id']);

			$list_arr['info'][$key]['time'] = date('Y-m-d H:i:s',$value['create_time']);
		}

		return $list_arr;
	}
}